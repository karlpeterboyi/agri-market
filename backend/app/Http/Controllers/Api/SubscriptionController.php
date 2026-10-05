<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $fallback = $this->fallbackPlans();

        if (!Schema::hasTable('subscription_plans')) {
            return response()->json(['data' => $fallback]);
        }

        $dbPlans = SubscriptionPlan::with('prices')->where('active', true)->get();
        if ($dbPlans->isEmpty()) {
            return response()->json(['data' => $fallback]);
        }

        $normalized = $dbPlans->map(function ($plan) use ($fallback) {
            $priceRow = $plan->prices
                ? $plan->prices->firstWhere('active', true) ?? $plan->prices->first()
                : null;
            $price = $priceRow
                ? (float) ($priceRow->price ?? $priceRow->amount ?? 0)
                : 0;

            $code = $plan->slug ?: Str::slug($plan->name);
            $fb = collect($fallback)->first(function ($f) use ($code, $plan) {
                return $f['code'] === $code
                    || str_contains(Str::slug($f['name']), Str::slug($plan->name))
                    || str_contains(Str::slug($plan->name), Str::slug($f['name']));
            });

            if ($price <= 0 && $fb) {
                $price = (float) $fb['price_tzs'];
            }

            $period = $priceRow->billing_period ?? ($fb['period'] ?? 'month');
            if ($period === 'monthly') {
                $period = 'month';
            }

            return [
                'id' => $plan->id,
                'code' => $code,
                'name' => $plan->name,
                'role' => $fb['role'] ?? 'commercial',
                'price_tzs' => $price,
                'period' => $period,
                'features' => $fb['features'] ?? array_values(array_filter([
                    $plan->description,
                    $plan->listing_limit ? "Up to {$plan->listing_limit} listings" : null,
                    $plan->analytics ? 'Analytics' : null,
                    $plan->verified_badge ? 'Verified badge' : null,
                ])),
                'description' => $plan->description,
            ];
        })->values()->all();

        // If every plan still shows 0, prefer full fallback catalogue
        $anyPrice = collect($normalized)->contains(fn ($p) => ($p['price_tzs'] ?? 0) > 0);
        if (!$anyPrice) {
            return response()->json(['data' => $fallback]);
        }

        return response()->json(['data' => $normalized]);
    }

    public function mine(Request $request)
    {
        if (!Schema::hasTable('user_subscriptions')) {
            return response()->json(['active' => false, 'subscriptions' => []]);
        }

        $subs = UserSubscription::where('user_id', $request->user()->id)->latest()->get();
        $active = $subs->first(function ($s) {
            return in_array(strtolower((string) $s->status), ['active'], true)
                && (empty($s->expires_at) || $s->expires_at > now());
        });

        return response()->json([
            'active' => (bool) $active,
            'current' => $active,
            'subscriptions' => $subs,
            'farmer_exempt' => ($request->user()->role ?? '') === 'farmer',
        ]);
    }

    /** Dev/sandbox activate — wire Pesapal later */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'plan_code' => 'nullable|string',
            'code' => 'nullable|string',
            'plan_id' => 'nullable|integer',
            'months' => 'nullable|integer|min:1|max:24',
        ]);

        $planCode = $data['plan_code'] ?? $data['code'] ?? null;
        if (!$planCode && !empty($data['plan_id']) && Schema::hasTable('subscription_plans')) {
            $plan = SubscriptionPlan::find($data['plan_id']);
            $planCode = $plan?->slug ?: ($plan ? Str::slug($plan->name) : null);
        }

        if (!$planCode) {
            return response()->json([
                'message' => 'The plan code field is required.',
                'errors' => ['plan_code' => ['Select a plan and try again.']],
            ], 422);
        }

        $role = $request->user()->role;
        if ($role === 'farmer') {
            return response()->json(['message' => 'Farmers do not need a subscription to list produce.'], 200);
        }

        $months = $data['months'] ?? 1;
        $planMeta = collect($this->fallbackPlans())->firstWhere('code', $planCode);
        $price = $planMeta['price_tzs'] ?? 25000;

        if (!Schema::hasTable('user_subscriptions')) {
            return response()->json(['message' => 'Subscriptions table missing — run migrations'], 500);
        }

        $sub = UserSubscription::create([
            'user_id' => $request->user()->id,
            'subscription_price_id' => null,
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addMonths($months)->toDateString(),
            'auto_renew' => false,
            'payment_reference' => 'sandbox-'.uniqid(),
            'meta' => [
                'plan_code' => $planCode,
                'price_tzs' => $price,
                'activated_via' => 'sandbox',
            ],
        ]);

        return response()->json([
            'message' => 'Subscription activated (sandbox). Pay via Pesapal in production.',
            'subscription' => $sub,
        ], 201);
    }

    public function current(Request $request)
    {
        return $this->mine($request);
    }

    public function cancel(Request $request)
    {
        if (!Schema::hasTable('user_subscriptions')) {
            return response()->json(['message' => 'No subscriptions'], 404);
        }

        UserSubscription::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Subscription cancelled']);
    }

    protected function fallbackPlans(): array
    {
        return [
            ['code' => 'buyer_basic', 'name' => 'Buyer Basic', 'role' => 'buyer', 'price_tzs' => 15000, 'period' => 'month', 'features' => ['Browse & buy', 'Escrow orders']],
            ['code' => 'agrodealer_pro', 'name' => 'Agrodealer Pro', 'role' => 'agrodealer', 'price_tzs' => 35000, 'period' => 'month', 'features' => ['List farm inputs', 'Analytics']],
            ['code' => 'provider_pro', 'name' => 'Service Provider', 'role' => 'provider', 'price_tzs' => 30000, 'period' => 'month', 'features' => ['List services', 'Bookings']],
            ['code' => 'transporter_pro', 'name' => 'Transporter Pro', 'role' => 'transporter', 'price_tzs' => 25000, 'period' => 'month', 'features' => ['List trucks', 'Job quotes']],
            ['code' => 'processor_pro', 'name' => 'Processor / Aggregator', 'role' => 'processor', 'price_tzs' => 40000, 'period' => 'month', 'features' => ['Bulk buy', 'Sell processed']],
            ['code' => 'financier_pro', 'name' => 'Financier', 'role' => 'financier', 'price_tzs' => 50000, 'period' => 'month', 'features' => ['Loan products', 'Inbox']],
            ['code' => 'educator_basic', 'name' => 'Educator', 'role' => 'educator', 'price_tzs' => 20000, 'period' => 'month', 'features' => ['Publish courses', 'Knowledge hub']],
        ];
    }
}
