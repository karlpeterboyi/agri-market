<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_price_id',
        'starts_at',
        'expires_at',
        'status',
        'auto_renew',
        'payment_reference',
        'meta',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'expires_at' => 'date',
        'auto_renew' => 'boolean',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPrice::class, 'subscription_price_id');
    }

    /** Convenience: plan via price (may be null if sandbox sub has no price row). */
    public function plan()
    {
        return $this->price?->plan();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_subscription_id');
    }

    public function hasFeature(string $feature): bool
    {
        if (!$this->price) {
            return false;
        }

        $plan = $this->price->plan;
        if (!$plan || !method_exists($plan, 'features')) {
            return false;
        }

        return $plan->features->contains('feature_key', $feature);
    }

    public function featureLimit(string $key): ?int
    {
        if (!$this->price) {
            return 0;
        }

        $plan = $this->price->plan;
        if (!$plan) {
            return 0;
        }

        $feature = $plan->features->firstWhere('feature_key', $key);

        return $feature?->feature_limit ?? 0;
    }
}
