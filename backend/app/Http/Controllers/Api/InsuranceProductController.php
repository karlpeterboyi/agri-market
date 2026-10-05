<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialInstitution;
use App\Models\InsuranceApplication;
use App\Models\InsuranceProduct;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InsuranceProductController extends Controller
{
    public function index(Request $request)
    {
        $q = InsuranceProduct::with('institution')
            ->where('active', true)
            ->when($request->insurance_type, fn ($qq) => $qq->where('insurance_type', $request->insurance_type))
            ->when($request->financial_institution_id, fn ($qq) => $qq->where('financial_institution_id', $request->financial_institution_id))
            ->when($request->boolean('featured'), fn ($qq) => $qq->where('featured', true))
            ->latest();

        return response()->json($q->paginate(20));
    }

    public function show($id)
    {
        return response()->json(
            InsuranceProduct::with('institution')->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $fi = $this->ownedInstitution($request);
        if (!$fi) {
            return response()->json(['message' => 'Register a financial institution first.'], 422);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'insurance_type' => 'required|in:crop,livestock,equipment,weather,life,multi',
            'description' => 'nullable|string',
            'premium_rate' => 'nullable|numeric|min:0|max:1',
            'minimum_premium' => 'nullable|numeric|min:0',
            'maximum_cover' => 'nullable|numeric|min:0',
            'coverage_period_months' => 'nullable|integer|min:1',
            'covered_risks' => 'nullable|array',
            'eligibility' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'online_application' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $data['financial_institution_id'] = $fi->id;
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        $data['active'] = $data['active'] ?? true;
        $data['online_application'] = $data['online_application'] ?? true;

        $product = InsuranceProduct::create($data);

        return response()->json($product->load('institution'), 201);
    }

    public function update(Request $request, $id)
    {
        $product = InsuranceProduct::findOrFail($id);
        $this->assertOwnsProduct($request, $product);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'insurance_type' => 'sometimes|in:crop,livestock,equipment,weather,life,multi',
            'description' => 'nullable|string',
            'premium_rate' => 'nullable|numeric|min:0|max:1',
            'minimum_premium' => 'nullable|numeric|min:0',
            'maximum_cover' => 'nullable|numeric|min:0',
            'coverage_period_months' => 'nullable|integer|min:1',
            'covered_risks' => 'nullable|array',
            'eligibility' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'online_application' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $product->update($data);

        return response()->json($product->fresh()->load('institution'));
    }

    public function destroy(Request $request, $id)
    {
        $product = InsuranceProduct::findOrFail($id);
        $this->assertOwnsProduct($request, $product);
        $product->update(['active' => false]);

        return response()->json(['message' => 'Insurance product deactivated']);
    }

    public function apply(Request $request, $id)
    {
        $product = InsuranceProduct::where('active', true)->findOrFail($id);

        $data = $request->validate([
            'sum_insured' => 'required|numeric|min:1',
            'farm_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $premium = null;
        if ($product->premium_rate) {
            $premium = round($data['sum_insured'] * (float) $product->premium_rate, 2);
            if ($product->minimum_premium && $premium < $product->minimum_premium) {
                $premium = (float) $product->minimum_premium;
            }
        }

        $app = InsuranceApplication::create([
            'insurance_product_id' => $product->id,
            'applicant_id' => $request->user()->id,
            'sum_insured' => $data['sum_insured'],
            'premium_amount' => $premium,
            'status' => 'submitted',
            'farm_details' => $data['farm_details'] ?? null,
            'notes' => $data['notes'] ?? null,
            'submitted_at' => now(),
        ]);

        return response()->json(['message' => 'Insurance application submitted', 'data' => $app], 201);
    }

    public function myApplications(Request $request)
    {
        return response()->json(
            InsuranceApplication::with('product.institution')
                ->where('applicant_id', $request->user()->id)
                ->latest()
                ->get()
        );
    }

    protected function ownedInstitution(Request $request): ?FinancialInstitution
    {
        $user = $request->user();
        if (RoleAccess::isAdmin($user) && $request->filled('financial_institution_id')) {
            return FinancialInstitution::find($request->financial_institution_id);
        }

        return FinancialInstitution::where('owner_user_id', $user->id)->first();
    }

    protected function assertOwnsProduct(Request $request, InsuranceProduct $product): void
    {
        $user = $request->user();
        if (RoleAccess::isAdmin($user)) {
            return;
        }
        $fi = FinancialInstitution::where('owner_user_id', $user->id)->first();
        if (!$fi || (int) $fi->id !== (int) $product->financial_institution_id) {
            abort(403, 'You can only manage your institution products.');
        }
    }
}
