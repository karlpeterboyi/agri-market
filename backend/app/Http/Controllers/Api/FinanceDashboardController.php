<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Finance\FarmerReadinessService;
use Illuminate\Http\Request;

class FinanceDashboardController extends Controller
{
    public function index(
        Request $request,
        FarmerReadinessService $readiness
    ) {

        $user = $request->user();

        $ready = $readiness->calculate($user);

        return response()->json([

            'credit_score' =>
                $user->creditScore,

            'readiness' =>
                $ready,

            'recommendations' =>
                $readiness->recommendations(
                    $user,
                    $ready['breakdown']
                ),

            'active_loans' =>
                $user->loanApplications()
                    ->whereIn('status', [
                        'approved',
                        'disbursed'
                    ])
                    ->count(),

            'loan_products_available' =>
                \App\Models\LoanProduct::where('active', true)
                    ->count(),

            'pending_repayments' =>
                \App\Models\LoanRepayment::whereHas(
                    'application',
                    fn($q) => $q->where('user_id', $user->id)
                )
                ->where('status', 'pending')
                ->count(),

            'next_due_payment' =>
                \App\Models\LoanRepayment::whereHas(
                    'application',
                    fn($q) => $q->where('user_id', $user->id)
                )
                ->where('status', 'pending')
                ->orderBy('due_date')
                ->first(),

        ]);
    }
}