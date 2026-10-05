# Knowledge Module – MkulimaHub

**Status:** Implemented (August 2026)  
**Covers:** Research • Extension • Training (Digital Academy) • AI Advisor • Knowledge Hub

## Overview

The Knowledge module turns MkulimaHub into a digital agricultural academy and advisory platform.

### Components

| Area | What it provides | Status |
|------|------------------|--------|
| **Research** | Institutions, Researchers, Publications, Demonstration Farms | ✅ Already strong, kept & exposed |
| **Extension** | Extension Officers, Ratings, Advisory Requests, Farmer Advisories | ✅ Existing + used |
| **Training** | Courses, Enrollments, Progress, Certificates | ✅ **New** |
| **AI Advisor** | Farm-specific recommendations (rule-based demo → ready for real AI) | ✅ **New** |
| **Knowledge Hub** | Unified landing + search across all knowledge content | ✅ **New** |

## New API Endpoints

### Public
```
GET  /api/knowledge-hub
GET  /api/knowledge-hub/search?q=
GET  /api/training-courses
GET  /api/training-courses/{id|slug}
```

### Authenticated
```
POST   /api/training-courses                  (create – admin/researcher/extension)
PUT    /api/training-courses/{id}
DELETE /api/training-courses/{id}
POST   /api/training-courses/{id}/enroll
POST   /api/training-courses/{id}/progress
GET    /api/my-course-enrollments

GET    /api/ai-recommendations
POST   /api/ai-recommendations/generate
GET    /api/ai-recommendations/{id}
POST   /api/ai-recommendations/{id}/accept
```

### Already existing (Research & Extension)
```
GET /api/research-institutions
GET /api/researchers
GET /api/research-publications
GET /api/demonstration-farms
GET /api/extension-officers
POST /api/advisory-requests
GET  /api/my-advisory-requests
...
```

## Training Courses

- Categories: crop_production, livestock, finance, pest_disease, climate, digital_agriculture, marketing…
- Levels: beginner / intermediate / advanced
- Languages: Swahili (`sw`) & English (`en`)
- Free or paid
- Enrollment + progress tracking + certificate code on completion
- Seeded with 5 realistic Tanzania-relevant courses

## AI Advisor

- Currently **rule-based** (demo) so the platform works offline of external AI keys.
- Designed to be swapped for OpenAI / local LLM / TensorFlow later.
- Categories: pest, soil, weather, market, general…
- Recommendations stored per farm; farmer can accept them.
- Linked to Farm ERP farms.

## Typical User Journeys

**Farmer**
1. Open Knowledge Hub → see featured courses, latest research, nearby extension officers
2. Search “fall armyworm” or “maize”
3. Enrol in a free course and track progress
4. Request advisory from an extension officer
5. Generate AI recommendation for a specific farm / crop stage

**Researcher / Institution**
1. Publish research publications & demonstration farms
2. Create training courses
3. Link content to their institution

## Files Added / Updated

- `database/migrations/2026_08_31_120000_create_training_courses_table.php`
- `app/Models/TrainingCourse.php`, `CourseEnrollment.php`
- `app/Http/Controllers/Api/TrainingCourseController.php`
- `app/Http/Controllers/Api/AIAdvisorController.php`
- `app/Http/Controllers/Api/KnowledgeHubController.php`
- `database/seeders/TrainingCourseSeeder.php`
- Routes in `routes/api.php`
- This documentation

## Next Possible Enhancements

- Full video lesson player + quizzes + exams
- Learning paths / certificates downloadable PDF
- Real AI integration (OpenAI / local model) behind AIAdvisorController
- Community forums / Q&A
- Offline content packs for low-connectivity areas

