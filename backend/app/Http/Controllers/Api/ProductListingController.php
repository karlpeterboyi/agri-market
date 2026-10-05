<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductListing;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

class ProductListingController extends Controller
{
    /**
     * Create a new product listing.
     * Images are optional and never validated with Laravel's "file/uploaded" rules
     * (those produce "The featured image failed to upload" when isValid() is false).
     */
    public function store(Request $request)
    {
        RoleAccess::assertCanSellProduce(auth()->user());

        $validated = $request->validate([
            'commodity_id' => 'required|exists:commodities,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'grade' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'region' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $paths = [];
        $imageNotes = [];

        foreach (['featured_image', 'image_2', 'image_3'] as $field) {
            $file = $request->file($field);
            if (!$file) {
                continue;
            }

            try {
                $stored = $this->storeListingImage($file, $field);
                if ($stored) {
                    $paths[$field] = $stored;
                } else {
                    $imageNotes[$field] = 'skipped';
                }
            } catch (\Throwable $e) {
                \Log::warning("Listing image {$field}: ".$e->getMessage());
                $imageNotes[$field] = $e->getMessage();
            }
        }

        try {
            $listing = ProductListing::create([
                'commodity_id' => $validated['commodity_id'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'grade' => $validated['grade'] ?? null,
                'price' => $validated['price'],
                'region' => $validated['region'],
                'district' => $validated['district'],
                'description' => $validated['description'] ?? null,
                'featured_image' => $paths['featured_image'] ?? null,
                'image_2' => $paths['image_2'] ?? null,
                'image_3' => $paths['image_3'] ?? null,
                'seller_id' => auth()->id(),
                'status' => 'available',
            ]);
        } catch (\Throwable $e) {
            \Log::error('ProductListing create failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Listing could not be saved: '.$e->getMessage(),
                'errors' => ['listing' => [$e->getMessage()]],
                'image_paths_attempted' => $paths,
            ], 500);
        }

        return response()->json([
            'message' => 'Listing created successfully',
            'listing' => $listing->load('commodity'),
            'data' => $listing,
            'images_saved' => $paths,
            'image_notes' => $imageNotes,
        ], 201);
    }

    /**
     * Save upload even when PHP is_uploaded_file() is false (proxies / Termux).
     */
    protected function storeListingImage($file, string $field): ?string
    {
        $dir = storage_path('app/public/listings');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $original = method_exists($file, 'getClientOriginalName')
            ? (string) $file->getClientOriginalName()
            : $field.'.jpg';
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION) ?: '');
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif', 'jfif', 'bmp'];
        if (!in_array($ext, $allowedExt, true)) {
            $ext = 'jpg';
        }

        $filename = $field.'_'.str_replace('.', '', uniqid('', true)).'.'.$ext;
        $destRelative = 'listings/'.$filename;
        $destAbsolute = storage_path('app/public/'.$destRelative);

        if (method_exists($file, 'isValid') && $file->isValid()) {
            try {
                return $file->storeAs('listings', $filename, 'public');
            } catch (\Throwable $e) {
                \Log::warning('storeAs failed, trying manual copy: '.$e->getMessage());
            }
        }

        $src = null;
        if (method_exists($file, 'getRealPath')) {
            $rp = $file->getRealPath();
            if ($rp && is_file($rp)) {
                $src = $rp;
            }
        }
        if (!$src && method_exists($file, 'getPathname')) {
            $pn = $file->getPathname();
            if ($pn && is_file($pn)) {
                $src = $pn;
            }
        }

        if ($src) {
            if (!@copy($src, $destAbsolute)) {
                $data = @file_get_contents($src);
                if ($data === false || file_put_contents($destAbsolute, $data) === false) {
                    throw new \RuntimeException("Could not write {$field} to storage");
                }
            }

            return $destRelative;
        }

        if (method_exists($file, 'getContent')) {
            try {
                $data = $file->getContent();
                if ($data !== '' && $data !== false) {
                    file_put_contents($destAbsolute, $data);

                    return $destRelative;
                }
            } catch (\Throwable $e) {
                // continue
            }
        }

        throw new \RuntimeException(
            "{$field} not readable (valid=".
            ((method_exists($file, 'isValid') && $file->isValid()) ? 'yes' : 'no').
            ', error='.(method_exists($file, 'getError') ? $file->getError() : '?').')'
        );
    }

    public function index()
    {
        return ProductListing::with([
            'commodity.category',
            'seller',
        ])
            ->where('status', 'available')
            ->latest()
            ->get();
    }

    public function show($id)
    {
        return ProductListing::with([
            'commodity.category',
            'seller',
        ])->findOrFail($id);
    }

    public function myListings()
    {
        return ProductListing::with([
            'commodity.category',
        ])
            ->where('seller_id', auth()->id())
            ->latest()
            ->get();
    }

    public function update(Request $request, $id)
    {
        $listing = ProductListing::findOrFail($id);
        if (!RoleAccess::ownsOrAdmin(auth()->user(), $listing->seller_id)) {
            abort(403, 'You can only edit your own listings.');
        }

        $validated = $request->validate([
            'commodity_id' => 'sometimes|required|exists:commodities,id',
            'quantity' => 'sometimes|required|numeric|min:0.01',
            'unit' => 'sometimes|required|string|max:50',
            'grade' => 'nullable|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'region' => 'sometimes|required|string|max:255',
            'district' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:available,sold,expired',
        ]);

        // Optional image replace on update
        foreach (['featured_image', 'image_2', 'image_3'] as $field) {
            if ($request->file($field)) {
                try {
                    $path = $this->storeListingImage($request->file($field), $field);
                    if ($path) {
                        $validated[$field] = $path;
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Update image {$field}: ".$e->getMessage());
                }
            }
        }

        $listing->update($validated);

        return response()->json([
            'message' => 'Listing updated successfully.',
            'listing' => $listing->fresh()->load('commodity'),
        ]);
    }

    public function destroy($id)
    {
        $listing = ProductListing::findOrFail($id);
        if (!RoleAccess::ownsOrAdmin(auth()->user(), $listing->seller_id)) {
            abort(403, 'You can only edit your own listings.');
        }

        $listing->delete();

        return response()->json([
            'message' => 'Listing deleted successfully.',
        ]);
    }
}
