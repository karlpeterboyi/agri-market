<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'listing_limit',
        'featured_listings',
        'analytics',
        'verified_badge',
        'priority_support',
        'business_page',
        'active',
    ];

    protected $casts = [
        'analytics' => 'boolean',
        'verified_badge' => 'boolean',
        'priority_support' => 'boolean',
        'business_page' => 'boolean',
        'active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
    
    public function users()
{
    return $this->hasMany(UserSubscription::class);
}

public function prices()
{
    return $this->hasMany(SubscriptionPrice::class);
}

public function features()
{
    return $this->hasMany(PlanFeature::class);
}

/**
 * Get a feature value by key.
 */
public function feature(string $key, $default = null)
{
    $feature = $this->features
        ->firstWhere('feature_key', $key);

    return $feature?->feature_value ?? $default;
}

/**
 * Check whether a feature exists.
 */
public function hasFeature(string $key): bool
{
    return $this->features()
        ->where('feature_key', $key)
        ->exists();
}

}