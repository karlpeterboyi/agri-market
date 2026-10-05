<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPrice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subscription_plan_id',
        'billing_period',
        'price',
        'currency',
        'active',
    ];

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Billing period constants.
     */
    public const MONTHLY = 'monthly';
    public const QUARTERLY = 'quarterly';
    public const BIANNUAL = 'biannual';
    public const ANNUAL = 'annual';

    /**
     * Get the subscription plan.
     */
    public function plan()
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'subscription_plan_id'
        );
    }

    /**
     * Get all subscriptions using this pricing option.
     */
    public function subscriptions()
    {
        return $this->hasMany(
            UserSubscription::class,
            'subscription_price_id'
        );
    }

    /**
     * Scope active pricing.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Human-readable billing period.
     */
    public function getBillingPeriodLabelAttribute(): string
    {
        return match ($this->billing_period) {
            self::MONTHLY => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::BIANNUAL => 'Biannual',
            self::ANNUAL => 'Annual',
            default => ucfirst($this->billing_period),
        };
    }

    /**
     * Formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }
}