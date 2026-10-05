<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPrice;
use App\Models\User;
use App\Models\UserSubscription;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminSubscriptionController extends Controller
{
    protected function assertAdmin(): void
    {
        RoleAccess::assertAdmin(auth()->user());
    }

    // ----- Plans -----

    public function plansIndex()
    {
        $this->assertAdmin();
        if (!Schema::hasTable('subscription_plans')) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => SubscriptionPlan::with('prices')->orderBy('name')->get(),
        ]);
    }

    public function plansStore(Request $request)
    {
        $this->assertAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'listing_limit' => 'nullable|integer|min:0',
            'featured_listings' => 'nullable|integer|min:0',
            'analytics' => 'nullable|boolean',
            'verified_badge' => 'nullable|boolean',
            'priority_support' => 'nullable|boolean',
            'business_page' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'price_tzs' => 'nullable|numeric|min:0',
            'billing_period' => 'nullable|string|max:50',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);
        $plan = SubscriptionPlan::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'listing_limit' => $data['listing_limit'] ?? 0,
            'featured_listings' => $data['featured_listings'] ?? 0,
            'analytics' => $data['analytics'] ?? false,
            'verified_badge' => $data['verified_badge'] ?? false,
            'priority_support' => $data['priority_support'] ?? false,
            'business_page' => $data['business_page'] ?? false,
            'active' => $data['active'] ?? true,
        ]);

        if (Schema::hasTable('subscription_prices') && isset($data['price_tzs'])) {
            try {
                SubscriptionPrice::create([
                    'subscription_plan_id' => $plan->id,
                    'amount' => $data['price_tzs'],
                    'currency' => 'TZS',
                    'billing_period' => $data['billing_period'] ?? 'month',
                    'active' => true,
                ]);
            } catch (\Throwable $e) {
                // price schema may vary
            }
        }

        return response()->json(['message' => 'Plan created', 'plan' => $plan->load('prices')], 201);
    }

    public function plansUpdate(Request $request, SubscriptionPlan $plan)
    {
        $this->assertAdmin();
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'listing_limit' => 'nullable|integer|min:0',
            'featured_listings' => 'nullable|integer|min:0',
            'analytics' => 'nullable|boolean',
            'verified_badge' => 'nullable|boolean',
            'priority_support' => 'nullable|boolean',
            'business_page' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);
        $plan->update($data);

        return response()->json(['message' => 'Plan updated', 'plan' => $plan->fresh('prices')]);
    }

    public function plansDestroy(SubscriptionPlan $plan)
    {
        $this->assertAdmin();
        $plan->delete();

        return response()->json(['message' => 'Plan deleted']);
    }

    // ----- User subscriptions -----

    public function userIndex(Request $request)
    {
        $this->assertAdmin();
        $q = UserSubscription::with(['user:id,name,email,role,phone'])->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $q->where('user_id', $request->user_id);
        }

        return response()->json(['data' => $q->paginate($request->integer('per_page', 30))]);
    }

    public function userStore(Request $request)
    {
        $this->assertAdmin();
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'nullable|in:active,expired,cancelled,pending',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'months' => 'nullable|integer|min:1|max:36',
            'subscription_price_id' => 'nullable|integer',
            'payment_reference' => 'nullable|string|max:100',
            'plan_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $user = User::findOrFail($data['user_id']);
        if (($user->role ?? '') === 'farmer') {
            return response()->json(['message' => 'Farmers are exempt from subscriptions.'], 422);
        }

        $starts = $data['starts_at'] ?? now()->toDateString();
        $expires = $data['expires_at'] ?? now()->addMonths($data['months'] ?? 1)->toDateString();

        $sub = UserSubscription::create([
            'user_id' => $data['user_id'],
            'subscription_price_id' => $data['subscription_price_id'] ?? null,
            'status' => $data['status'] ?? 'active',
            'starts_at' => $starts,
            'expires_at' => $expires,
            'auto_renew' => false,
            'payment_reference' => $data['payment_reference'] ?? ('admin-'.uniqid()),
            'meta' => [
                'plan_code' => $data['plan_code'] ?? null,
                'notes' => $data['notes'] ?? null,
                'activated_by_admin' => auth()->id(),
            ],
        ]);

        return response()->json(['message' => 'Subscription assigned', 'subscription' => $sub->load('user')], 201);
    }

    public function userUpdate(Request $request, UserSubscription $userSubscription)
    {
        $this->assertAdmin();
        $data = $request->validate([
            'status' => 'sometimes|in:active,expired,cancelled,pending',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'auto_renew' => 'nullable|boolean',
            'payment_reference' => 'nullable|string|max:100',
        ]);
        $userSubscription->update($data);

        return response()->json([
            'message' => 'Subscription updated',
            'subscription' => $userSubscription->fresh('user'),
        ]);
    }

    public function userDestroy(UserSubscription $userSubscription)
    {
        $this->assertAdmin();
        $userSubscription->delete();

        return response()->json(['message' => 'Subscription deleted']);
    }
}
