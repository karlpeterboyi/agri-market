<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get or create the authenticated user's wallet.
     */
    public function show()
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'available_balance' => 0,
                'pending_balance' => 0,
            ]
        );

        return response()->json($wallet);
    }

    /**
     * Transaction history.
     */
    public function transactions(Request $request)
    {
        $wallet = Wallet::where('user_id', auth()->id())->first();

        if (!$wallet) {
            return response()->json([]);
        }

        $query = $wallet->transactions()->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->paginate(30));
    }

    /**
     * Simple summary.
     */
    public function summary()
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => auth()->id()],
            ['available_balance' => 0, 'pending_balance' => 0]
        );

        $credits = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', 'credit')
            ->sum('amount');

        $debits = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', 'debit')
            ->sum('amount');

        return response()->json([
            'available_balance' => $wallet->available_balance,
            'pending_balance' => $wallet->pending_balance,
            'total_credits' => $credits,
            'total_debits' => $debits,
        ]);
    }
}
