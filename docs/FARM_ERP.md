# Farm ERP Module – MkulimaHub

**Status:** Core implementation completed (August 2026)  
**Part of:** Delivery Phase A → Farm ERP

## Overview

The Farm ERP module allows farmers and organisations to digitally manage their farms as a complete enterprise resource system.

### Capabilities Implemented

| Feature | Description | Status |
|---------|-------------|--------|
| **Farms** | Multi-farm ownership, location, area, type, certification | ✅ Complete |
| **Field Blocks** | Sub-divisions of a farm with area & boundary support | ✅ Complete |
| **Crop Cycles** | Planting → growing → harvest tracking with expected vs actual yield | ✅ Complete |
| **Farm Activities** | Daily operations (planting, weeding, spraying, irrigation, harvesting…) with cost tracking | ✅ Complete |
| **Warehouses** | On-farm storage locations | ✅ Complete |
| **Inventory** | Seeds, fertilisers, chemicals, feed, tools with min stock levels | ✅ Complete |
| **Stock Movements** | In / out / adjustment movements linked to activities | ✅ Partial (controller existed) |
| **Cost Summary** | Activity cost aggregation by type and period | ✅ Complete |
| **Harvest Recording** | Dedicated harvest endpoint for crop cycles | ✅ Complete |

## API Endpoints (Authenticated)

### Farms
- `GET /api/farms`
- `POST /api/farms`
- `GET /api/farms/{id}`
- `PUT /api/farms/{id}`
- `DELETE /api/farms/{id}`

### Field Blocks
- `GET /api/field-blocks?farm_id=`
- `POST /api/field-blocks`
- `GET /api/field-blocks/{id}`
- `PUT /api/field-blocks/{id}`
- `DELETE /api/field-blocks/{id}`

### Crop Cycles
- `GET /api/crop-cycles?farm_id=&status=&season=`
- `POST /api/crop-cycles`
- `GET /api/crop-cycles/{id}`
- `PUT /api/crop-cycles/{id}`
- `DELETE /api/crop-cycles/{id}`
- `POST /api/crop-cycles/{id}/harvest`  ← record actual harvest

### Farm Activities
- `GET /api/farm-activities?farm_id=&activity_type=&from_date=&to_date=`
- `POST /api/farm-activities`
- `GET /api/farm-activities/{id}`
- `PUT /api/farm-activities/{id}`
- `DELETE /api/farm-activities/{id}`
- `GET /api/farm-activities-cost-summary?farm_id=&from_date=&to_date=`

### Warehouses & Inventory
- `GET/POST /api/farm-warehouses`
- `GET/PUT/DELETE /api/farm-warehouses/{id}`
- `GET/POST /api/inventory-items`
- `GET/PUT/DELETE /api/inventory-items/{id}`
- Stock movements endpoints already existed

## Typical Farmer Workflow

1. Create Farm
2. Add Field Blocks
3. Create Crop Cycle on a Field Block (select crop + variety + planting date)
4. Log Farm Activities (linked to cycle or block) – costs are captured
5. Manage Inventory in Warehouses (seeds, fertiliser…)
6. At harvest → call `/harvest` endpoint with actual yield
7. View cost summary for profitability analysis

## Ownership & Security

- All resources are scoped to the authenticated user via `owner_id` on the Farm.
- Controllers enforce ownership checks before any mutation.
- Soft deletes used where appropriate (Farm, FarmActivity).

## Next Improvements (still open)

- [ ] GIS polygon drawing for boundaries (frontend + GeoJSON)
- [ ] Irrigation schedule module
- [ ] Asset / Machinery register linked to farm
- [ ] Livestock herd management integrated into Farm ERP (currently more marketplace-focused)
- [ ] Full financial P&L view per farm / per crop cycle
- [ ] Mobile offline support for activity logging
- [ ] Frontend pages for all the new endpoints

## Related Seed Data

`FarmSeeder` creates realistic farms in Morogoro, Arusha and Mbeya with field blocks and a main warehouse for every farmer user.

---

**Implemented by:** Grok / MkulimaHub Development  
**Date:** 31 August 2026
