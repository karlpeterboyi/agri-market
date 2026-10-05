<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public static function releaseEscrow(Payment $payment): array
    {
        return DB::transaction(function () use ($payment) {

            $order = Order::findOrFail(
                $payment->order_id
            );

            $sellerWallet = Wallet::firstOrCreate(
                ['user_id' => $order->seller_id],
                [
                    'available_balance' => 0,
                    'pending_balance' => 0
                ]
            );

            $grossAmount = $payment->amount;

            $commissionRate = 0.05;

            $commission = round(
                $grossAmount * $commissionRate,
                2
            );

            $sellerAmount = $grossAmount - $commission;

            $sellerWallet->increment(
                'available_balance',
                $sellerAmount
            );

            WalletTransaction::create([
                'wallet_id' => $sellerWallet->id,
                'type' => 'credit',
                'amount' => $sellerAmount,
                'reference' => $payment->transaction_ref,
                'description' =>
                    'Escrow released for Order #' .
                    $order->id
            ]);

            $payment->update([
                'status' => 'released',
                'released_at' => now(),
                'escrow_amount' => 0
            ]);

            $order->update([
                'status' => 'completed'
            ]);

            return [
                'payment' => $payment->fresh(),
                'order' => $order->fresh(),
                'commission' => $commission,
                'seller_amount' => $sellerAmount
            ];
        });
    }
}