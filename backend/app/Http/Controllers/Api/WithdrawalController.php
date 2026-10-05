<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\NmbLinkPolicy;

class WithdrawalController extends Controller
{
    /**
     * Seller requests a withdrawal.
     * Validates against the available balance without deducting yet.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'phone' => 'required|string',
        ]);

        $wallet = Wallet::where(
            'user_id',
            auth()->id()
        )->firstOrFail();

        // Before creating a withdrawal, validate against the available balance
        if ($wallet->available_balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient available balance.'
            ], 422);
        }

        $linkCheck = NmbLinkPolicy::assertCanWithdraw(auth()->user(), (float) $validated['amount']);
        if (!$linkCheck['ok']) {
            return response()->json([
                'message' => $linkCheck['message'],
                'policy' => 'nmb_link_required_for_large_withdrawal',
                'threshold_tzs' => NmbLinkPolicy::withdrawalThreshold(),
            ], $linkCheck['code'] ?? 422);
        }

        // Create the withdrawal request with a 'pending' status
        $withdrawal = Withdrawal::create([
            'user_id' => auth()->id(),
            'amount' => $validated['amount'],
            'phone' => $validated['phone'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Withdrawal request submitted successfully.',
            'withdrawal' => $withdrawal
        ], 201);
    }

    /**
     * Seller views their withdrawal history.
     */
    public function index()
    {
        return Withdrawal::where(
            'user_id',
            auth()->id()
        )
        ->latest()
        ->get();
    }
}
