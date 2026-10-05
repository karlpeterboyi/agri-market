<?php

namespace App\Services\Credit;

use App\Models\User;
use App\Models\CreditScore;
use App\Models\Order;
use App\Models\LoanRepayment;
use App\Models\Rating;
use App\Models\UserSubscription;

class CreditScoreService
{
    public function calculate(User $user): CreditScore
    {
        $marketplace = $this->marketplaceScore($user);

        $repayment = $this->repaymentScore($user);

        $profile = $this->profileScore($user);

        $reputation = $this->reputationScore($user);

        $subscription = $this->subscriptionScore($user);

        $activity = $this->activityScore($user);

        $overall = min(
            1000,

            $marketplace +
            $repayment +
            $profile +
            $reputation +
            $subscription +
            $activity
        );

        $risk = $this->riskLevel($overall);

        return CreditScore::updateOrCreate(

            [
                'user_id' => $user->id
            ],

            [

                'overall_score' => $overall,

                'marketplace_score' => $marketplace,

                'repayment_score' => $repayment,

                'profile_score' => $profile,

                'reputation_score' => $reputation,

                'subscription_score' => $subscription,

                'activity_score' => $activity,

                'risk_level' => $risk,

                'calculated_at' => now(),

            ]
        );
    }

    protected function marketplaceScore(User $user): int
    {
        $completed = Order::where(function ($q) use ($user) {

            $q->where('buyer_id', $user->id)

              ->orWhere('seller_id', $user->id);

        })

        ->where('status', 'completed')

        ->count();

        return min(250, $completed * 5);
    }

    protected function repaymentScore(User $user): int
    {
        $paid = LoanRepayment::whereHas(

            'application',

            fn($q) => $q->where('user_id', $user->id)

        )

        ->where('status', 'paid')

        ->count();

        return min(250, $paid * 10);
    }

    protected function profileScore(User $user): int
    {
        $score = 0;

        if ($user->email_verified_at) $score += 30;

        if (!empty($user->phone)) $score += 20;

        if ($user->farmerProfile) $score += 50;

        return min(100, $score);
    }

    protected function reputationScore(User $user): int
    {
        $average = Rating::where(

            'rated_user_id',

            $user->id

        )->avg('rating');

        if (!$average) {
            return 0;
        }

        return (int) round($average * 30);
    }

    protected function subscriptionScore(User $user): int
    {
        $active = UserSubscription::where(

            'user_id',

            $user->id

        )

        ->where('status', 'active')

        ->exists();

        return $active ? 100 : 0;
    }

    protected function activityScore(User $user): int
    {
        return min(

            150,

            now()->diffInDays($user->created_at) / 2

        );
    }

    protected function riskLevel(int $score): string
    {
        return match (true) {

            $score >= 850 => 'Excellent',

            $score >= 700 => 'Very Low',

            $score >= 600 => 'Low',

            $score >= 500 => 'Moderate',

            $score >= 350 => 'High',

            default => 'Very High',

        };
    }
}