<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\LoanProductResource;
use App\Models\FinancialInstitution;
use App\Models\LoanProduct;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoanProductController extends Controller
{
    public function index(Request $request)
    {
        $query = LoanProduct::with('financialInstitution')
            ->where('active', true);

        if ($request->filled('loan_type')) {
            $query->where('loan_type', $request->loan_type);
        }
        if ($request->filled('financial_institution_id')) {
            $query->where('financial_institution_id', $request->financial_institution_id);
        }
        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }
        if ($request->filled('min_amount')) {
            $query->where('maximum_amount', '>=', $request->min_amount);
        }

        return LoanProductResource::collection(
            $query->orderByDesc('featured')->orderBy('name')->paginate(20)
        );
    }

    public function show(LoanProduct $loanProduct)
    {
        return new LoanProductResource(
            $loanProduct->load('financialInstitution')
        );
    }

    public function store(Request $request)
    {
        $fi = $this->resolveInstitution($request);
        if (!$fi) {
            return response()->json(['message' => 'Register a financial institution profile first.'], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'loan_type' => 'required|string|max:100',
            // input_credit | equipment | working_capital | warehouse_receipt | agribusiness | livestock
            'minimum_amount' => 'required|numeric|min:0',
            'maximum_amount' => 'required|numeric|gte:minimum_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'minimum_duration_months' => 'required|integer|min:1',
            'maximum_duration_months' => 'required|integer|gte:minimum_duration_months',
            'processing_fee' => 'nullable|numeric|min:0',
            'requires_collateral' => 'nullable|boolean',
            'online_application' => 'nullable|boolean',
            'eligibility' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'featured' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $product = LoanProduct::create([
            ...$validated,
            'financial_institution_id' => $fi->id,
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
            'requires_collateral' => $validated['requires_collateral'] ?? false,
            'online_application' => $validated['online_application'] ?? true,
            'featured' => $validated['featured'] ?? false,
            'active' => $validated['active'] ?? true,
        ]);

        return (new LoanProductResource($product->load('financialInstitution')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, LoanProduct $loanProduct)
    {
        $this->assertCanManage($request, $loanProduct);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'loan_type' => 'sometimes|required|string|max:100',
            'minimum_amount' => 'sometimes|required|numeric|min:0',
            'maximum_amount' => 'sometimes|required|numeric',
            'interest_rate' => 'sometimes|required|numeric|min:0|max:100',
            'minimum_duration_months' => 'sometimes|required|integer|min:1',
            'maximum_duration_months' => 'sometimes|required|integer',
            'processing_fee' => 'nullable|numeric|min:0',
            'requires_collateral' => 'nullable|boolean',
            'online_application' => 'nullable|boolean',
            'eligibility' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'featured' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $loanProduct->update($validated);

        return new LoanProductResource($loanProduct->fresh()->load('financialInstitution'));
    }

    public function destroy(Request $request, LoanProduct $loanProduct)
    {
        $this->assertCanManage($request, $loanProduct);
        $loanProduct->update(['active' => false]);

        return response()->json(['message' => 'Loan product deactivated.']);
    }

    protected function resolveInstitution(Request $request): ?FinancialInstitution
    {
        $user = $request->user();
        if (RoleAccess::isAdmin($user) && $request->filled('financial_institution_id')) {
            return FinancialInstitution::find($request->financial_institution_id);
        }

        $fi = FinancialInstitution::where('owner_user_id', $user->id)->first();
        if ($fi) {
            return $fi;
        }

        if (!RoleAccess::canManageFinance($user) && !RoleAccess::isAdmin($user)) {
            abort(403, 'Only financiers or admin can manage loan products. Your role: '.($user->role ?? ''));
        }

        return null;
    }

    protected function assertCanManage(Request $request, LoanProduct $product): void
    {
        $user = $request->user();
        if (RoleAccess::isAdmin($user)) {
            return;
        }
        $fi = FinancialInstitution::where('owner_user_id', $user->id)->first();
        if (!$fi || (int) $fi->id !== (int) $product->financial_institution_id) {
            abort(403, 'You can only manage products for your institution.');
        }
    }
}
