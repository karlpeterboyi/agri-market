<?php

namespace App\Services\Nmb;

use App\Models\User;
use App\Models\UserBankLink;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Orchestrates NMB OBP payments for MkulimaHub money movement:
 * - Loan disbursement to farmer
 * - Marketplace escrow release to seller
 */
class NmbPayoutService
{
    public function __construct(protected NmbObpClient $nmb)
    {
    }

    public function escrowAccountId(): string
    {
        return (string) config('nmb.escrow_account_id', env('NMB_OBP_ESCROW_ACCOUNT_ID', 'mkulima-escrow-001'));
    }

    /**
     * Disburse an approved loan to the farmer's bank account / mobile money via NMB.
     *
     * @return array{ok:bool,mock:bool,transaction_request?:array,error?:string,mode:string}
     */
    public function disburseLoan(array $context): array
    {
        $amount = (float) ($context['amount'] ?? 0);
        $reference = (string) ($context['reference'] ?? 'loan');
        $accountNumber = $context['account_number'] ?? null;
        $accountName = $context['account_name'] ?? 'Farmer';
        $toAccountId = $context['to_account_id'] ?? null;
        $fromAccountId = $context['from_account_id'] ?? $this->escrowAccountId();
        $userId = $context['user_id'] ?? null;

        if ($amount <= 0) {
            return ['ok' => false, 'mock' => $this->nmb->isMock(), 'mode' => 'none', 'error' => 'Invalid amount'];
        }

        // Prefer explicit linked NMB account id
        if (!$toAccountId && $userId) {
            $toAccountId = $this->linkedNmbAccountId((int) $userId);
        }

        try {
            if ($toAccountId) {
                $tr = $this->nmb->createPaymentToAccount(
                    $fromAccountId,
                    $toAccountId,
                    $amount,
                    'TZS',
                    "Loan disbursement {$reference}"
                );

                return [
                    'ok' => true,
                    'mock' => $this->nmb->isMock(),
                    'mode' => 'ACCOUNT',
                    'transaction_request' => $tr,
                ];
            }

            if ($accountNumber) {
                $cp = $this->nmb->createCounterparty($fromAccountId, [
                    'name' => $accountName,
                    'account_number' => $accountNumber,
                    'description' => "Loan beneficiary {$reference}",
                ]);
                $cpId = $cp['id'] ?? $cp['counterparty_id'] ?? null;
                if (!$cpId) {
                    return ['ok' => false, 'mock' => $this->nmb->isMock(), 'mode' => 'COUNTERPARTY', 'error' => 'Counterparty id missing'];
                }

                $tr = $this->nmb->createPaymentToCounterparty(
                    $fromAccountId,
                    $cpId,
                    $amount,
                    'TZS',
                    "Loan disbursement {$reference}"
                );

                return [
                    'ok' => true,
                    'mock' => $this->nmb->isMock(),
                    'mode' => 'COUNTERPARTY',
                    'counterparty' => $cp,
                    'transaction_request' => $tr,
                ];
            }

            // Fallback: internal ACCOUNT transfer to synthetic farmer account (sandbox)
            $tr = $this->nmb->createPaymentToAccount(
                $fromAccountId,
                'farmer-wallet-'.($userId ?: 'unknown'),
                $amount,
                'TZS',
                "Loan disbursement {$reference} (wallet fallback)"
            );

            return [
                'ok' => true,
                'mock' => $this->nmb->isMock(),
                'mode' => 'ACCOUNT_FALLBACK',
                'transaction_request' => $tr,
            ];
        } catch (\Throwable $e) {
            Log::error('NMB loan disbursement failed', ['error' => $e->getMessage(), 'ref' => $reference]);

            return [
                'ok' => false,
                'mock' => $this->nmb->isMock(),
                'mode' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Pay seller net amount on escrow release.
     *
     * @return array{ok:bool,mock:bool,transaction_request?:array,error?:string,mode:string}
     */
    public function releaseEscrowToSeller(array $context): array
    {
        $amount = (float) ($context['amount'] ?? 0);
        $orderId = $context['order_id'] ?? null;
        $sellerId = $context['seller_id'] ?? null;
        $fromAccountId = $context['from_account_id'] ?? $this->escrowAccountId();

        if ($amount <= 0) {
            return ['ok' => false, 'mock' => $this->nmb->isMock(), 'mode' => 'none', 'error' => 'Invalid amount'];
        }

        $toAccountId = $context['to_account_id'] ?? null;
        $accountNumber = $context['account_number'] ?? null;
        $accountName = $context['account_name'] ?? 'Seller';

        if (!$toAccountId && $sellerId) {
            $toAccountId = $this->linkedNmbAccountId((int) $sellerId);
            $link = $this->primaryLink((int) $sellerId);
            if ($link) {
                $accountNumber = $accountNumber ?: $link->account_number;
                $accountName = $accountName ?: ($link->account_name ?: 'Seller');
            }
        }

        $description = 'Escrow release order #'.($orderId ?? '');

        try {
            if ($toAccountId) {
                $tr = $this->nmb->createPaymentToAccount(
                    $fromAccountId,
                    $toAccountId,
                    $amount,
                    'TZS',
                    $description
                );

                return [
                    'ok' => true,
                    'mock' => $this->nmb->isMock(),
                    'mode' => 'ACCOUNT',
                    'transaction_request' => $tr,
                ];
            }

            if ($accountNumber) {
                $cp = $this->nmb->createCounterparty($fromAccountId, [
                    'name' => $accountName,
                    'account_number' => $accountNumber,
                    'description' => $description,
                ]);
                $cpId = $cp['id'] ?? $cp['counterparty_id'] ?? null;
                $tr = $this->nmb->createPaymentToCounterparty(
                    $fromAccountId,
                    $cpId,
                    $amount,
                    'TZS',
                    $description
                );

                return [
                    'ok' => true,
                    'mock' => $this->nmb->isMock(),
                    'mode' => 'COUNTERPARTY',
                    'counterparty' => $cp,
                    'transaction_request' => $tr,
                ];
            }

            $tr = $this->nmb->createPaymentToAccount(
                $fromAccountId,
                'seller-wallet-'.($sellerId ?: 'unknown'),
                $amount,
                'TZS',
                $description.' (wallet fallback)'
            );

            return [
                'ok' => true,
                'mock' => $this->nmb->isMock(),
                'mode' => 'ACCOUNT_FALLBACK',
                'transaction_request' => $tr,
            ];
        } catch (\Throwable $e) {
            Log::error('NMB escrow release payout failed', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return [
                'ok' => false,
                'mock' => $this->nmb->isMock(),
                'mode' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function linkedNmbAccountId(int $userId): ?string
    {
        $link = $this->primaryLink($userId);

        return $link?->nmb_account_id ?: null;
    }

    protected function primaryLink(int $userId): ?UserBankLink
    {
        if (!class_exists(UserBankLink::class) || !Schema::hasTable('user_bank_links')) {
            return null;
        }

        return UserBankLink::where('user_id', $userId)
            ->where('provider', 'nmb')
            ->orderByDesc('verified')
            ->orderByDesc('id')
            ->first();
    }
}
