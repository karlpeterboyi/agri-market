<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserBankLink;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user finance dossier — credibility pack for FI / government / partners.
 * Combines wallet, marketplace payments, loans, NMB links and (mock/live) bank trails.
 */
class FinanceDossierController extends Controller
{
    public function mine(Request $request)
    {
        return response()->json($this->build($request->user()));
    }

    public function show(Request $request, User $user)
    {
        $actor = $request->user();
        $role = $actor->role ?? '';
        if ($actor->id !== $user->id && !in_array($role, ['admin', 'financier', 'financial_institution'], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($this->build($user));
    }

    protected function build(User $user): array
    {
        $wallet = class_exists(Wallet::class)
            ? Wallet::firstOrCreate(['user_id' => $user->id], ['available_balance' => 0, 'pending_balance' => 0])
            : null;

        $bankLinks = [];
        if (class_exists(UserBankLink::class) && Schema::hasTable('user_bank_links')) {
            $bankLinks = UserBankLink::where('user_id', $user->id)->get();
        }

        $ordersAsBuyer = Schema::hasTable('orders')
            ? Order::where('buyer_id', $user->id)->count()
            : 0;
        $ordersAsSeller = Schema::hasTable('orders')
            ? Order::where('seller_id', $user->id)->count()
            : 0;

        $paymentsPaid = Schema::hasTable('payments')
            ? (float) Payment::whereHas('order', fn ($q) => $q->where('buyer_id', $user->id))
                ->whereIn('status', ['paid', 'released'])
                ->sum('amount')
            : 0;

        $paymentsReceived = Schema::hasTable('payments')
            ? (float) Payment::whereHas('order', fn ($q) => $q->where('seller_id', $user->id))
                ->where('status', 'released')
                ->sum('amount')
            : 0;

        $loans = [];
        if (Schema::hasTable('loan_applications')) {
            $loans = LoanApplication::with('product:id,name,interest_rate')
                ->where('user_id', $user->id)
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($l) => [
                    'id' => $l->id,
                    'application_number' => $l->application_number,
                    'status' => $l->status,
                    'requested_amount' => $l->requested_amount,
                    'product' => $l->product?->name,
                    'submitted_at' => $l->submitted_at,
                ]);
        }

        $walletTx = [];
        if ($wallet && class_exists(WalletTransaction::class) && Schema::hasTable('wallet_transactions')) {
            $walletTx = WalletTransaction::where('wallet_id', $wallet->id)
                ->latest()
                ->limit(30)
                ->get();
        }

        $withdrawals = [];
        if (Schema::hasTable('withdrawals')) {
            $withdrawals = Withdrawal::where('user_id', $user->id)->latest()->limit(10)->get();
        }

        $credibility = $this->score([
            'bank_linked' => count($bankLinks) > 0,
            'bank_verified' => collect($bankLinks)->contains(fn ($l) => $l->verified),
            'orders_buyer' => $ordersAsBuyer,
            'orders_seller' => $ordersAsSeller,
            'volume_paid' => $paymentsPaid,
            'volume_received' => $paymentsReceived,
            'loans' => is_countable($loans) ? count($loans) : 0,
        ]);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => $user->status,
                'member_since' => $user->created_at,
            ],
            'wallet' => $wallet ? [
                'available_balance' => (float) $wallet->available_balance,
                'pending_balance' => (float) $wallet->pending_balance,
            ] : null,
            'nmb_links' => $bankLinks,
            'marketplace' => [
                'orders_as_buyer' => $ordersAsBuyer,
                'orders_as_seller' => $ordersAsSeller,
                'total_paid_tzs' => $paymentsPaid,
                'total_received_tzs' => $paymentsReceived,
            ],
            'loans' => $loans,
            'wallet_transactions' => $walletTx,
            'withdrawals' => $withdrawals,
            'credibility' => $credibility,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function score(array $m): array
    {
        $score = 20;
        if ($m['bank_linked']) {
            $score += 15;
        }
        if ($m['bank_verified']) {
            $score += 20;
        }
        $score += min(15, $m['orders_buyer'] * 2);
        $score += min(15, $m['orders_seller'] * 2);
        if ($m['volume_paid'] + $m['volume_received'] > 100000) {
            $score += 10;
        }
        if ($m['volume_paid'] + $m['volume_received'] > 1000000) {
            $score += 5;
        }
        $score = min(100, $score);

        return [
            'score' => $score,
            'band' => $score >= 75 ? 'strong' : ($score >= 50 ? 'moderate' : 'building'),
            'signals' => $m,
            'note' => 'NMB-linked settlement trails increase credibility for lenders and government reporting.',
        ];
    }
}
