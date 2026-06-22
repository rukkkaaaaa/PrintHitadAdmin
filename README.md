# PrintHitadAdmin

Admin panel for managing print advertisement workflows (HitAd / Lahipita), including categories, ad types, ad sizes, tints, criteria, members, payments, and reporting.

Built with Laravel 10 + Vite.

## Tech Stack

- PHP `^8.1`
- Laravel `^10`
- MySQL / MariaDB
- Vite `^4`
- DomPDF (`barryvdh/laravel-dompdf`) for PDF generation
- S3-compatible object storage (configured as `oracle` disk) for file uploads

## Core Modules

- Authentication & session-based admin access
- User management (`super admin`, `site admin`, `advertising admin`, `report admin`)
- Categories, ad types, ad sizes, tints
- Criteria and criteria options
- Districts, cities, members
- Advertisement create/edit/view/download flows
- Payment tracking (paid/unpaid)
- Monthly reports + PDF exports
- Publication cutoff settings and general pricing settings

## Local Setup

### 1) Install dependencies

```bash
composer install
npm install
```

### 2) Environment configuration

The repository contains both `.env` and `.env.example`.

Ensure `.env` includes correct values for:

- `APP_URL`
- `DB_*` (database connection)
- Mail settings (`MAIL_*`) if email features are used
- OCI/S3 storage settings (required for upload flows):
	- `OCI_ACCESS_KEY_ID`
	- `OCI_SECRET_ACCESS_KEY`
	- `OCI_DEFAULT_REGION`
	- `OCI_BUCKET`
	- `OCI_URL`

Generate app key if not set:

```bash
php artisan key:generate
```

### 3) Database setup

Run migrations:

```bash
php artisan migrate
```

If you are connecting to an already-initialized legacy database, a full migrate may fail on existing base tables; in that case run only required new migration files using `--path`.

### 4) Start the app

In separate terminals:

```bash
php artisan serve
npm run dev
```

Open the app at the URL configured in `APP_URL` (default: `http://127.0.0.1:8000`).

## Authentication Notes

- Login page: `/login`
- Registration page: `/register`
- Root route `/` redirects to `/login` when no session exists.
- After login, users are redirected to `/dashboard`.

## Important Routes (Quick Reference)

- Dashboard: `/dashboard`
- Advertisements: `/advertisements`, `/advertisements/create`, `/all-print-ads`
- Categories: `/categories`
- Ad Types: `/adtypes`
- Ad Sizes: `/adsizes`
- Tints: `/tints`
- Criteria: `/adcriterias`, `/adcriteria-options`
- Locations: `/districts`, `/cities`
- Members: `/members`
- Reports: `/reports`
- Settings: `/publication-deadlines`, `/general-settings`

## Development Commands

```bash
# Run automated tests
php artisan test

# Frontend build
npm run build

# Clear cached framework state
php artisan optimize:clear
```

## File Upload / Storage

This project uses an S3-compatible disk named `oracle` in `config/filesystems.php` for several upload flows (NIC images, ad images, payment slips, etc.).

If OCI settings are missing, upload-related features can fail even when the app boots correctly.

## Troubleshooting

- **302 on `/` during tests**: root route redirects to `/login` when unauthenticated.
- **Migration conflicts (`table already exists`)**: use targeted migration with `php artisan migrate --path=...`.
- **Assets not updating**: restart `npm run dev` and clear browser cache.
- **Upload URL issues**: verify `OCI_URL` + `OCI_BUCKET` and object visibility.

## Project Structure (High-Level)

- `app/Http/Controllers/` — application controllers (`AuthController`, `GeneralController`)
- `resources/views/` — Blade templates
- `routes/web.php` — main web routes
- `database/migrations/` — schema history
- `config/` — framework and service configuration

## License

This project is distributed under the MIT license (inherited from Laravel project template unless changed by project owners).
