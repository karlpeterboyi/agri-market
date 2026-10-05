# Finance Module – MkulimaHub

**Status:** Core implementation completed (August 2026)

## Overview

The Finance module provides:

1. **Farmer / User Wallet** – available & pending balances, transactions, withdrawals
2. **Payments** – Pesapal integration for marketplace orders (already present)
3. **Loan Products & Applications** – full lifecycle with workflow state machine
4. **Chart of Accounts + Journal Entries** – double-entry bookkeeping for farm financial management
5. **Finance Dashboard & Reports** – existing controllers for readiness, recommendations, credit score

## Key Features Implemented / Completed

### Wallet
- `GET /api/wallet` – get or create wallet
- `GET /api/wallet/transactions`
- `GET /api/wallet/summary`
- `POST /api/wallet/withdraw` (existing WithdrawalController)
- Admin approval of withdrawals (existing)

### Loan Products
- Full CRUD for institutions/admins
- Public listing of active products with filters (type, amount, featured)
- `GET/POST /api/loan-products`

### Loan Applications
- Farmer creates draft application
- Full workflow:
  - submit → under_review → approve / reject → disburse → generate schedule → complete
- Ownership & role checks
- `GET/POST /api/loan-applications`
- Workflow actions under `/api/loan-applications/{id}/...`

### Chart of Accounts
- `GET/POST /api/accounts`
- Types: asset, liability, equity, income, expense
- Hierarchical (parent/child)
- Farm or organisation scoped

### Journal Entries (Double-Entry)
- Balanced entries enforced (debits == credits)
- Multiple lines per entry
- Linked to farm
- `GET/POST /api/journal-entries`

## Typical Flows

**Loan**
1. Browse loan products
2. Create application (draft)
3. Submit → Institution reviews → Approves
4. Disburse to bank / mobile money / wallet
5. Generate repayment schedule
6. Track repayments → Complete

**Farm Accounting**
1. Create accounts (or use seeded chart)
2. Post journal entries for activities, inventory, sales, expenses
3. Use for P&L and balance sheet views (future reports)

## Existing Supporting Pieces
- LoanWorkflowService + State Machine + Events
- FinanceApiController (dashboard, readiness, recommendations)
- FinanceDashboardController & FinanceReportController
- CreditScore model
- FinancialInstitution + categories
- LoanProduct, LoanApplication, Disbursement, Repayment, Collateral, Guarantor models
- ChartOfAccountsSeeder, LoanProductSeeder, etc.

## Next Improvements
- [ ] Insurance products & policies
- [ ] Savings / Investment marketplace
- [ ] Automatic journal entries from farm activities & marketplace sales
- [ ] Full repayment recording UI + auto status updates
- [ ] Credit scoring engine refinements
- [ ] Frontend pages for loans & farm accounting

