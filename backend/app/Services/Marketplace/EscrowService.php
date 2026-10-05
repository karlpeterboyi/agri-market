<?php

namespace App\Services\Marketplace;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\Nmb\NmbPayoutService;

/**
 * Unified marketplace money flow:
 * Pay → hold in escrow (seller pending) → buyer confirms delivery →
 * release 90% seller available + 10% platform (MkulimaHub).
 */
class EscrowService
{
    public const PLATFORM_FEE_RATE = 0.10;

    public function platformFee(float $total): float
    {
        return round($total * self::PLATFORM_FEE_RATE, 2);
    }

    public function sellerNet(float $total): float
    {
        return round($total - $this->platformFee($total), 2);
    }

    public function hold(Order $order, Payment $payment): void
    {
        DB::transaction(function () use ($order, $payment) {
            $total = (float) ($payment->amount ?? $order->total_amount);
            $fee = $this->platformFee($total);
            $net = $this->sellerNet($total);

            $order->update([
                'status' => 'in_escrow',
                'platform_fee' => $fee,
                'seller_net' => $net,
            ]);

            $payment->update([
                'status' => 'paid',
                'paid_at' => $payment->paid_at ?? now(),
                'escrow_amount' => $total,
            ]);

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $order->seller_id],
                ['available_balance' => 0, 'pending_balance' => 0]
            );

            $wallet->increment('pending_balance', $net);

            $this->recordTx($wallet->id, 'credit', $net, "Escrow hold for order #{$order->id}", "order:{$order->id}");
        });
    }

    public function release(Order $order, Payment $payment): array
    {
        return DB::transaction(function () use ($order, $payment) {
            $total = (float) ($payment->escrow_amount ?: $payment->amount ?: $order->total_amount);
            $fee = (float) ($order->platform_fee ?? $this->platformFee($total));
            $net = (float) ($order->seller_net ?? $this->sellerNet($total));

            $sellerWallet = Wallet::firstOrCreate(
                ['user_id' => $order->seller_id],
                ['available_balance' => 0, 'pending_balance' => 0]
            );

            $pending = (float) $sellerWallet->pending_balance;
            if ($pending >= $net) {
                $sellerWallet->decrement('pending_balance', $net);
            } elseif ($pending > 0) {
                $sellerWallet->decrement('pending_balance', $pending);
            }

            $sellerWallet->increment('available_balance', $net);
            $this->recordTx($sellerWallet->id, 'credit', $net, "Escrow release 90% order #{$order->id}", "order:{$order->id}:release");

            $admin = User::where('role', 'admin')->orderBy('id')->first();
            if ($admin && $fee > 0) {
                $platformWallet = Wallet::firstOrCreate(
                    ['user_id' => $admin->id],
                    ['available_balance' => 0, 'pending_balance' => 0]
                );
                $platformWallet->increment('available_balance', $fee);
                $this->recordTx($platformWallet->id, 'credit', $fee, "Platform fee 10% order #{$order->id}", "order:{$order->id}:fee");
            }

            $payment->update([
                'status' => 'released',
                'released_at' => now(),
                'escrow_amount' => 0,
            ]);

            $order->update(['status' => 'completed']);

            $nmb = app(NmbPayoutService::class)->releaseEscrowToSeller([
                'amount' => $net,
                'order_id' => $order->id,
                'seller_id' => $order->seller_id,
            ]);

            return [
                'seller_net' => $net,
                'platform_fee' => $fee,
                'seller_available' => $sellerWallet->fresh()->available_balance,
                'nmb_payout' => $nmb,
            ];
        });
    }

    protected function recordTx(int $walletId, string $type, float $amount, string $description, string $reference): void
    {
        if (!class_exists(WalletTransaction::class)) {
            return;
        }
        try {
            WalletTransaction::create([
                'wallet_id' => $walletId,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'reference' => $reference,
            ]);
        } catch (\Throwable $e) {
            // non-fatal if schema differs
        }
    }
}
