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

For local Paystack checkout on Windows, use `powershell -ExecutionPolicy Bypass -File scripts\serve-with-paystack.ps1` instead of `php artisan serve`. It applies the bundled CA certificate only to that Laravel process, avoiding a machine-wide `php.ini` change. See [`docs/paystack-local.md`](docs/paystack-local.md) for webhook setup; no Paystack dashboard callback URL is needed.

The repository also contains installer assets under `installer/`. Installer and license-check logic must remain intact and must not be bypassed.

## Docker / Sail

`docker-compose.yml` provides app, MySQL, Redis, and mail services. The `mailhog` service name is retained for compatibility, but the image is Mailpit; existing `MAIL_HOST=mailhog` values keep working.

## Scanner apps

Two free options use the existing `/api/scanner/...` contract:

- `/scanner-app` is an installable browser PWA using `html5-qrcode` and OpenStreetMap-free static assets. Camera access requires HTTPS or `localhost`; manual ticket-code entry works on plain LAN HTTP.
- [`mobile/`](mobile/README.md) is an Expo Go app using `expo-camera`, with no EAS account or paid build required. Its sign-in screen has an **API server** field; enter your computer's LAN address, for example `http://192.168.1.10:8000`, and save it after sign-in. Use a scanner account created under Admin/Organization → Scanner.

For Expo Go on a phone, start Laravel with `php artisan serve --host 0.0.0.0 --port=8000`. `localhost` and `127.0.0.1` refer to the phone itself and will not reach your computer. Keep both devices on the same Wi-Fi and allow port 8000 through the firewall. No API route, payload or Passport behaviour was changed for either scanner.

## Event pricing and online links

Event prices are set on **tickets**, not on the event record. The admin event wizard includes a **Tickets & pricing** step; saving with `Create & set ticket prices` or `Update & manage tickets` opens the existing ticket manager, where each paid ticket has its own `price` field.

Online events require an `http://` or `https://` joining link in the admin event form. The link is shown on public event detail and ticket-selection pages and is hidden from API model serialization.

## Production email with Brevo free tier

Brevo's free tier can be used through Laravel's built-in SMTP transport; no package or code change is required. Create a Brevo account, verify the sender address/domain, generate an SMTP key, then set these values in the production `.env` (never commit them):

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp-brevo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-brevo-login-email
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_FROM_ADDRESS=verified-sender@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Use port `465` with `MAIL_ENCRYPTION=ssl` only if your Brevo account is configured for it. The free tier has provider limits and sending rules; review Brevo's current plan terms before relying on it for production volume. Local Mailpit configuration is unchanged, and no paid email service is required for development or tests.

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
- `/api/user/setting` keeps its response keys for mobile clients, but map/OneSignal/payment secrets are returned as `null`; only public keys and feature flags are populated.
- Ownership checks apply to customer orders/reviews, organizer events/tickets/taxes/coupons/orders, staff payment pages, and scanner-assigned events. Unauthorized requests receive `403`.
- Named login throttles limit each login endpoint to 5 attempts per minute per `email|ip`; excess attempts receive `429`.
- Checkout totals, taxes, coupon discounts, quantities, and Stripe amounts are recalculated on the server; client-supplied monetary fields are not trusted.
- Payment SDK scripts (PayPal, Razorpay, Flutterwave, Stripe) are provider-hosted by design and must not be removed. Public gateway callbacks stay reachable, but they do not mark an order paid without verified gateway confirmation.
- Twilio remains optional and off unless explicitly configured.
- Never commit `.env` secrets, payment keys, OAuth keys, or production data.
- The approved follow-up hardening and its behavior changes are documented in `docs/audit.md`. License-check and installer logic must remain intact and unchanged.
