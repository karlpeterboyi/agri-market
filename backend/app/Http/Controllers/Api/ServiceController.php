<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

/**
 * Service = the offering a provider sells on the marketplace (has a base price).
 * Package = optional pricing tier under a service (e.g. "Half day", "Full day").
 * Packages are optional; creating a Service alone is enough for it to appear.
 */
class ServiceController extends Controller
{
    public function categories()
    {
        return response()->json(ServiceCategory::orderBy('name')->get());
    }

    public function index(Request $request)
    {
        $query = Service::with(['category', 'provider'])
            ->where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            });

        if ($request->filled('category')) {
            $query->where('service_category_id', $request->category);
        }
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'ILIKE', "%{$request->keyword}%")
                    ->orWhere('description', 'ILIKE', "%{$request->keyword}%");
            });
        }
        if ($request->filled('verified')) {
            $query->where('verified', true);
        }
        if ($request->filled('featured')) {
            $query->where('featured', true);
        }

        return $query->latest()->paginate(20);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated. Login as a service provider.'], 401);
        }
        if (!RoleAccess::canSellServices($user)) {
            return response()->json([
                'message' => 'Only service providers (role=provider) or admin can create services.',
                'your_role' => RoleAccess::role($user),
                'allowed_roles' => ['provider', 'admin'],
            ], 403);
        }

        $validated = $request->validate([
            'service_category_id' => 'nullable|exists:service_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'pricing_type' => 'nullable|string|max:50',
            'unit' => 'nullable|string',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'status' => 'nullable|string|max:30',
            // optional: also create a default package with same price
            'create_default_package' => 'nullable|boolean',
        ]);

        if (empty($validated['service_category_id'])) {
            $cat = ServiceCategory::query()->first();
            if (!$cat) {
                $cat = ServiceCategory::create([
                    'name' => 'General',
                    'slug' => 'general',
                    'description' => 'General services',
                ]);
            }
            $validated['service_category_id'] = $cat->id;
        }

        $createPkg = $validated['create_default_package'] ?? true;
        unset($validated['create_default_package']);

        $validated['provider_id'] = $user->id;
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['pricing_type'] = $validated['pricing_type'] ?? 'fixed';
        $validated['description'] = $validated['description'] ?? $validated['title'];
        $validated['phone'] = $validated['phone'] ?? $user->phone;

        $service = Service::create($validated);

        // Optional default package so "plan price" is not a separate required step
        if ($createPkg && class_exists(ServicePackage::class)) {
            try {
                ServicePackage::create([
                    'service_id' => $service->id,
                    'name' => 'Standard',
                    'description' => $service->description,
                    'price' => $service->price,
                    'unit' => $validated['unit'] ?? null,
                    'active' => true,
                ]);
            } catch (\Throwable $e) {
                // non-fatal
            }
        }

        return response()->json([
            'message' => 'Service published. Packages are optional pricing tiers under this service.',
            'data' => $service->load(['category', 'packages']),
        ], 201);
    }

    public function show(Service $service)
    {
        return $service->load(['provider', 'category', 'packages']);
    }

    public function provider(User $user)
    {
        return Service::with('category')
            ->where('provider_id', $user->id)
            ->latest()
            ->get();
    }

    public function stats()
    {
        return response()->json([
            'total_services' => Service::count(),
            'verified' => Service::where('verified', true)->count(),
            'featured' => Service::where('featured', true)->count(),
            'providers' => User::where('role', 'provider')->count(),
            'categories' => ServiceCategory::count(),
        ]);
    }

    public function myServices(Request $request)
    {
        $services = Service::with(['category', 'packages'])
            ->where('provider_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        if (!RoleAccess::ownsOrAdmin(auth()->user(), $service->provider_id)) {
            return response()->json(['message' => 'You can only manage your own services.'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'pricing_type' => 'nullable|string|max:50',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'status' => 'nullable|string|max:30',
            'service_category_id' => 'nullable|exists:service_categories,id',
        ]);

        $service->update($data);

        return response()->json($service->fresh()->load(['category', 'packages']));
    }

    public function destroy(Service $service)
    {
        if (!RoleAccess::ownsOrAdmin(auth()->user(), $service->provider_id)) {
            return response()->json(['message' => 'You can only manage your own services.'], 403);
        }

        $service->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
