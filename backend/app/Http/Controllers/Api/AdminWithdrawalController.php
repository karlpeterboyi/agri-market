<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\MpesaB2CService;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    /**
     * List pending withdrawals.
     */
    public function index()
    {
        return Withdrawal::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    /**
     * Approve withdrawal and send B2C payment.
     */
    public function approve(
        Withdrawal $withdrawal,
        MpesaB2CService $b2c
    ) {

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'message' => 'Withdrawal already processed'
            ], 422);
        }

        return DB::transaction(function () use (
            $withdrawal,
            $b2c
        ) {

            $response = $b2c->send(
                $withdrawal->phone_number,
                $withdrawal->amount
            );

            if (!$response['success']) {
                return response()->json([
                    'message' => 'B2C payout failed'
                ], 422);
            }

            $withdrawal->update([
                'status' => 'paid',
                'reference' => $response['reference'],
                'approved_at' => now(),
                'paid_at' => now(),
            ]);

            $wallet = Wallet::where(
                'user_id',
                $withdrawal->user_id
            )->firstOrFail();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $withdrawal->amount,
                'reference' => $response['reference'],
                'description' => 'Withdrawal payout'
            ]);

            return response()->json([
                'message' => 'Withdrawal approved successfully',
                'withdrawal' => $withdrawal
            ]);
        });
    }

    /**
     * Reject withdrawal and restore reserved funds.
     */
    public function reject(
        Withdrawal $withdrawal
    ) {

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'message' => 'Withdrawal already processed'
            ], 422);
        }

        return DB::transaction(function () use ($withdrawal) {

            $wallet = Wallet::where(
                'user_id',
                $withdrawal->user_id
            )->firstOrFail();

            $wallet->increment(
                'available_balance',
                $withdrawal->amount
            );

            $withdrawal->update([
                'status' => 'rejected',
                'approved_at' => now(),
            ]);

            return response()->json([
                'message' => 'Withdrawal rejected successfully'
            ]);
        });
    }
}