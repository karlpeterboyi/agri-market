<?php

namespace App\Services\Finance;

use App\Models\FinancialInstitution;
use App\Models\LoanApplication;
use App\Models\LoanDisbursement;
use App\Models\LoanRepayment;

class FinanceReportService
{
    public function dashboard(): array
    {
        return [
            'applications' =>
                LoanApplication::count(),

            'approved' =>
                LoanApplication::where(
                    'status',
                    'approved'
                )->count(),

            'rejected' =>
                LoanApplication::where(
                    'status',
                    'rejected'
                )->count(),

            'disbursed' =>
                LoanApplication::where(
                    'status',
                    'disbursed'
                )->count(),

            'completed' =>
                LoanApplication::where(
                    'status',
                    'completed'
                )->count(),

            'institutions' =>
                FinancialInstitution::count(),

            'total_disbursed' =>
                LoanDisbursement::sum(
                    'disbursed_amount'
                ),

            'total_outstanding' =>
                LoanRepayment::where(
                    'status',
                    '!=',
                    'paid'
                )->sum(
                    'balance'
                ),
        ];
    }

    public function repaymentPerformance(): array
    {
        return [
            'paid' =>
                LoanRepayment::where(
                    'status',
                    'paid'
                )->count(),

            'pending' =>
                LoanRepayment::where(
                    'status',
                    'pending'
                )->count(),

            'overdue' =>
                LoanRepayment::where(
                    'status',
                    'overdue'
                )->count(),

            'amount_paid' =>
                LoanRepayment::where(
                    'status',
                    'paid'
                )->sum(
                    'paid_amount'
                ),

            'outstanding_balance' =>
                LoanRepayment::where(
                    'status',
                    '!=',
                    'paid'
                )->sum(
                    'balance'
                ),
        ];
    }

    public function institutionPortfolio(): array
    {
        return FinancialInstitution::withCount([
            'loanApplications',
            'loanProducts',
        ])
        ->withSum(
            'loanDisbursements',
            'disbursed_amount'
        )
        ->get()
        ->toArray();
    }

    public function cropPortfolio()
    {
        return LoanApplication::selectRaw(
            'crop_type,
             COUNT(*) as total,
             SUM(requested_amount) as amount'
        )
        ->groupBy('crop_type')
        ->orderByDesc('amount')
        ->get();
    }

    public function livestockPortfolio()
    {
        return LoanApplication::selectRaw(
            'livestock_type,
             COUNT(*) as total,
             SUM(requested_amount) as amount'
        )
        ->groupBy('livestock_type')
        ->get();
    }

    public function regionalPortfolio()
    {
        return LoanApplication::selectRaw(
            'region,
             COUNT(*) as loans,
             SUM(requested_amount) as amount'
        )
        ->groupBy('region')
        ->get();
    }

    public function genderAnalysis()
    {
        return LoanApplication::join(
            'users',
            'users.id',
            '=',
            'loan_applications.user_id'
        )
        ->selectRaw(
            'gender,
             COUNT(*) as borrowers,
             SUM(requested_amount) as amount'
        )
        ->groupBy('gender')
        ->get();
    }

    public function youthPortfolio()
    {
        return LoanApplication::join(
            'users',
            'users.id',
            '=',
            'loan_applications.user_id'
        )
        ->whereRaw(
            "DATE_PART('year', AGE(users.date_of_birth)) <= 35"
        )
        ->selectRaw(
            'COUNT(*) as youth_loans,
             SUM(requested_amount) as amount'
        )
        ->first();
    }
}
