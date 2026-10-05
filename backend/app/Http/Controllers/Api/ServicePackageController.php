<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

class ServicePackageController extends Controller
{
    public function index($service)
    {
        return ServicePackage::where('service_id', $service)
            ->when(!RoleAccess::isAdmin(auth()->user()), fn ($q) => $q->where('active', true))
            ->get();
    }

    public function myPackages()
    {
        $user = auth()->user();
        $query = ServicePackage::with('service')
            ->whereHas('service', function ($q) use ($user) {
                if (!RoleAccess::isAdmin($user)) {
                    $q->where('provider_id', $user->id);
                }
            });

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        RoleAccess::assertCanSellServices(auth()->user());

        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'duration' => 'nullable|string|max:100',
            'features' => 'nullable|array',
            'featured' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $service = Service::findOrFail($data['service_id']);
        if (!RoleAccess::ownsOrAdmin(auth()->user(), $service->provider_id)) {
            abort(403, 'You can only add packages to your own services.');
        }

        $data['active'] = $data['active'] ?? true;
        $package = ServicePackage::create($data);

        return response()->json($package->load('service'), 201);
    }

    public function update(Request $request, $id)
    {
        $package = ServicePackage::with('service')->findOrFail($id);
        if (!RoleAccess::ownsOrAdmin(auth()->user(), $package->service->provider_id)) {
            abort(403, 'You can only edit your own packages.');
        }

        $package->update($request->only([
            'name', 'description', 'price', 'unit', 'duration', 'features', 'featured', 'active',
        ]));

        return $package->fresh()->load('service');
    }

    public function destroy($id)
    {
        $package = ServicePackage::with('service')->findOrFail($id);
        if (!RoleAccess::ownsOrAdmin(auth()->user(), $package->service->provider_id)) {
            abort(403, 'You can only delete your own packages.');
        }
        $package->delete();

        return response()->json(['message' => 'Package deleted']);
    }
}
