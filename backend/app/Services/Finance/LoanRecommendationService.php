<?php

namespace App\Services\Finance;

use App\Models\LoanProduct;
use App\Models\User;

class LoanRecommendationService
{
    public function recommend(User $user)
    {
        $credit = $user->creditScore;

        $query = LoanProduct::query()
            ->with('institution.preference')
            ->where('active', true);

        // Low score → avoid collateral-heavy loans
        if ($credit && $credit->overall_score < 500) {

            $query->where('requires_collateral', false);

        }

        // Farmer profile
        if ($user->farmerProfile) {

            $farmType = strtolower(
                $user->farmerProfile->farm_type ?? ''
            );

            if (str_contains($farmType, 'livestock')) {

                $query->whereIn('loan_type', [
                    'livestock',
                    'working_capital',
                ]);

            } else {

                $query->whereIn('loan_type', [
                    'crop',
                    'working_capital',
                ]);
            }
        }

        return $query

            ->get()

            ->map(function ($loan) use ($user) {

                return new LoanRecommendation(

                    loan: $loan,

                    approvalProbability: $this->approvalProbability($user, $loan),

                    compatibilityScore: $this->compatibilityScore($user, $loan),

                    reasons: $this->recommendationReasons($user, $loan),

                    improvements: $this->improvements($user, $loan),

                );

            });
    }

    protected function compatibilityScore(
        User $user,
        LoanProduct $loan
    ): int
    {
        $score = 50;

        $prefs = $loan->institution->preference;

        if (!$prefs) {
            return $score;
        }

        if (
            $prefs->women_programme &&
            $user->gender === 'female'
        ) {
            $score += 15;
        }

        if (
            $prefs->youth_programme &&
            $user->age <= 35
        ) {
            $score += 15;
        }

        if ($loan->featured) {
            $score += 10;
        }

        return min(100, $score);
    }

    protected function approvalProbability(
        User $user,
        LoanProduct $loan
    ): int
    {
        $score = 40;

        if ($user->creditScore) {

            $score += intval(
                $user->creditScore->overall_score / 20
            );

        }

        if ($loan->featured) {

            $score += 5;

        }

        if (!$loan->requires_collateral) {

            $score += 10;

        }

        if ($user->farmerProfile) {

            $score += 10;

        }

        return min(98, max(5, $score));
    }

    protected function recommendationReasons(
        User $user,
        LoanProduct $loan
    ): array
    {
        $reasons = [];

        if ($loan->featured) {

            $reasons[] =
                "Featured agricultural financing programme.";

        }

        if (!$loan->requires_collateral) {

            $reasons[] =
                "No collateral required.";

        }

        if ($loan->online_application) {

            $reasons[] =
                "Supports online application.";

        }

        if ($user->creditScore?->overall_score >= 700) {

            $reasons[] =
                "Strong agricultural credit profile.";

        }

        if ($user->loanApplications()->count() == 0) {

            $reasons[] =
                "Suitable for first-time applicants.";

        }

        return $reasons;
    }

    protected function improvements(
        User $user,
        LoanProduct $loan
    ): array
    {
        $items = [];

        if (!$user->farmerProfile) {

            $items[] =
                "Complete your farmer profile.";

        }

        if (!$user->creditScore ||
            $user->creditScore->overall_score < 700) {

            $items[] =
                "Improve your credit score by completing more successful marketplace transactions.";

        }

        if (!$user->subscriptions()
                ->where('status', 'active')
                ->exists()) {

            $items[] =
                "Maintain an active subscription.";

        }

        if ($loan->requires_collateral) {

            $items[] =
                "Prepare acceptable collateral documentation.";

        }

        return $items;
    }
}
