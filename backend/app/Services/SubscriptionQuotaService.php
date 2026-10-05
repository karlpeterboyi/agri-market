<?php

namespace App\Services;

use App\Models\User;
use App\Models\ProductListing;
use App\Models\LivestockListing;
use App\Models\MachineryListing;
use App\Models\Service;

class SubscriptionQuotaService
{
    /**
     * Get the user's active subscription.
     */
    protected function subscription(User $user)
    {
        return $user->subscriptions()
            ->with('price.plan.features')
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Determine whether the user can create a crop listing.
     */
    public function canCreateCropListing(User $user): bool
    {
        return $this->withinLimit(
            $user,
            'crop_listings',
            ProductListing::where('seller_id', $user->id)->count()
        );
    }

    /**
     * Determine whether the user can create a livestock listing.
     */
    public function canCreateLivestockListing(User $user): bool
    {
        return $this->withinLimit(
            $user,
            'livestock_listings',
            LivestockListing::where('seller_id', $user->id)->count()
        );
    }

    /**
     * Determine whether the user can create a machinery listing.
     */
    public function canCreateMachineryListing(User $user): bool
    {
        return $this->withinLimit(
            $user,
            'machinery_listings',
            MachineryListing::where('owner_id', $user->id)->count()
        );
    }

    /**
     * Determine whether the user can create a service listing.
     */
    public function canCreateService(User $user): bool
    {
        return $this->withinLimit(
            $user,
            'service_listings',
            Service::where('provider_id', $user->id)->count()
        );
    }

    /**
     * Determine whether the user can create a featured listing.
     */
    public function canCreateFeaturedListing(User $user): bool
    {
        $count =
            ProductListing::where('seller_id', $user->id)
                ->where('featured', true)
                ->count()

            +

            LivestockListing::where('seller_id', $user->id)
                ->where('featured', true)
                ->count()

            +

            MachineryListing::where('owner_id', $user->id)
                ->where('featured', true)
                ->count()

            +

            Service::where('provider_id', $user->id)
                ->where('featured', true)
                ->count();

        return $this->withinLimit(
            $user,
            'featured_listings',
            $count
        );
    }

    /**
     * Shared quota checker.
     */
    protected function withinLimit(
        User $user,
        string $feature,
        int $currentCount
    ): bool {

        $subscription = $this->subscription($user);

        if (!$subscription) {
            return false;
        }

        $limit = $subscription->featureLimit($feature);

        // NULL means unlimited.
        if (is_null($limit)) {
            return true;
        }

        return $currentCount < $limit;
    }

    /**
     * Remaining quota.
     */
    public function remaining(
        User $user,
        string $feature,
        int $currentCount
    ): ?int {

        $subscription = $this->subscription($user);

        if (!$subscription) {
            return 0;
        }

        $limit = $subscription->featureLimit($feature);

        if (is_null($limit)) {
            return null;
        }

        return max(0, $limit - $currentCount);
    }
}