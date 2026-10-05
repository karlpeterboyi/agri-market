# Agri-market (MkulimaHub)

Digital agriculture marketplace for Tanzania / East Africa: Farm ERP, crop & input marketplace, escrow payments, logistics, finance, and knowledge hub.

## Stack

- **Backend:** Laravel 13, PostgreSQL, Sanctum, Spatie Permission  
- **Frontend:** Vue 3, Vite, Tailwind, Pinia  
- **Payments:** Pesapal (+ NMB Open Banking sandbox hooks)

## Local development

```bash
# Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
# configure DB_* in .env (pgsql)
php artisan migrate --seed
php artisan storage:link
php artisan serve

# Frontend
cd frontend
npm install
npm run dev -- --host
```

Default seeded admin (if seeders run): see project docs / `DatabaseSeeder`.

## Deploy on Render

1. Push this repo to GitHub (already: `karlpeterboyi/agri-market`).
2. [Render Dashboard](https://dashboard.render.com) → **New** → **Blueprint** → select this repository.
3. Apply `render.yaml` (creates free Postgres + Docker web service).
4. After first deploy, set **APP_URL** to the Render URL (e.g. `https://agri-market.onrender.com`).
5. Optional: set `PESAPAL_*`, `FRONTEND_URL` (same as APP_URL), NMB keys.

### Manual (without Blueprint)

- **PostgreSQL** free instance  
- **Web Service** → Docker → root Dockerfile  
- Link DB env vars as in `render.yaml`

Free tier spins down after idle; first request may be slow.

## License

Private / project use — Agrihub Int. (T)
