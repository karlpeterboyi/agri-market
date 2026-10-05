# NMB Bank OBP → MkulimaHub Agri-Fintech

**API Explorer:** https://obp-apiexplorer-sandbox.nmbbank.co.tz/resource-docs/OBPv6.0.0  
**Sandbox host:** https://obp-api-sandbox.nmbbank.co.tz  
**Default bank_id:** `nmbb.01.tz.nmbb`

## Priority endpoints chosen (why)

| OBP capability | Use in MkulimaHub |
|----------------|-------------------|
| DirectLogin | App ↔ bank auth |
| Get Banks / Bank | Bank discovery |
| Get Accounts + Balances | Farmer/FI linked balances, escrow monitoring |
| Get Transactions | Credit scoring, repayment verification, reconciliation |
| Create Customer | Onboard farmers & agribusinesses |
| Create Counterparty | Seller / farmer payout beneficiaries |
| Transaction Request COUNTERPARTY / ACCOUNT | Loan disbursement, escrow release, withdrawals |

## MkulimaHub API bridge

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/nmb/status` | Config / mock mode |
| GET | `/api/nmb/banks` | List banks |
| GET | `/api/nmb/accounts` | Accounts at NMB |
| GET | `/api/nmb/balances?account_id=` | Balances |
| GET | `/api/nmb/transactions?account_id=` | Transactions |
| POST | `/api/nmb/customers` | Onboard current user |
| POST | `/api/nmb/counterparties` | Create beneficiary |
| POST | `/api/nmb/pay` | Payment / disbursement |
| POST | `/api/nmb/link-account` | Store local bank link |
| GET | `/api/nmb/my-links` | User bank links |

## .env

```env
NMB_OBP_BASE_URL=https://obp-api-sandbox.nmbbank.co.tz
NMB_OBP_API_VERSION=v5.0.0
NMB_OBP_BANK_ID=nmbb.01.tz.nmbb
NMB_OBP_CONSUMER_KEY=
NMB_OBP_CONSUMER_SECRET=
NMB_OBP_USERNAME=
NMB_OBP_PASSWORD=
NMB_OBP_VIEW_ID=owner
NMB_OBP_MOCK=true
```

Set `NMB_OBP_MOCK=false` and fill consumer + user credentials after registering an app on the NMB sandbox.

## UI

Authenticated: `/finance/nmb`

## Next (not yet wired)

- Auto-call `/nmb/pay` from loan `disburse` and order escrow `release`
- Consent / AIS flows for farmer-permissioned account access
- Webhooks for payment status
