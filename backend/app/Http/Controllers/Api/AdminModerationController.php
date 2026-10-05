<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Crop;
use App\Models\InputListing;
use App\Models\MachineryListing;
use App\Models\ProductListing;
use App\Models\Service;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

/**
 * Admin CRUD across marketplace objects and master catalogs.
 */
class AdminModerationController extends Controller
{
    protected function bootAdmin(): void
    {
        RoleAccess::assertAdmin(auth()->user());
    }

    public function productListings()
    {
        $this->bootAdmin();
        return ProductListing::with(['commodity', 'seller'])->latest()->get();
    }

    public function updateProductListing(Request $request, $id)
    {
        $this->bootAdmin();
        $item = ProductListing::findOrFail($id);
        $item->update($request->only([
            'quantity', 'unit', 'grade', 'price', 'region', 'district', 'description', 'status',
        ]));
        return $item->fresh()->load(['commodity', 'seller']);
    }

    public function deleteProductListing($id)
    {
        $this->bootAdmin();
        ProductListing::findOrFail($id)->delete();
        return response()->json(['message' => 'Listing deleted']);
    }

    public function services()
    {
        $this->bootAdmin();
        return Service::with(['provider', 'category'])->latest()->get();
    }

    public function updateService(Request $request, $id)
    {
        $this->bootAdmin();
        $item = Service::findOrFail($id);
        $item->update($request->only([
            'title', 'description', 'price', 'pricing_type', 'region', 'district', 'phone', 'status',
        ]));
        return $item->fresh();
    }

    public function deleteService($id)
    {
        $this->bootAdmin();
        Service::findOrFail($id)->delete();
        return response()->json(['message' => 'Service deleted']);
    }

    public function machinery()
    {
        $this->bootAdmin();
        return MachineryListing::with(['owner', 'category', 'brand'])->latest()->get();
    }

    public function storeMachinery(Request $request)
    {
        $this->bootAdmin();
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
            'owner_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,active,booked,sold,maintenance',
        ]);

        $data['owner_id'] = $data['owner_id'] ?? auth()->id();
        $data['condition'] = $data['condition'] ?? 'Used';
        $data['for_sale'] = array_key_exists('for_sale', $data) ? (bool) $data['for_sale'] : !empty($data['sale_price']);
        $data['for_rent'] = array_key_exists('for_rent', $data) ? (bool) $data['for_rent'] : true;
        $data['operator_included'] = (bool) ($data['operator_included'] ?? false);
        $data['available'] = true;
        $data['verified'] = true;
        $data['featured'] = false;
        $data['status'] = $data['status'] ?? 'active';

        $item = MachineryListing::create($data);
        return response()->json($item->load(['owner', 'category', 'brand', 'model']), 201);
    }

    public function updateMachinery(Request $request, $id)
    {
        $this->bootAdmin();
        $item = MachineryListing::findOrFail($id);
        $validated = $request->validate([
            'machinery_category_id' => 'sometimes|exists:machinery_categories,id',
            'machinery_brand_id' => 'sometimes|exists:machinery_brands,id',
            'machinery_model_id' => 'sometimes|exists:machinery_models,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'sale_price' => 'nullable|numeric|min:0',
            'rental_price' => 'nullable|numeric|min:0',
            'rental_period' => 'nullable|in:hour,day,week,month',
            'for_sale' => 'nullable|boolean',
            'for_rent' => 'nullable|boolean',
            'region' => 'sometimes|string|max:100',
            'district' => 'sometimes|string|max:100',
            'status' => 'nullable|in:draft,active,booked,sold,maintenance',
            'available' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'condition' => 'nullable|string|max:50',
        ]);
        $item->update($validated);
        return $item->fresh();
    }

    public function deleteMachinery($id)
    {
        $this->bootAdmin();
        MachineryListing::findOrFail($id)->delete();
        return response()->json(['message' => 'Machinery listing deleted']);
    }

    public function inputs()
    {
        $this->bootAdmin();
        return InputListing::with(['seller', 'category'])->latest()->get();
    }

    public function updateInput(Request $request, $id)
    {
        $this->bootAdmin();
        $item = InputListing::findOrFail($id);
        $item->update($request->only([
            'name', 'description', 'price', 'stock', 'unit', 'brand', 'region', 'district', 'status', 'featured',
        ]));
        return $item->fresh();
    }

    public function deleteInput($id)
    {
        $this->bootAdmin();
        InputListing::findOrFail($id)->delete();
        return response()->json(['message' => 'Input listing deleted']);
    }

    public function commodities()
    {
        $this->bootAdmin();
        return Commodity::latest()->get();
    }

    public function storeCommodity(Request $request)
    {
        $this->bootAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'commodity_category_id' => 'nullable|exists:commodity_categories,id',
        ]);
        return response()->json(Commodity::create($data), 201);
    }

    public function updateCommodity(Request $request, $id)
    {
        $this->bootAdmin();
        $item = Commodity::findOrFail($id);
        $item->update($request->only(['name', 'description', 'commodity_category_id']));
        return $item;
    }

    public function deleteCommodity($id)
    {
        $this->bootAdmin();
        Commodity::findOrFail($id)->delete();
        return response()->json(['message' => 'Commodity deleted']);
    }

    public function crops()
    {
        $this->bootAdmin();
        return Crop::latest()->get();
    }

    public function storeCrop(Request $request)
    {
        $this->bootAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);
        $data['active'] = true;
        return response()->json(Crop::create($data), 201);
    }

    public function updateCrop(Request $request, $id)
    {
        $this->bootAdmin();
        $item = Crop::findOrFail($id);
        $item->update($request->only(['name', 'scientific_name', 'category', 'description', 'active']));
        return $item;
    }

    public function deleteCrop($id)
    {
        $this->bootAdmin();
        Crop::findOrFail($id)->delete();
        return response()->json(['message' => 'Crop deleted']);
    }
}
