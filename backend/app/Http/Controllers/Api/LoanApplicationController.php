<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\LoanApplicationResource;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Services\Finance\LoanWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Support\NmbLinkPolicy;

class LoanApplicationController extends Controller
{
    /**
     * List applications (own for farmers, all for admin/FI)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = strtolower(trim((string) ($user->role ?? '')));
        $query = LoanApplication::with(['product.financialInstitution', 'applicant']);

        if ($role === 'admin') {
            // platform-wide
        } elseif (in_array($role, ['financier', 'financial_institution'], true)) {
            $fiIds = \App\Models\FinancialInstitution::where('owner_user_id', $user->id)->pluck('id');

            // Link FI by email if owner_user_id was never set
            if ($fiIds->isEmpty() && $user->email) {
                $matched = \App\Models\FinancialInstitution::where('email', $user->email)->pluck('id');
                if ($matched->isNotEmpty()) {
                    \App\Models\FinancialInstitution::whereIn('id', $matched)
                        ->whereNull('owner_user_id')
                        ->update(['owner_user_id' => $user->id]);
                    $fiIds = $matched;
                }
            }

            // mine=1 → only this institution's products; default → all non-draft (credit desk)
            if ($request->boolean('mine') && $fiIds->isNotEmpty()) {
                $query->whereHas('product', fn ($q) => $q->whereIn('financial_institution_id', $fiIds));
            } elseif ($request->boolean('mine') && $fiIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            }
            // else: all applications (shared FI inbox) so submitted farmer apps are visible

            if (!$request->filled('status')) {
                $query->where('status', '!=', 'draft');
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('loan_product_id')) {
            $query->where('loan_product_id', $request->loan_product_id);
        }

        return LoanApplicationResource::collection(
            $query->latest()->paginate(50)
        );
    }

    /**
     * Farmer creates a new loan application (starts in draft)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_product_id' => 'required|exists:loan_products,id',
            'requested_amount' => 'required|numeric|min:1000',
            'repayment_period_months' => 'required|integer|min:1|max:120',
            'purpose' => 'required|string|max:1000',
            'annual_income' => 'nullable|numeric|min:0',
            'farm_size' => 'nullable|numeric|min:0',
            'farm_size_unit' => 'nullable|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $product = LoanProduct::where('active', true)->findOrFail($validated['loan_product_id']);

        if ($validated['requested_amount'] < $product->minimum_amount ||
            $validated['requested_amount'] > $product->maximum_amount) {
            return response()->json([
                'message' => "Requested amount must be between {$product->minimum_amount} and {$product->maximum_amount}.",
            ], 422);
        }

        if ($validated['repayment_period_months'] < $product->minimum_duration_months ||
            $validated['repayment_period_months'] > $product->maximum_duration_months) {
            return response()->json([
                'message' => "Repayment period must be between {$product->minimum_duration_months} and {$product->maximum_duration_months} months.",
            ], 422);
        }

        $application = LoanApplication::create([
            ...$validated,
            'user_id' => auth()->id(),
            'application_number' => 'LA-' . strtoupper(Str::random(10)),
            'status' => 'draft',
        ]);

        return (new LoanApplicationResource(
            $application->load(['product.financialInstitution', 'applicant'])
        ))->response()->setStatusCode(201);
    }

    public function show(LoanApplication $loanApplication)
    {
        $this->authorizeView($loanApplication);

        return new LoanApplicationResource(
            $loanApplication->load([
                'product.financialInstitution',
                'applicant',
                'documents',
                'guarantors',
                'collaterals',
                'repayments',
                'disbursements',
            ])
        );
    }

    public function update(Request $request, LoanApplication $loanApplication)
    {
        if ($loanApplication->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($loanApplication->status, ['draft', 'rejected'])) {
            return response()->json([
                'message' => 'Only draft or rejected applications can be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'requested_amount' => 'sometimes|required|numeric|min:1000',
            'repayment_period_months' => 'sometimes|required|integer|min:1|max:120',
            'purpose' => 'sometimes|required|string|max:1000',
            'annual_income' => 'nullable|numeric|min:0',
            'farm_size' => 'nullable|numeric|min:0',
            'farm_size_unit' => 'nullable|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $loanApplication->update($validated);

        return new LoanApplicationResource($loanApplication->fresh()->load('product'));
    }

    public function destroy(LoanApplication $loanApplication)
    {
        if ($loanApplication->user_id !== auth()->id()) {
            abort(403);
        }

        if ($loanApplication->status !== 'draft') {
            return response()->json(['message' => 'Only draft applications can be deleted.'], 422);
        }

        $loanApplication->delete();

        return response()->json(['message' => 'Application deleted.']);
    }

    // ==================== WORKFLOW ACTIONS ====================

    public function submit(LoanApplication $loanApplication, LoanWorkflowService $workflow)
    {
        $this->authorizeOwner($loanApplication);

        try {
            $workflow->submit($loanApplication);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Could not submit application: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Loan application submitted for review.',
            'data' => new LoanApplicationResource($loanApplication->fresh()),
        ]);
    }

    public function review(LoanApplication $loanApplication, LoanWorkflowService $workflow)
    {
        $this->authorizeInstitution();

        $workflow->startReview($loanApplication);

        return response()->json([
            'message' => 'Loan moved to under review.',
            'data' => new LoanApplicationResource($loanApplication->fresh()),
        ]);
    }

    public function approve(LoanApplication $loanApplication, LoanWorkflowService $workflow)
    {
        $this->authorizeInstitution();

        $workflow->approve($loanApplication);

        return response()->json([
            'message' => 'Loan approved.',
            'data' => new LoanApplicationResource($loanApplication->fresh()),
        ]);
    }

    public function reject(Request $request, LoanApplication $loanApplication, LoanWorkflowService $workflow)
    {
        $this->authorizeInstitution();

        $request->validate(['remarks' => 'required|string|max:1000']);

        $workflow->reject($loanApplication, $request->remarks);

        return response()->json([
            'message' => 'Loan rejected.',
            'data' => new LoanApplicationResource($loanApplication->fresh()),
        ]);
    }

    public function disburse(Request $request, LoanApplication $loanApplication, LoanWorkflowService $workflow)
    {
        $this->authorizeInstitution();

        $farmer = $loanApplication->applicant ?: \App\Models\User::find($loanApplication->user_id);
        $linkCheck = NmbLinkPolicy::assertCanDisburseLoan($farmer);
        if (!$linkCheck['ok']) {
            return response()->json([
                'message' => $linkCheck['message'],
                'policy' => 'nmb_link_required_for_disburse',
            ], $linkCheck['code'] ?? 422);
        }
        // Prefill account from linked NMB if not provided
        if (!empty($linkCheck['link'])) {
            $request->merge([
                'account_number' => $request->input('account_number') ?: $linkCheck['link']->account_number,
                'account_name' => $request->input('account_name') ?: $linkCheck['link']->account_name,
                'bank_name' => $request->input('bank_name') ?: 'NMB',
            ]);
        }

        // link prefill may have been merged onto request
        $data = $request->validate([
            'approved_amount' => 'required|numeric|min:0',
            'disbursed_amount' => 'required|numeric|min:0',
            'channel' => 'required|string|max:50', // bank, mobile_money, wallet
            'account_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'mobile_network' => 'nullable|string',
            'phone_number' => 'nullable|string',
        ]);

        if (empty($data['account_number']) && !empty($linkCheck['link'])) {
            $data['account_number'] = $linkCheck['link']->account_number;
            $data['account_name'] = $data['account_name'] ?? $linkCheck['link']->account_name;
            $data['bank_name'] = $data['bank_name'] ?? 'NMB';
            $data['nmb_account_id'] = $linkCheck['link']->nmb_account_id;
        }


        $disbursement = $workflow->disburse($loanApplication, $data);

        return response()->json([
            'message' => 'Loan disbursed successfully. NMB OBP payout attempted (see workflow log / disbursement channel).',
            'disbursement_id' => $disbursement->id ?? null,
            'reference' => $disbursement->reference_number ?? null,
            'channel' => $disbursement->channel ?? null,
            'status' => $disbursement->status ?? null,
        ]);
    }

    public function generateSchedule(LoanApplication $loanApplication, LoanWorkflowService $workflow)
    {
        $this->authorizeInstitution();

        $workflow->generateRepaymentSchedule($loanApplication);

        return response()->json(['message' => 'Repayment schedule generated.']);
    }

    public function complete(LoanApplication $loanApplication, LoanWorkflowService $workflow)
    {
        $this->authorizeInstitution();

        $workflow->complete($loanApplication);

        return response()->json(['message' => 'Loan marked as completed.']);
    }

    // ==================== HELPERS ====================

    protected function authorizeView(LoanApplication $app): void
    {
        $user = auth()->user();
        if ($app->user_id !== $user->id && !in_array($user->role ?? '', ['admin', 'financial_institution', 'financier'], true)) {
            abort(403);
        }
    }

    protected function authorizeOwner(LoanApplication $app): void
    {
        if ($app->user_id !== auth()->id()) {
            abort(403, 'You can only act on your own applications.');
        }
    }

    protected function authorizeInstitution(): void
    {
        if (!in_array(auth()->user()->role ?? '', ['admin', 'financial_institution', 'financier'], true)) {
            abort(403, 'Only financial institutions or admins can perform this action.');
        }
    }
}
