<?php

namespace App\Services\Finance;

use App\Events\LoanApproved;
use App\Events\LoanCompleted;
use App\Events\LoanDisbursed;
use App\Events\LoanRejected;
use App\Events\LoanSubmitted;
use App\Events\LoanUnderReview;
use App\Models\LoanApplication;
use App\Models\LoanDisbursement;
use App\Models\LoanRepayment;
use App\Models\LoanWorkflowLog;
use App\StateMachines\LoanStateMachine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Nmb\NmbPayoutService;

class LoanWorkflowService
{
    /**
     * Submit loan application
     */
    public function submit(
        LoanApplication $loan
    ): LoanApplication {
        if ($loan->status === 'submitted') {
            return $loan;
        }
        LoanStateMachine::assert(
            $loan->status,
            'submitted'
        );

        $loan->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->logWorkflow(
            $loan,
            'submitted',
            'draft',
            'submitted'
        );

        LoanSubmitted::dispatch($loan);

        return $loan;
    }

    /**
     * Move to review
     */
    public function startReview(
        LoanApplication $loan
    ): LoanApplication {
        LoanStateMachine::assert(
            $loan->status,
            'under_review'
        );

        $loan->update([
            'status' => 'under_review'
        ]);

        $this->logWorkflow(
            $loan,
            'review_started',
            'submitted',
            'under_review'
        );

        LoanUnderReview::dispatch($loan);

        return $loan;
    }

    /**
     * Reject application
     */
    public function reject(
        LoanApplication $loan,
        string $remarks
    ): LoanApplication {
        LoanStateMachine::assert(
            $loan->status,
            'rejected'
        );

        $loan->update([
            'status' => 'rejected',
            'remarks' => $remarks,
            'rejected_at' => now(),
        ]);

        $this->logWorkflow(
            $loan,
            'rejected',
            'under_review',
            'rejected',
            $remarks
        );

        LoanRejected::dispatch($loan);

        return $loan;
    }

    /**
     * Approve application
     */
    public function approve(
        LoanApplication $loan
    ): LoanApplication {
        LoanStateMachine::assert(
            $loan->status,
            'approved'
        );

        $loan->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->logWorkflow(
            $loan,
            'approved',
            'under_review',
            'approved'
        );

        LoanApproved::dispatch($loan);

        return $loan;
    }

    /**
     * Disburse funds
     */
    public function disburse(
        LoanApplication $loan,
        array $data
    ): LoanDisbursement {
        return DB::transaction(function () use ($loan, $data) {
            LoanStateMachine::assert(
                $loan->status,
                'disbursed'
            );

            $reference = 'DIS-' . strtoupper(Str::random(10));

            $nmbResult = app(NmbPayoutService::class)->disburseLoan([
                'amount' => (float) $data['disbursed_amount'],
                'reference' => $reference,
                'account_name' => $data['account_name'] ?? optional($loan->applicant)->name,
                'account_number' => $data['account_number'] ?? null,
                'to_account_id' => $data['nmb_account_id'] ?? null,
                'from_account_id' => $data['from_account_id'] ?? null,
                'user_id' => $loan->user_id,
            ]);

            $disbursement = LoanDisbursement::create([
                'loan_application_id' => $loan->id,
                'reference_number'    => $reference,
                'approved_amount'     => $data['approved_amount'],
                'disbursed_amount'    => $data['disbursed_amount'],
                'disbursement_date'   => now(),
                'channel'             => $data['channel'] ?? ($nmbResult['ok'] ? 'nmb_obp' : 'internal'),
                'account_name'        => $data['account_name'] ?? null,
                'account_number'      => $data['account_number'] ?? null,
                'bank_name'           => $data['bank_name'] ?? 'NMB',
                'mobile_network'      => $data['mobile_network'] ?? null,
                'phone_number'        => $data['phone_number'] ?? null,
                'status'              => $nmbResult['ok'] ? 'successful' : 'pending_bank',
            ]);

            // Attach NMB payload if column/meta exists is best-effort via remarks on workflow log
            $loan->update([
                'status' => 'disbursed'
            ]);

            $data['_nmb'] = $nmbResult;

            $this->logWorkflow(
                $loan,
                'disbursed',
                'approved',
                'disbursed',
                null,
                [
                    'amount' => $data['disbursed_amount'],
                    'channel' => $data['channel'] ?? 'nmb_obp',
                    'nmb' => $nmbResult,
                ]
            );

            LoanDisbursed::dispatch($loan);

            return $disbursement;
        });
    }

    /**
     * Generate repayment schedule
     */
    public function generateRepaymentSchedule(
        LoanApplication $loan
    ): void {
        $amount   = $loan->requested_amount;
        $months   = $loan->repayment_period_months;
        $interest = $loan->product->interest_rate;

        $monthlyPrincipal = $amount / $months;
        $monthlyInterest  = ($amount * ($interest / 100)) / 12;

        for ($i = 1; $i <= $months; $i++) {
            LoanRepayment::create([
                'loan_application_id' => $loan->id,
                'installment_number'  => $i,
                'due_date'            => now()->addMonths($i),
                'principal_amount'    => round($monthlyPrincipal, 2),
                'interest_amount'     => round($monthlyInterest, 2),
                'total_amount'        => round($monthlyPrincipal + $monthlyInterest, 2),
                'balance'             => round($monthlyPrincipal + $monthlyInterest, 2),
            ]);
        }
    }

    /**
     * Mark loan as completed when fully repaid
     */
    public function complete(
        LoanApplication $loan
    ): void {
        $remaining = $loan->repayments()
            ->where('status', '!=', 'paid')
            ->count();

        if ($remaining == 0) {
            LoanStateMachine::assert(
                $loan->status,
                'completed'
            );

            $loan->update([
                'status' => 'completed'
            ]);

            $this->logWorkflow(
                $loan,
                'completed',
                'disbursed',
                'completed'
            );

            LoanCompleted::dispatch($loan);
        }
    }

    /**
     * Log workflow status change
     */
    protected function logWorkflow(
        LoanApplication $loan,
        string $action,
        ?string $from,
        string $to,
        ?string $remarks = null,
        array $metadata = []
    ): void {
        LoanWorkflowLog::create([
            'loan_application_id' => $loan->id,
            'user_id'             => auth()->id(),
            'action'              => $action,
            'from_status'         => $from,
            'to_status'           => $to,
            'remarks'             => $remarks,
            'metadata'            => $metadata,
        ]);
    }
}
