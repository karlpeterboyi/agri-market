<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialInstitution;
use App\Models\FinancialInstitutionCategory;
use App\Models\InsuranceProduct;
use App\Models\LoanProduct;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Financial institution self-service:
 * register institution profile → manage loan + insurance products.
 */
class FinancialInstitutionController extends Controller
{
    public function index(Request $request)
    {
        $q = FinancialInstitution::with('category')
            ->where('active', true)
            ->when($request->institution_type, fn ($qq) => $qq->where('institution_type', $request->institution_type))
            ->when($request->boolean('featured'), fn ($qq) => $qq->where('featured', true))
            ->latest();

        return response()->json($q->paginate(20));
    }

    public function show($id)
    {
        $fi = FinancialInstitution::with(['category', 'loanProducts' => fn ($q) => $q->where('active', true), 'insuranceProducts' => fn ($q) => $q->where('active', true)])
            ->findOrFail($id);

        return response()->json($fi);
    }

    public function categories()
    {
        return response()->json(
            FinancialInstitutionCategory::where('active', true)->orderBy('name')->get()
        );
    }

    /** Authenticated institution profile for current user */
    public function mine(Request $request)
    {
        $fi = FinancialInstitution::with(['category', 'loanProducts', 'insuranceProducts'])
            ->where('owner_user_id', $request->user()->id)
            ->first();

        return response()->json(['data' => $fi]);
    }

    /** Register / update institution (role financier or admin) */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!RoleAccess::canManageFinance($user) && !RoleAccess::isAdmin($user)) {
            // Allow first-time upgrade path: if user has no FI yet and is admin/financier
            if (!in_array($user->role, ['financier', 'admin'], true)) {
                return response()->json([
                    'message' => 'Register as financier role or contact admin. Your role: '.$user->role,
                    'allowed_roles' => ['financier', 'admin'],
                ], 403);
            }
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'institution_type' => 'nullable|in:bank,mfi,sacco,insurer,fintech,government',
            'description' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:40',
            'contact_person' => 'nullable|string|max:255',
            'head_office' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'regions' => 'nullable|array',
            'financial_institution_category_id' => 'nullable|exists:financial_institution_categories,id',
        ]);

        $existing = FinancialInstitution::where('owner_user_id', $user->id)->first();
        if ($existing) {
            $existing->update($data);
            return response()->json(['message' => 'Institution updated', 'data' => $existing->fresh()], 200);
        }

        if (empty($data['financial_institution_category_id'])) {
            $cat = FinancialInstitutionCategory::first();
            if (!$cat) {
                $cat = FinancialInstitutionCategory::create([
                    'name' => 'Banks & MFIs',
                    'slug' => 'banks-mfis',
                    'active' => true,
                ]);
            }
            $data['financial_institution_category_id'] = $cat->id;
        }

        $data['owner_user_id'] = $user->id;
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        $data['institution_type'] = $data['institution_type'] ?? 'bank';
        $data['country'] = $data['country'] ?? 'Tanzania';
        $data['active'] = true;
        $data['verified'] = RoleAccess::isAdmin($user);

        $fi = FinancialInstitution::create($data);

        return response()->json(['message' => 'Institution registered', 'data' => $fi], 201);
    }

    public function dashboard(Request $request)
    {
        $fi = FinancialInstitution::where('owner_user_id', $request->user()->id)->first();
        if (!$fi && !RoleAccess::isAdmin($request->user())) {
            return response()->json(['message' => 'No institution profile. Register first.', 'data' => null], 404);
        }

        $fiId = $fi?->id;
        $loanQ = LoanProduct::query()->when($fiId, fn ($q) => $q->where('financial_institution_id', $fiId));
        $insQ = InsuranceProduct::query()->when($fiId, fn ($q) => $q->where('financial_institution_id', $fiId));

        if (RoleAccess::isAdmin($request->user()) && !$fiId) {
            $loanQ = LoanProduct::query();
            $insQ = InsuranceProduct::query();
        }

        // Credit desk: all non-draft applications (filter client-side by product if needed)
        $apps = \App\Models\LoanApplication::with(['product.financialInstitution', 'applicant'])
            ->where('status', '!=', 'draft')
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'institution' => $fi,
            'stats' => [
                'loan_products' => (clone $loanQ)->count(),
                'active_loan_products' => (clone $loanQ)->where('active', true)->count(),
                'insurance_products' => (clone $insQ)->count(),
                'active_insurance_products' => (clone $insQ)->where('active', true)->count(),
                'applications' => $apps->count(),
                'pending_applications' => $apps->whereIn('status', ['submitted', 'under_review'])->count(),
            ],
            'loan_products' => $loanQ->latest()->limit(20)->get(),
            'insurance_products' => $insQ->latest()->limit(20)->get(),
            'applications' => $apps,
        ]);
    }
}
