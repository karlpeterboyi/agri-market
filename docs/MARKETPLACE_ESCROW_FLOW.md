# Unified marketplace business flow

Applies to **produce (crops)**, **farm inputs**, and **machinery**.

```
Seller lists item
        ↓
Buyer views listing → Buy now  OR  Make offer → Seller accepts
        ↓
Order created (status: pending_payment)
  - total_amount
  - platform_fee = 10%
  - seller_net = 90%
  - LogisticsRequest (pending)
        ↓
Buyer pays (Pesapal / markPaid)
        ↓
EscrowService::hold()
  - order status → in_escrow
  - payment status → paid, escrow_amount = total
  - seller wallet pending_balance += 90%
        ↓
Logistics: assign → in_transit → delivered
        ↓
Buyer confirms delivery  POST /orders/{id}/confirm-delivery
        ↓
EscrowService::release()
  - seller pending → available (90%)
  - admin wallet available += 10% (MkulimaHub)
  - payment status → released
  - order status → completed
```

## API

### Create order
`POST /api/orders` (auth)

```json
{
  "marketplace_type": "product|input|machinery",
  "listing_id": 1,
  "marketplace_id": 1,
  "quantity": 2,
  "pickup_region": "Morogoro",
  "pickup_district": "Kilosa",
  "delivery_region": "Dar es Salaam",
  "delivery_district": "Ilala"
}
```

### Pay
`POST /api/payments` `{ "order_id": 1, "phone": "07..." }`  
or test: `POST /api/payments/{id}/paid`

### Confirm delivery (buyer)
`POST /api/orders/{id}/confirm-delivery`

## Fee
`EscrowService::PLATFORM_FEE_RATE = 0.10`
