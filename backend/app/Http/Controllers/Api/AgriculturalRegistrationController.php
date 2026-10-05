<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgriculturalRegistration;
use App\Models\Farm;
use Illuminate\Http\Request;

class AgriculturalRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AgriculturalRegistration::with(['farm', 'user:id,name,phone']);

        if (!in_array($user->role ?? '', ['admin', 'government_officer'])) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_type' => 'required|in:farm,trader,input_dealer,processor,exporter,cooperative',
            'farm_id' => 'nullable|exists:farms,id',
            'business_name' => 'nullable|string|max:255',
            'region' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'ward' => 'nullable|string|max:100',
            'details' => 'nullable|array',
        ]);

        if (!empty($validated['farm_id'])) {
            Farm::where('id', $validated['farm_id'])
                ->where('owner_id', auth()->id())
                ->firstOrFail();
        }

        $registration = AgriculturalRegistration::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return response()->json($registration, 201);
    }

    public function show(AgriculturalRegistration $agriculturalRegistration)
    {
        $this->authorizeView($agriculturalRegistration);

        return response()->json(
            $agriculturalRegistration->load(['farm', 'user', 'approver'])
        );
    }

    public function review(Request $request, AgriculturalRegistration $agriculturalRegistration)
    {
        $this->authorizeGovernment();

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,suspended',
            'notes' => 'nullable|string|max:2000',
            'expires_at' => 'nullable|date',
        ]);

        $agriculturalRegistration->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'approved_by' => auth()->id(),
            'issued_at' => $validated['status'] === 'approved' ? now() : null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Registration reviewed.',
            'data' => $agriculturalRegistration->fresh(),
        ]);
    }

    protected function authorizeView(AgriculturalRegistration $reg): void
    {
        $user = auth()->user();
        if ($reg->user_id !== $user->id && !in_array($user->role ?? '', ['admin', 'government_officer'])) {
            abort(403);
        }
    }

    protected function authorizeGovernment(): void
    {
        if (!in_array(auth()->user()->role ?? '', ['admin', 'government_officer'])) {
            abort(403);
        }
    }
}
