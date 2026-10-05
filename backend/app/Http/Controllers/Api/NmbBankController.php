<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBankLink;
use App\Services\Nmb\NmbObpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * MkulimaHub ↔ NMB OBP fintech bridge.
 *
 * Surfaces the most useful OBP capabilities for agri-finance:
 * bank status, accounts/balances, transactions, customer onboarding,
 * counterparties, and payments (loan disbursement / seller payout).
 */
class NmbBankController extends Controller
{
    public function __construct(protected NmbObpClient $nmb)
    {
    }

    public function status()
    {
        return response()->json($this->nmb->status());
    }

    public function banks()
    {
        return response()->json($this->nmb->getBanks());
    }

    public function bank()
    {
        return response()->json($this->nmb->getBank());
    }

    public function accounts()
    {
        return response()->json($this->nmb->getAccountsAtBank());
    }

    public function balances(Request $request)
    {
        if ($request->filled('account_id')) {
            return response()->json(
                $this->nmb->getAccountBalances($request->account_id)
            );
        }

        return response()->json($this->nmb->getAccountsBalances());
    }

    public function transactions(Request $request)
    {
        $accountId = $request->validate([
            'account_id' => 'required|string',
        ])['account_id'];

        return response()->json(
            $this->nmb->getTransactions($accountId)
        );
    }

    /**
     * Onboard current user (or given payload) as NMB customer.
     */
    public function createCustomer(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'legal_name' => 'nullable|string|max:255',
            'mobile_phone_number' => 'nullable|string|max:40',
            'email' => 'nullable|email',
            'date_of_birth' => 'nullable|date',
        ]);

        $payload = [
            'legal_name' => $data['legal_name'] ?? $user->name,
            'mobile_phone_number' => $data['mobile_phone_number'] ?? $user->phone,
            'email' => $data['email'] ?? $user->email,
            'date_of_birth' => $data['date_of_birth'] ?? '1990-01-01',
        ];

        $customer = $this->nmb->createCustomer($payload);

        $this->saveLink($user, [
            'nmb_customer_id' => $customer['customer_id'] ?? $customer['id'] ?? null,
            'meta' => $customer,
        ]);

        return response()->json([
            'message' => 'Customer registered with NMB (sandbox/live per config)',
            'customer' => $customer,
            'mock' => $this->nmb->isMock(),
        ], 201);
    }

    public function createCounterparty(Request $request)
    {
        $data = $request->validate([
            'account_id' => 'required|string',
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:64',
            'bank_code' => 'nullable|string|max:32',
            'description' => 'nullable|string',
        ]);

        $cp = $this->nmb->createCounterparty($data['account_id'], [
            'name' => $data['name'],
            'account_number' => $data['account_number'],
            'other_bank_routing_address' => $data['bank_code'] ?? 'NMB',
            'description' => $data['description'] ?? $data['name'],
        ]);

        return response()->json([
            'message' => 'Counterparty created',
            'counterparty' => $cp,
            'mock' => $this->nmb->isMock(),
        ], 201);
    }

    /**
     * Initiate payment — used for loan disbursement & marketplace seller payout.
     */
    public function pay(Request $request)
    {
        $data = $request->validate([
            'from_account_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:500',
            // COUNTERPARTY path
            'counterparty_id' => 'nullable|string',
            // ACCOUNT path
            'to_account_id' => 'nullable|string',
            // purpose tags for MkulimaHub
            'purpose' => 'nullable|in:loan_disbursement,seller_payout,escrow_release,withdrawal,other',
            'reference' => 'nullable|string|max:100',
        ]);

        if (empty($data['counterparty_id']) && empty($data['to_account_id'])) {
            return response()->json([
                'message' => 'Provide counterparty_id or to_account_id',
            ], 422);
        }

        $description = $data['description']
            ?? (($data['purpose'] ?? 'transfer').' '.($data['reference'] ?? ''));

        if (!empty($data['counterparty_id'])) {
            $result = $this->nmb->createPaymentToCounterparty(
                $data['from_account_id'],
                $data['counterparty_id'],
                (float) $data['amount'],
                $data['currency'] ?? 'TZS',
                trim($description)
            );
        } else {
            $result = $this->nmb->createPaymentToAccount(
                $data['from_account_id'],
                $data['to_account_id'],
                (float) $data['amount'],
                $data['currency'] ?? 'TZS',
                trim($description)
            );
        }

        return response()->json([
            'message' => 'Payment request created',
            'transaction_request' => $result,
            'mock' => $this->nmb->isMock(),
            'purpose' => $data['purpose'] ?? 'other',
        ], 201);
    }

    /**
     * Link farmer bank account number in MkulimaHub (local record + optional NMB customer).
     */
    public function linkAccount(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'account_number' => 'required|string|max:64',
            'account_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:32',
            'nmb_account_id' => 'nullable|string|max:100',
        ]);

        $link = $this->saveLink($user, [
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'] ?? $user->name,
            'bank_code' => $data['bank_code'] ?? 'NMB',
            'nmb_account_id' => $data['nmb_account_id'] ?? null,
            'verified' => false,
        ]);

        return response()->json([
            'message' => 'Bank account linked',
            'link' => $link,
            'nmb' => $this->nmb->status(),
        ], 201);
    }

    public function myLinks(Request $request)
    {
        if (!class_exists(UserBankLink::class) || !Schema::hasTable('user_bank_links')) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => UserBankLink::where('user_id', $request->user()->id)->latest()->get(),
        ]);
    }

    public function transactionRequestTypes(Request $request)
    {
        $accountId = $request->validate(['account_id' => 'required|string'])['account_id'];

        return response()->json(
            $this->nmb->getTransactionRequestTypes($accountId)
        );
    }

    protected function saveLink(User $user, array $attrs): ?UserBankLink
    {
        if (!class_exists(UserBankLink::class) || !Schema::hasTable('user_bank_links')) {
            return null;
        }

        return UserBankLink::updateOrCreate(
            [
                'user_id' => $user->id,
                'account_number' => $attrs['account_number'] ?? ($attrs['nmb_customer_id'] ?? 'unknown'),
            ],
            array_merge([
                'provider' => 'nmb',
                'bank_code' => 'NMB',
            ], $attrs)
        );
    }
}
