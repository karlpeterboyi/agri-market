# NMB automatic payouts

## Loan disburse
LoanWorkflowService::disburse() calls NmbPayoutService::disburseLoan().

Priority: nmb_account_id / linked bank link → ACCOUNT transfer; else account_number → COUNTERPARTY; else farmer-wallet-{id} fallback.

## Escrow release
EscrowService::release() after wallet 90/10 calls NmbPayoutService::releaseEscrowToSeller().

## Config
NMB_OBP_ESCROW_ACCOUNT_ID=mkulima-escrow-001
NMB_OBP_MOCK=true
