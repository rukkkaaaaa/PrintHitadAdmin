# PrintHitad Admin

Admin dashboard for managing the PrintHitad / Lahipita advertisement workflow.

This README now explains both **how to run** the app and **what is happening inside the system** during daily usage.

## ✨ What this app does

- Session-based admin authentication
- User and role management
- Advertisement lifecycle management (create, edit, view, download)
- Paid/unpaid and publication-based ad listings
- Master data management:
	- Categories
	- Advertisement types
	- Advertisement sizes
	- Tints
	- Criteria and criteria options
	- Districts and cities
- Members listing (from `customers`)
- Monthly report generation + PDF export
- Publication deadline and general settings management

## 🔄 What is happening in this system (workflow)

In practical terms, this is the day-to-day flow:

1. **Admin logs in**
	- Unauthenticated users are redirected to `/login`.
	- After login, users are taken to `/dashboard`.

2. **Role-based access is applied**
	- `super admin`: access to everything.
	- `site admin`: only configuration/master-data areas (categories, types, sizes, tints, criteria, locations).
	- reporting roles: mainly reports and dashboard.
	- advertising roles: advertisement creation and advertisement-related screens.

3. **Master data is managed**
	- Teams configure categories, ad types, ad sizes, tints, criteria/options, districts, and cities.
	- These become selectable data for advertisement forms.

4. **Advertisement is created/updated**
	- Ads are created from `/advertisements/create`.
	- The form behavior changes by publication (Hitad/Lahipita) and selected options.
	- Pricing is calculated from configured settings/rates and ad details.
	- Uploads (images/documents) are saved through configured storage.

5. **Operations tracking**
	- Ads are reviewed in listing pages (`/advertisements`, paid/unpaid pages, and Lahipita subsets).
	- Staff can view details, edit records, download outputs, and track payment status.

6. **Monthly reporting**
	- `/reports` summarizes monthly activity.
	- PDFs are exported via `/reports/{type}/pdf?month=YYYY-MM`.

7. **Deadline/settings enforcement**
	- Publication deadlines and general settings affect eligibility and pricing logic in the ad workflow.

## 🧱 Tech stack

- PHP `^8.1`
- Laravel `^10`
- MySQL / MariaDB
- Vite `^4`
- `barryvdh/laravel-dompdf` (PDF output)
- S3-compatible object storage (`oracle` disk) for uploads

## ✅ Requirements

Before running locally, make sure you have:

- PHP 8.1+
- Composer
- Node.js + npm
- MySQL/MariaDB database

## 🚀 Local setup

### 1) Clone and install dependencies

```bash
composer install
npm install
```

### 2) Configure environment

If `.env` is missing, create it from `.env.example`.

Set at minimum:

- App
	- `APP_NAME`
	- `APP_ENV`
	- `APP_URL`
- Database
	- `DB_CONNECTION`
	- `DB_HOST`
	- `DB_PORT`
	- `DB_DATABASE`
	- `DB_USERNAME`
	- `DB_PASSWORD`
- Mail (if using email features)
	- `MAIL_*`

Generate app key:

```bash
php artisan key:generate
```

### 3) Configure object storage (recommended)

Uploads use the `oracle` disk in `config/filesystems.php`.

Add these variables to `.env` when using upload flows:

- `OCI_ACCESS_KEY_ID`
- `OCI_SECRET_ACCESS_KEY`
- `OCI_DEFAULT_REGION` (default: `ap-singapore-1`)
- `OCI_BUCKET`
- `OCI_URL`

> If OCI values are missing or invalid, upload-related features may fail.

### 4) Run database migrations

```bash
php artisan migrate
```

If you point to an already-initialized legacy DB and hit duplicate table errors, run only required migration files with `--path`.

### 5) Start the app

Use two terminals:

```bash
php artisan serve
npm run dev
```

Open your configured app URL (typically `http://127.0.0.1:8000`).

## 🔐 Authentication and access

- Login page: `/login`
- Registration page: `/register`
- Root route (`/`) redirects to `/login` when unauthenticated
- Dashboard: `/dashboard`

Role checks are enforced in middleware and include special handling for:

- `super admin` (full access)
- reporting roles (reports/dashboard-focused access)
- advertising roles (advertisement-focused access)
- `site admin` (configuration sections only)

## 🧭 Route quick reference

- Dashboard: `/dashboard`
- Users: `/users`
- Reports: `/reports`, `/reports/{type}/pdf`
- Members: `/members`
- Advertisements:
	- `/all-print-ads`
	- `/advertisements`
	- `/advertisements/create`
	- `/advertisements/{id}/view`
	- `/advertisements/{id}/edit`
	- `/advertisements/paid`
	- `/advertisements/unpaid`
	- Lahipita-specific subsets under `/advertisements/lahipita...`
- Master data:
	- `/categories`
	- `/adtypes`
	- `/adsizes`
	- `/tints`
	- `/adcriterias`
	- `/adcriteria-options`
	- `/districts`
	- `/cities`
- Settings:
	- `/publication-deadlines`
	- `/general-settings`

## 🛠️ Useful commands

```bash
# Run tests
php artisan test

# Build frontend assets
npm run build

# Clear optimized/cache state
php artisan optimize:clear
```

## 📁 High-level structure

- `app/Http/Controllers/` — main controllers (`AuthController`, `GeneralController`)
- `app/Http/Middleware/` — session auth and role restrictions
- `resources/views/` — Blade templates
- `routes/web.php` — web routes
- `database/migrations/` — schema migrations
- `config/` — framework/service configuration

## 🩺 Troubleshooting

- **Redirect to `/login` unexpectedly**
	- Session may be missing/expired, or route is protected.
- **Migration “table already exists” errors**
	- Use targeted migration execution with `--path` for incremental updates.
- **Uploaded files not accessible**
	- Verify OCI credentials, endpoint, bucket, and visibility settings.
- **Frontend changes not appearing**
	- Restart `npm run dev`, clear browser cache, and ensure Vite is running.

## 📜 License

MIT (see `LICENSE`).
