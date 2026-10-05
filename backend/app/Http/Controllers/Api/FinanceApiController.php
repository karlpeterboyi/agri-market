<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\CreditScoreResource;
use App\Http\Resources\Finance\LoanApplicationResource;
use App\Http\Resources\Finance\LoanProductResource;
use App\Http\Resources\Finance\ReadinessResource;
use App\Http\Resources\Finance\RecommendationResource;
use App\Http\Resources\Finance\RepaymentResource;
use App\Models\LoanProduct;
use App\Services\Finance\FarmerReadinessService;
use Illuminate\Http\Request;

class FinanceApiController extends Controller
{
    public function dashboard(
        Request $request,
        FarmerReadinessService $readiness
    )
    {
        $user = $request->user();

        $ready = $readiness->calculate($user);

        return response()->json([

            'credit_score' =>
                new CreditScoreResource(
                    $user->creditScore
                ),

            'readiness' =>
                new ReadinessResource($ready),

            'recommendations' =>
                RecommendationResource::collection(
                    collect(
                        $readiness->recommendations(
                            $user,
                            $ready['breakdown']
                        )
                    )
                ),

        ]);
    }

    public function loanProducts()
    {
        return LoanProductResource::collection(
            LoanProduct::where(
                'active',
                true
            )->get()
        );
    }

    public function applications(
        Request $request
    )
    {
        return LoanApplicationResource::collection(

            $request->user()

                ->loanApplications()

                ->latest()

                ->paginate(20)

        );
    }

    public function repayments(
        Request $request
    )
    {
        return RepaymentResource::collection(

            $request->user()

                ->loanRepayments()

                ->latest()

                ->paginate(20)

        );
    }
}