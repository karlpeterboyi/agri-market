# Reports & Analytics Module – MkulimaHub

**Status:** Implemented (1 September 2026)

## Overview

Unified analytics and reporting layer for:

- Platform BI (admin / government)
- Marketplace performance
- Farm production & costs (Farm ERP)
- Indicator forecasting (from official statistics)
- Carbon / ESG proxy metrics
- Farmer personal dashboard
- Existing Finance & Admin reports remain available

## Architecture

```
AnalyticsController
    └── PlatformAnalyticsService
            ├── platformOverview()
            ├── marketplaceReport()
            ├── farmProductionReport()
            ├── forecastIndicator()   ← linear trend from agricultural_statistics
            └── esgSnapshot()         ← proxy carbon / land-use metrics
```

Existing specialised reports:
- `FinanceReportController` + `FinanceReportService` (loan portfolio, gender, youth, regions…)
- `AdminReportController` (quick counts + richer `/analytics` endpoint)
- `GovernmentDashboardController`

## API Endpoints

### Analytics (authenticated)
```
GET /api/analytics                     → catalogue of available reports
GET /api/analytics/platform            → platform BI overview (admin/gov)
GET /api/analytics/marketplace         → GMV, orders by status, listings by region
GET /api/analytics/farm-production     → crop cycles, yields, activity costs
GET /api/analytics/forecast            → ?indicator_code=&region=&years_ahead=
GET /api/analytics/esg                 → carbon / land-use snapshot
GET /api/analytics/farmer-dashboard    → farmer personal production + ESG
```

### Related existing endpoints
```
GET /api/finance/reports
GET /api/admin/reports
GET /api/admin/analytics          (subscription-gated richer admin analytics)
GET /api/government/dashboard
GET /api/agricultural-statistics/time-series
```

## Report Details

### Platform Overview
Users (farmers/buyers/providers), farms & area, marketplace GMV & escrow, loan pipeline, subsidy pipeline.

### Marketplace
- Orders by status
- Gross Merchandise Value (paid payments)
- Top listing regions
- Optional date filters: `?from=2026-01-01&to=2026-12-31`

### Farm Production
- Crop cycles grouped by crop (area, expected vs actual yield)
- Cost breakdown by activity type (input + labour)
- Scoped to farmer’s own farms (or any farm for admin)

### Forecast
Simple linear regression on annual `agricultural_statistics` for any `indicator_code` (e.g. `maize_production_mt`). Returns history + N-year forecast.

### ESG / Carbon
Proxy metrics only (replace with real emission factors later):
- Land use intensity
- Irrigation intensity
- Estimated sequestration (tCO₂e) based on cultivated area

## Roles
| Report | Typical roles |
|--------|----------------|
| Platform / Marketplace | admin, government_officer |
| Farm production / ESG / Farmer dashboard | farmer (+ admin) |
| Forecast | admin, government_officer, researcher |
| Finance reports | admin, financial_institution |

## Files Added
- `app/Services/Analytics/PlatformAnalyticsService.php`
- `app/Http/Controllers/Api/AnalyticsController.php`
- Enhanced `AdminReportController`
- Routes under `/api/analytics/*`
- This documentation

## Future Enhancements
- Saved / scheduled reports
- PDF / Excel export
- Real carbon models (practice-based emission factors)
- Machine-learning price & yield forecasts
- Role-based report builder UI

