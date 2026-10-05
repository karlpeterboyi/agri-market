<?php

namespace App\Services\Finance;

use App\Models\User;

class FarmerReadinessService
{
    public function calculate(User $user): array
    {
        $breakdown = [

            'identity' => $this->identity($user),

            'profile' => $this->profile($user),

            'marketplace' => $this->marketplace($user),

            'credit' => $this->credit($user),

            'documents' => $this->documents($user),

            'collateral' => $this->collateral($user),

            'subscription' => $this->subscription($user),

        ];

        $overall = round(
            array_sum($breakdown) /
            count($breakdown)
        );

        return [

            'overall' => $overall,

            'breakdown' => $breakdown,

            'risk' => $this->risk($overall),

            'recommendations' => $this->recommendations($user, $breakdown),

        ];
    }

    public function recommendations(
        User $user,
        array $breakdown
    ): array
    {
        $items = [];

        if ($breakdown['identity'] < 100) {

            $items[] = [

                'priority' => 1,

                'title' => 'Verify your account',

                'impact' => '+20 Readiness',

            ];
        }

        if ($breakdown['profile'] < 100) {

            $items[] = [

                'priority' => 2,

                'title' => 'Complete your farmer profile',

                'impact' => '+20 Readiness',

            ];
        }

        if ($breakdown['collateral'] == 0) {

            $items[] = [

                'priority' => 3,

                'title' => 'Register collateral',

                'impact' => '+15 Readiness',

            ];
        }

        if ($breakdown['documents'] == 0) {

            $items[] = [

                'priority' => 4,

                'title' => 'Upload supporting documents',

                'impact' => '+15 Readiness',

            ];
        }

        if ($breakdown['marketplace'] < 60) {

            $items[] = [

                'priority' => 5,

                'title' => 'Increase marketplace activity',

                'impact' => '+20 Readiness',

            ];
        }

        return collect($items)

            ->sortBy('priority')

            ->values()

            ->all();
    }

    protected function identity(User $user): int
    {
        $score = 0;

        if ($user->email_verified_at)
            $score += 50;

        if (!empty($user->phone))
            $score += 50;

        return $score;
    }

    protected function profile(User $user): int
    {
        if (!$user->farmerProfile)
            return 0;

        $score = 40;

        if ($user->farmerProfile->farm_size)
            $score += 20;

        if ($user->farmerProfile->region)
            $score += 20;

        if ($user->farmerProfile->district)
            $score += 20;

        return min(100, $score);
    }

    protected function marketplace(User $user): int
    {
        $orders = $user->orders()->count();

        return min(100, $orders * 5);
    }

    protected function credit(User $user): int
    {
        if (!$user->creditScore)
            return 0;

        return min(
            100,
            intval(
                $user->creditScore->overall_score / 10
            )
        );
    }

    protected function documents(User $user): int
    {
        return $user->loanApplications()

            ->withCount('documents')

            ->get()

            ->sum('documents_count') > 0 ? 100 : 0;
    }

    protected function collateral(User $user): int
    {
        return $user->loanApplications()

            ->withCount('collaterals')

            ->get()

            ->sum('collaterals_count') > 0 ? 100 : 0;
    }

    protected function subscription(User $user): int
    {
        return $user->subscriptions()

            ->where('status', 'active')

            ->exists()

            ? 100 : 0;
    }

    protected function risk(int $score): string
    {
        return match (true) {

            $score >= 90 => 'Excellent',

            $score >= 75 => 'Good',

            $score >= 60 => 'Fair',

            $score >= 40 => 'Needs Improvement',

            default => 'Poor',

        };
    }
}
