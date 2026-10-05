<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineryListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\RoleAccess;

class MachineryListingController extends Controller
{
    /**
     * Public marketplace
     */
    public function index(Request $request)
    {
        $query = MachineryListing::with([
            'owner',
            'category',
            'brand',
            'model',
            'images',
            'reviews'
        ]);

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where('title', 'ILIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'ILIKE', '%' . $request->search . '%');

            });

        }

        if ($request->filled('category')) {

            $query->where('machinery_category_id', $request->category);

        }

        if ($request->filled('brand')) {

            $query->where('machinery_brand_id', $request->brand);

        }

        if ($request->filled('model')) {

            $query->where('machinery_model_id', $request->model);

        }

        if ($request->filled('region')) {

            $query->where('region', $request->region);

        }

        if ($request->filled('for_sale')) {

            $query->where('for_sale', true);

        }

        if ($request->filled('for_rent')) {

            $query->where('for_rent', true);

        }

        if ($request->filled('available')) {

            $query->where('available', true);

        }

        if ($request->filled('featured')) {

            $query->where('featured', true);

        }

        if ($request->filled('min_price')) {

            $query->where('rental_price', '>=', $request->min_price);

        }

        if ($request->filled('max_price')) {

            $query->where('rental_price', '<=', $request->max_price);

        }

        if ($request->filled('horsepower')) {

            $query->where('horsepower', '>=', $request->horsepower);

        }

        return $query
            ->latest()
            ->paginate(12);
    }

    /**
     * Create listing
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated. Login as farmer or admin to create machinery listings.',
            ], 401);
        }
        if (!RoleAccess::canSellMachinery($user)) {
            return response()->json([
                'message' => 'Machinery listings are allowed for roles: farmer, admin only. (Service providers use Services, not Machinery.)',
                'your_role' => RoleAccess::role($user),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'allowed_roles' => ['farmer', 'admin'],
            ], 403);
        }

        $data = $request->validate([
            'machinery_category_id' => 'required|exists:machinery_categories,id',
            'machinery_brand_id' => 'required|exists:machinery_brands,id',
            'machinery_model_id' => 'required|exists:machinery_models,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'manufacture_year' => 'nullable|integer|min:1950|max:2100',
            'condition' => 'nullable|string|max:50',
            'horsepower' => 'nullable|integer|min:0',
            'engine_hours' => 'nullable|integer|min:0',
            'fuel_type' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'sale_price' => 'nullable|numeric|min:0',
            'rental_price' => 'nullable|numeric|min:0',
            'rental_period' => 'nullable|in:hour,day,week,month',
            'for_sale' => 'nullable|boolean',
            'for_rent' => 'nullable|boolean',
            'operator_included' => 'nullable|boolean',
            'region' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'ward' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'nullable|in:draft,active,booked,sold,maintenance',
        ]);

        $model = \App\Models\MachineryModel::findOrFail($data['machinery_model_id']);
        if ((int) $model->machinery_brand_id !== (int) $data['machinery_brand_id']) {
            return response()->json(['message' => 'Selected model does not belong to the selected brand.'], 422);
        }

        $data['owner_id'] = $user->id;
        $data['condition'] = $data['condition'] ?? 'Used';
        $data['for_sale'] = array_key_exists('for_sale', $data)
            ? (bool) $data['for_sale']
            : !empty($data['sale_price']);
        $data['for_rent'] = array_key_exists('for_rent', $data)
            ? (bool) $data['for_rent']
            : (!empty($data['rental_price']) || empty($data['sale_price']));
        $data['operator_included'] = (bool) ($data['operator_included'] ?? false);
        $data['available'] = true;
        $data['verified'] = false;
        $data['featured'] = false;
        $data['status'] = $data['status'] ?? 'active';

        if (!$data['for_sale'] && !$data['for_rent']) {
            $data['for_rent'] = true;
        }

        $listing = MachineryListing::create($data);

        return response()->json(
            $listing->load(['owner', 'category', 'brand', 'model']),
            201
        );
    }

    /**
     * Details page
     */
    public function show(MachineryListing $machineryListing)
    {
        return $machineryListing->load([

            'owner',

            'category',

            'brand',

            'model',

            'images',

            'reviews.user'

        ]);
    }

    /**
     * Update listing
     */
    public function update(Request $request, MachineryListing $machineryListing)
    {
        abort_unless(
            RoleAccess::ownsOrAdmin(Auth::user(), $machineryListing->owner_id),
            403,
            'You can only manage your own machinery listings.'
        );

        $validated = $request->validate([
            'machinery_category_id' => 'sometimes|exists:machinery_categories,id',
            'machinery_brand_id' => 'sometimes|exists:machinery_brands,id',
            'machinery_model_id' => 'sometimes|exists:machinery_models,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'manufacture_year' => 'nullable|integer|min:1950|max:2100',
            'condition' => 'nullable|string|max:50',
            'horsepower' => 'nullable|integer|min:0',
            'engine_hours' => 'nullable|integer|min:0',
            'fuel_type' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'sale_price' => 'nullable|numeric|min:0',
            'rental_price' => 'nullable|numeric|min:0',
            'rental_period' => 'nullable|in:hour,day,week,month',
            'for_sale' => 'nullable|boolean',
            'for_rent' => 'nullable|boolean',
            'operator_included' => 'nullable|boolean',
            'region' => 'sometimes|required|string|max:100',
            'district' => 'sometimes|required|string|max:100',
            'ward' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'available' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'status' => 'nullable|in:draft,active,booked,sold,maintenance',
        ]);

        $machineryListing->update($validated);

        return $machineryListing->fresh()->load(['owner', 'category', 'brand', 'model']);
    }

    /**
     * Delete listing
     */
    public function destroy(MachineryListing $machineryListing)
    {
        abort_unless(
            RoleAccess::ownsOrAdmin(Auth::user(), $machineryListing->owner_id),
            403,
            'You can only manage your own machinery listings.'
        );

        $machineryListing->delete();

        return response()->json([
            'message'=>'Deleted successfully'
        ]);
    }

    /**
     * Featured machinery
     */
    public function featured()
    {
        return MachineryListing::with([
            'brand',
            'category'
        ])
        ->where('featured',true)
        ->latest()
        ->take(8)
        ->get();
    }

    /**
     * Related machinery
     */
    public function related(MachineryListing $machineryListing)
    {
        return MachineryListing::with([
            'brand',
            'category'
        ])
        ->where('machinery_category_id',
            $machineryListing->machinery_category_id
        )
        ->where('id','!=',$machineryListing->id)
        ->take(6)
        ->get();
    }

    /**
     * Owner listings
     */
    public function myListings()
    {
        return MachineryListing::with(['category', 'brand', 'model'])
            ->where('owner_id', Auth::id())
            ->latest()
            ->get();
    }
}