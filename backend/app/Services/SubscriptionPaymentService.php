<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SubscriptionPrice;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\PesapalService;

class SubscriptionPaymentService
{
    /**
     * Create a subscription and its payment record.
     */
    public function createSubscription(int $subscriptionPriceId): array
    {
        $price = SubscriptionPrice::with('plan')
            ->findOrFail($subscriptionPriceId);

        // Cancel any pending subscription for this user
        UserSubscription::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'auto_renew' => false,
            ]);

        $subscription = UserSubscription::create([
            'user_id' => Auth::id(),
            'subscription_price_id' => $price->id,
            'starts_at' => now(),
            'expires_at' => $this->expiryDate($price->billing_period),
            'status' => 'pending',
            'auto_renew' => true,
        ]);

        $payment = Payment::create([
            'user_subscription_id' => $subscription->id,
            'payment_type' => 'subscription',
            'amount' => $price->price,
            'currency' => $price->currency,
            'status' => 'pending',
            'reference' => 'SUB-'.Str::upper(Str::random(12)),
        ]);

        $user = Auth::user();
        $names = explode(' ', $user->name, 2);

        $response = app(PesapalService::class)->checkoutPayment(
            $payment,
            "Subscription - {$price->plan->name}",
            $user->email,
            $user->phone,
            $names[0],
            $names[1] ?? ''
        );

        return [
            'subscription' => $subscription,
            'payment' => $payment->fresh(),
            'redirect_url' => $response['redirect_url'] ?? null,
        ];
    }

    /**
     * Activate subscription after successful payment.
     */
    public function activate(Payment $payment): UserSubscription
    {
        $subscription = $payment->subscription;

        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => $this->expiryDate(
                $subscription->price->billing_period
            ),
        ]);

        return $subscription->fresh();
    }

    /**
     * Calculate expiry date.
     */
    protected function expiryDate(string $period)
    {
        return match ($period) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'biannual' => now()->addMonths(6),
            'annual' => now()->addYear(),
            default => now()->addYear(),
        };
    }
}
