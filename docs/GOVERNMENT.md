# Government Module – MkulimaHub

**Status:** Implemented (1 September 2026)

## Overview

The Government module provides the institutional layer of MkulimaHub for ministries, LGAs, agencies and development partners.

### Capabilities

| Feature | Description |
|---------|-------------|
| **Announcements** | Official notices from Ministry of Agriculture, LGAs, plant health, etc. |
| **Subsidy Programmes** | Design and publish input/seed/equipment subsidy schemes |
| **Subsidy Applications** | Farmers apply → government reviews → approve/reject → disburse |
| **Agricultural Registrations** | Farm, trader, input dealer, processor, exporter, cooperative registration |
| **Statistics & Food Security** | Official production, price and food-security indicators + time series |
| **Government Dashboard** | Summary counts and recent activity for officers |

## API Endpoints

### Public
```
GET  /api/government/announcements
GET  /api/government/announcements/{id|slug}
GET  /api/subsidy-programs
GET  /api/subsidy-programs/{id}
GET  /api/agricultural-statistics
GET  /api/agricultural-statistics/food-security
GET  /api/agricultural-statistics/time-series?indicator_code=&region=&from_year=
```

### Authenticated – Farmers
```
POST /api/subsidy-applications
GET  /api/subsidy-applications
GET  /api/subsidy-applications/{id}
POST /api/subsidy-applications/{id}/submit

POST /api/agricultural-registrations
GET  /api/agricultural-registrations
GET  /api/agricultural-registrations/{id}
```

### Authenticated – Government Officers / Admin
```
GET  /api/government/dashboard
POST /api/government/announcements
PUT  /api/government/announcements/{id}
DELETE /api/government/announcements/{id}

POST /api/subsidy-programs
PUT  /api/subsidy-programs/{id}
DELETE /api/subsidy-programs/{id}

POST /api/subsidy-applications/{id}/review
POST /api/subsidy-applications/{id}/disburse

POST /api/agricultural-registrations/{id}/review
POST /api/agricultural-statistics
```

## Typical Flows

**Subsidy**
1. Government opens a programme (e.g. Fertilizer Subsidy 2026/27)
2. Farmer creates application (linked to farm) → submits
3. Officer reviews → approves / rejects
4. Officer marks as disbursed

**Registration**
1. Farmer / trader applies for registration
2. Officer reviews and issues / rejects (with optional expiry)

**Statistics**
- Public can query production, prices and food-security indicators
- Officers can post new data points
- Time-series endpoint ready for charts

## Seeded Demo Data
- 3 announcements (national fertiliser, Morogoro soil testing, SPS export rules)
- 2 open subsidy programmes (Fertilizer + Improved Maize Seed)
- Sample national & regional statistics (maize/rice production, food insecurity %, prices)

## Role
Use role `government_officer` (or `admin`) for management actions.

## Files Added
- Migration: `2026_09_01_100000_create_government_module_tables.php`
- Models: GovernmentAnnouncement, SubsidyProgram, SubsidyApplication, AgriculturalRegistration, AgriculturalStatistic
- Controllers: Announcement, SubsidyProgram, SubsidyApplication, Registration, Statistic, Dashboard
- Seeder: GovernmentSeeder
- Routes + this documentation

