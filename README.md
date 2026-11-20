# Event Management & Ticketing Platform

A Laravel event management and ticketing application with a public event site, admin/organizer panels, attendee accounts, QR ticket scanning, and versioned-by-contract JSON APIs for user, organization, and scanner clients.

> See [`overview.md`](overview.md) for a codebase map and [`docs/audit.md`](docs/audit.md) for the modernization audit history.

## Stack

- **Backend:** PHP 8.2+, Laravel 12, Eloquent/MySQL, Laravel Passport 12, Spatie Laravel Permission 6
- **Frontend:** Blade, Vite 6, Tailwind CSS 3, Alpine.js 3, Lucide Blade icons, Plus Jakarta Sans via `@fontsource`
- **Legacy compatibility:** Bootstrap 4, jQuery and a small set of admin JavaScript libraries are still bundled for admin screens that are being migrated page by page
- **Maps:** Leaflet with OpenStreetMap tiles (no Google Maps API key)
- **Local mail:** Mailpit exposed under the existing `mailhog` service name
- **Tests:** PHPUnit 11 feature tests using database transactions

There is no SPA rewrite. Interactivity uses Alpine and small Vite-bundled modules; Livewire is only appropriate for genuinely stateful flows if one is added later.

## Requirements

- PHP 8.2+ with common extensions (pdo_mysql, mbstring, xml, curl, zip, gd/intl as needed)
- Composer 2
- Node.js 18+ and npm
- MySQL 8 or a compatible database
- Redis (optional, depending on queue/cache configuration)
- OpenSSL CLI (tests generate ephemeral Passport keys)

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure `DB_*`, `APP_URL`, mail, payment, and notification values in `.env`. Never commit real credentials or generated Passport keys.

For a fresh local install, generate Passport keys:

```bash
php artisan passport:install
```

Then prepare the database and assets:

```bash
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan serve
```

The repository also contains installer assets under `installer/`. Installer and license-check logic must remain intact and must not be bypassed.

## Docker / Sail

`docker-compose.yml` provides app, MySQL, Redis, and mail services. The `mailhog` service name is retained for compatibility, but the image is Mailpit; existing `MAIL_HOST=mailhog` values keep working.

## Build commands

```bash
npm run dev       # Vite dev server with HMR
npm run build     # production build into public/build
npm run watch     # rebuild on file changes
```

Current Vite inputs are listed in `vite.config.js`. Public and admin layouts include the generated assets with `@vite`; do not reintroduce Laravel Mix `mix()` calls.

## Tests

```bash
php artisan test
# or
vendor/bin/phpunit
```

Useful focused runs:

```bash
php artisan test --filter=Phase6PerformanceTest
php artisan test --filter=CheckoutAndOrderTest
php artisan test --filter=ScannerApiTest
```

Tests cover registration/login, public event pages, checkout and order creation, coupon/tax calculation, QR generation, scanner API flows, admin shells/reports, PWA assets, and Phase 6 performance/SEO regressions.

## API contracts

The following route families are used by deployed mobile/client apps and are treated as frozen contracts:

- `/api/user/...`
- `/api/organization/...`
- `/api/scanner/...`

Do not rename routes, change request/response shapes, change Passport auth behavior, or alter scanner validation semantics without explicit approval. Existing Passport tokens must keep working.

## SEO

- `config/brand.php` defines `canonical_url`, `twitter_handle`, and `currency` defaults; set `BRAND_CANONICAL_URL` in production.
- Public pages emit canonical, Open Graph, and Twitter meta tags from `resources/views/components/seo/meta.blade.php`.
- Event detail pages emit schema.org `Event` JSON-LD from `resources/views/components/seo/event-json-ld.blade.php`.
- `/sitemap.xml` is additive and lists public events, categories, blogs, and core pages.
- `artesaos/seotools` remains installed and its existing controller calls are unchanged.

## Style guide

In local/dev environments, visit `/style-guide`. The route is registered in `routes/dev.php` and loaded only when the application is not in production.

## Adding a Blade component

1. Create `resources/views/components/example.blade.php`.
2. Render it as `<x-example />` or `<x-example :prop="$value">...</x-example>`.
3. Declare defaults with `@props([...])` when the component accepts parameters.
4. Add a demo to `resources/views/style-guide-samples.blade.php`.
5. If it needs JavaScript, add an input to `vite.config.js` and include it with `@vite` in the relevant layout/view.
6. Run `npm run build` and `php artisan test`.

Use Tailwind design tokens from `tailwind.config.js`; do not hard-code new brand colours in individual views.

## Project structure

- `app/` — controllers, models, providers, support helpers, mail
- `routes/` — web, frontend, API, and dev-only route definitions
- `resources/views/` — Blade layouts, components, and page templates
- `resources/js/`, `resources/css/`, `resources/sass/` — Vite source assets
- `database/` — migrations, factories, seeders
- `config/` — application and package configuration
- `public/build/` — generated Vite output (ignored by Git)
- `installer/` — installer/license assets that must be preserved

## Security and payments

- Keep CSRF protection, validation, and authorization checks intact.
- Payment SDK scripts (PayPal, Razorpay, Flutterwave, Stripe) are provider-hosted by design and must not be removed.
- Twilio remains optional and off unless explicitly configured.
- Never commit `.env` secrets, payment keys, OAuth keys, or production data.
- Known API authorization/secret-redaction findings from Phase 6 are documented in `docs/audit.md` and require an explicit product decision before changing response shapes or auth behavior.
