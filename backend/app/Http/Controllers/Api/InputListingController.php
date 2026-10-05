<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InputCategory;
use App\Models\InputListing;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

class InputListingController extends Controller
{
    public function index(Request $request)
    {
        $q = InputListing::with(['seller', 'category'])
            ->when($request->status, fn ($qq) => $qq->where('status', $request->status))
            ->when($request->region, fn ($qq) => $qq->where('region', $request->region))
            ->when($request->featured, fn ($qq) => $qq->where('featured', true))
            ->when($request->mine && auth()->check(), fn ($qq) => $qq->where('seller_id', auth()->id()))
            ->latest();

        if (!$request->boolean('mine') && !$request->boolean('all')) {
            $q->whereIn('status', ['available', 'active']);
        }

        return response()->json($q->paginate($request->integer('per_page', 24)));
    }

    public function myListings()
    {
        return response()->json(
            InputListing::with('category')
                ->where('seller_id', auth()->id())
                ->latest()
                ->get()
        );
    }

    public function store(Request $request)
    {
        // Commercial roles must subscribe (farmers/admin exempt)
        $role = strtolower((string) (auth()->user()->role ?? ''));
        if (!in_array($role, ['farmer', 'admin'], true)) {
            $has = \App\Models\UserSubscription::where('user_id', auth()->id())
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()->toDateString());
                })->exists();
            if (!$has) {
                return response()->json([
                    'message' => 'An active subscription is required to list farm inputs.',
                    'code' => 'subscription_required',
                    'subscribe_url' => '/finance/subscriptions',
                ], 402);
            }
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!RoleAccess::canSellInputs($user)) {
            return response()->json([
                'message' => 'Only agrodealers (or admin) can create farm-input listings.',
                'your_role' => RoleAccess::role($user),
                'allowed_roles' => ['agrodealer', 'admin'],
            ], 403);
        }

        $data = $request->validate([
            'input_category_id' => 'nullable|exists:input_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'brand' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'featured' => 'nullable|boolean',
            'status' => 'nullable|string|max:30',
        ]);

        if (empty($data['input_category_id'])) {
            $cat = InputCategory::query()->first();
            if (!$cat) {
                $cat = InputCategory::create([
                    'name' => 'General',
                    'slug' => 'general',
                ]);
            }
            $data['input_category_id'] = $cat->id;
        }

        $data['seller_id'] = $user->id;
        $data['region'] = $data['region'] ?: 'Tanzania';
        $data['district'] = $data['district'] ?: 'Unknown';
        $data['status'] = $data['status'] ?? 'available';
        $data['featured'] = (bool) ($data['featured'] ?? false);
        $data['description'] = $data['description'] ?? $data['name'];

        $item = InputListing::create($data);

        return response()->json($item->load('category'), 201);
    }

    public function show($id)
    {
        return response()->json(
            InputListing::with(['seller', 'category'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $item = InputListing::findOrFail($id);
        $user = auth()->user();

        if (!RoleAccess::ownsOrAdmin($user, $item->seller_id)) {
            return response()->json(['message' => 'You can only manage your own input listings.'], 403);
        }

        $data = $request->validate([
            'input_category_id' => 'nullable|exists:input_categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:50',
            'brand' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'featured' => 'nullable|boolean',
            'status' => 'nullable|string|max:30',
        ]);

        $item->update($data);

        return response()->json($item->fresh()->load('category'));
    }

    public function destroy($id)
    {
        $item = InputListing::findOrFail($id);
        $user = auth()->user();

        if (!RoleAccess::ownsOrAdmin($user, $item->seller_id)) {
            return response()->json(['message' => 'You can only manage your own input listings.'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
