# Phase 0 audit — Odogwu EventRight application

Date: 2026-11-19  
Branch: `modernization/phase-0-audit`  
Scope: baseline audit and safety net before any design/framework migration. No behaviour-changing refactors were made.

---

## 1. Environment and versions

| Item | Version / value |
|---|---|
| Laravel | 8.83.29 (app boots; `php artisan --version` OK) |
| PHP (CLI) | 8.5.8 |
| Composer | 2.10.2 |
| Node / npm | 24.18.0 / 12.0.2 |
| PHPUnit | 9.6.39 |
| Database | MySQL/MariaDB via `DB_HOST=127.0.0.1`, local dev DB `odogwu` (37 tables, full schema present) |
| Web check | `php artisan serve` + GET `/` → **200** (`<title>Odogwu Fest</title>`) |
| Git state at start | Branch `main`; 28 tracked files modified plus untracked `AGENTS.md`, `overview.md`, `vendor.partial.20261119091812/`. These pre-existing changes were left untouched. |

`vendor/` exists. `node_modules/` was absent at the start; `npm install --no-audit --no-fund` completed successfully during the baseline (1,235 packages, with deprecation warnings for Vue 2, Bootstrap 4, Axios 0.19, Popper 1 and old Mix tooling). `public/css` and `public/js` were initially absent; `npm run production` compiled them successfully (`css/app.css` 143 KiB, `js/app.js` 360 KiB). There is **no `npm run build` script** yet — Phase 1 will introduce Vite and the standard `build` script. Build output and `package-lock.json` are currently untracked.

### Compatibility note found during baseline

`config/database.php:62` used `PDO::MYSQL_ATTR_SSL_CA`, which is deprecated on PHP 8.5 and produced a deprecation notice on every test run. It was changed to use `Pdo\Mysql::ATTR_SSL_CA` when available, with a fallback for older PHP. This is a compatibility fix only, not a behaviour change.

---

## 2. Dependencies: current, outdated, abandoned

### Backend (`composer.json` / `composer.lock`)

| Package | Constraint | Status / note |
|---|---|---|
| `laravel/framework` | `^8.12` (installed 8.83.29) | **EOL**; migrate one major at a time (9 → 10 → 11 → 12) in Phase 1. |
| `php` | `^7.3|^8.0` | Must be raised for each Laravel major. Installed PHP is 8.5.8. |
| `laravel/passport` | `^10.1` | Keep (hard constraint). Needs upgrade alongside Laravel; OAuth tables and personal-access client are seeded by `installer/eventright.sql`. |
| `spatie/laravel-permission` | `^3.18` | Very old; upgrade with care (roles `admin`, `organization`, `scanner` are seeded). |
| `laravel/ui` | `^3.1` | Effectively deprecated by Laravel; only default auth scaffolding depends on it. |
| `facade/ignition` (dev) | `^2.5` | Abandoned; replaced by `spatie/laravel-ignition` in newer Laravel. |
| `nunomaduro/collision` (dev) | `^5.0` | Old; upgrade with PHPUnit/Laravel. |
| `fideloper/proxy` | `^4.4` | Deprecated; Laravel 9+ handles trusted proxies natively. |
| `fruitcake/laravel-cors` | `^2.0` | Deprecated; Laravel 9+ has built-in CORS. |
| `yajra/laravel-datatables-oracle` | `^9.15` | Old; upgrade if DataTables remains in admin. |
| `simplesoftwareio/simple-qrcode` | `^4.2` | Abandoned upstream; used only by `OrderController@getQrCode`. Consider maintained BaconQrCode wrapper later. |
| `tanmuhittin/laravel-google-translate` | `^2.0` | Old; check translation use before upgrading. |
| `stripe/stripe-php` | `^7.67` | Very old; upgrade (payment behaviour must stay identical). |
| `twilio/sdk` | `^6.16` | Old; keep optional/off by default (hard constraint). |
| `kingflamez/laravelrave` | `^3.0` | Flutterwave/Rave integration; verify maintenance before upgrade. |
| `unicodeveloper/laravel-paystack` | `^1.0` | Paystack integration. |
| `artesaos/seotools` | `^0.20.1` | Keep working; used heavily in controllers. |
| `berkayk/onesignal-laravel` | `^1.0` | Optional push notifications. |
| `laravel/sail` (dev) | `^0.0.5` | Extremely old; update with PHP/Laravel. |

**Payment integrations present:** Paystack, Flutterwave/Rave, Stripe, PayPal/Razorpay client-side, plus admin settlement payouts. No paid dependency was added during Phase 0.

### Frontend (`package.json`)

- Laravel Mix 5 / Webpack, Sass, jQuery 3, Bootstrap 4, Popper 1, Axios 0.19, Lodash, Vue 2 + `vue-template-compiler`.
- **Vue 2 is EOL and effectively unused** — `resources/js/app.js` mounts a `ExampleComponent` that no Blade view renders.
- No Vite, no Tailwind, no Alpine, no Livewire yet.

---

## 3. Routes by area

`php artisan route:list --json` reports **304 routes**: ~182 web/admin, ~32 frontend, ~90 API.

### 3.1 Frontend web routes (`routes/frontend.php`, all `web` middleware)

Public:

| Method + URI | Name | Handler |
|---|---|---|
| GET `/` | – | `FrontendController@main` |
| GET `/index` | `frontend.index` | `FrontendController@viewIndex` |
| GET `/event-tickets`, GET `/eventdetail/{id}/{name}` | `frontend.tickets`, `event.details` | `FrontendController@viewEventTickets` |
| GET/POST `/user/register`, `/user/login`, `/user/resetPassword`, `/user/org-register` | – | `FrontendController@register` / `@userRegister` / `@login` / `@userLogin` / … |
| GET/POST `/all-events`, `/events-category/{id}/{name}`, `/event-type/{type}` | – | listing/filter actions |
| GET `/event/{id}/{name}`, `/organization/{id}/{name}` | – | detail pages |
| GET `/all-category`, `/all-blogs`, `/blog-detail/{id}/{name}`, `/contact`, `/report-event` | – | content pages |

Authenticated (`appuser` guard): `/checkout/{id}`, `POST /createOrder`, `/user/profile`, `/my-tickets`, `/getOrder/{id}`, favorites/following/reviews, profile upload/password, `/web/create-payment/{id}`, `POST /web/payment/{id}`, `/web/rave/callback/{id}`.

### 3.2 Admin / organization web routes (`routes/web.php`)

- Default auth: `/login`, `/register`, `/logout`, `/password/*`.
- License/installer endpoints: `POST /admin/login`, `/saveEnvData`, `/saveAdminData`, `/license-setting`, `/save-license-setting` — **licensing logic; untouched (see §8)**.
- Dashboards: `/admin/home`, `/organization/home`.
- CRUD resources: `roles`, `users`, `app-user`, `events`, `tickets` (custom routes), `tax`, `faq`, `banner`, `blog`, `feedback`, `coupon`, `category`, `notification-template`, `language`.
- Orders/reports: `/orders*`, `/order-invoice*`, `/admin-report/*`, `/organization-report/*`, `/getStatistics/{month}`, `/view-settlement/{id}`, `/pay-to-organization`, `/get-code/{code}`.
- Scanner/staff management: `/scanner`, `/scanner/create`, `/block-scanner/{id}`, etc.
- Settings: `/admin-setting`, `/save-*`, `/change-language/{lang}`.

### 3.3 APIs (`routes/api.php`, 90 routes, no route names)

- **`/api/user/...`** — public: `login`, `register`, `forget-password`, `setting`, `events`, `event-detail/{id}`, `event-tickets/{id}`, `search-*`, `all-coupon`, `category`. Authenticated (`auth:userApi`): profile, favorites/following, notifications, `check-code`, `order-tax/{event_id}`, `create-order`, orders/tickets, reviews.
- **`/api/organization/...`** — public: `login`, `register`, `forget-password`, `setting`. Authenticated (`auth:api`): profile, events/tickets CRUD, taxes, coupons, FAQs, scanners, followers, notifications, galleries, orders.
- **`/api/scanner/...`** — public: `login`, `forget-password`, `setting`. Authenticated (`auth:api`): `events`, `event-detail/{id}`, `event-users/{id}`, `profile`, `scan-ticket/{id}/{event_id}`, profile/password updates.

API contracts, route names and auth behaviour were **not** changed. Note: `api.php` is registered twice in `RouteServiceProvider` (`mapApiRoutes()` at line 44 and an inline group at lines 48–51); Laravel de-duplicates them today, but this should be cleaned up during Phase 1 and re-verified.

---

## 4. Blade layouts, partials and branding

124 Blade files.

| Layout | Files using it | Notes |
|---|---|---|
| `resources/views/master.blade.php` | 64 admin views + `auth/login` | Includes `admin.layout.{header,sidebar,footer,breadcrumbs}`; contains the license-status content gate (lines 56–104). **Do not alter licensing markup without approval.** |
| `resources/views/frontend/master.blade.php` | 11 public views | Includes `frontend.layout.{search,header,footer}`; payment SDK + Google Maps bootstrap. |
| `resources/views/frontend/auth/app.blade.php` | 6 auth/profile views | Standalone auth layout. |
| `resources/views/layouts/app.blade.php` | 6 Laravel-default auth views + `home` | Only consumer of Mix-built `js/app.js` / `css/app.css` (currently missing). |
| `resources/views/main.blade.php` | `web/home.blade.php`, `web/tickets.blade.php` | Legacy Revolution Slider landing layout. |
| `resources/views/app.blade.php` | `web/landing.blade.php` | Legacy landing layout #2. |
| `errors/*` | 10 error views | Extend `errors::minimal` or standalone error layouts. |

Standalone views (26) include layouts, partials and payment pages (`createPayment.blade.php`, `transction_verify.blade.php`, `cancel.blade.php`, `admin/frontpage.blade.php`, `admin/order/invoicePrint.blade.php`).

### Hard-coded "Odogwu Fest" branding

68 matches across 8 files: `app.blade.php` (title, meta, email), `main.blade.php` (nav/socials), `master.blade.php:78` (license alert text — **licensing, leave until approved**), `admin/frontpage.blade.php`, `admin/layout/footer.blade.php`, `web/footer.blade.php`, `web/home.blade.php`, `web/landing.blade.php`. Also `frontend/profile.blade.php:6` uses an external Wallpaper Cave background.

Phase 3 must move this text/colours into config/settings so the app is reusable.

---

## 5. Assets, CDNs and library usage

### Build pipeline

- `webpack.mix.js:14-15` compiles `resources/js/app.js` and `resources/sass/app.scss`.
- **`npm run build` does not exist**; scripts are `development`, `watch`, `hot`, `production`. Node modules are not installed.
- No `@vite` and no `mix()` calls anywhere in views; layouts use raw `<link>`/`<script>` and `asset()`/`url()`.
- `public/css` and `public/js` do not exist; `layouts/app.blade.php:13,20` and `transction_verify.blade.php:123-124` reference missing files.
- Large checked-in vendor trees: `public/admin/{css,js}`, `public/frontend/{css,js,fonts}`, `public/web/*` (97 files, Bootstrap 4, Revolution Slider, Font Awesome).

### CDN dependencies in Blade

| Library | Where | Purpose |
|---|---|---|
| Bootstrap 4.3.1 CSS/JS | `master.blade.php`, `frontend/master.blade.php`, `frontend/auth/app.blade.php`, `admin/order/invoicePrint.blade.php`, `admin/frontpage.blade.php` | Core layout/grid; duplicated with local copies in `public/frontend` and `public/web`. |
| jQuery 3.3.1 (CDN) | `master.blade.php`, `invoicePrint.blade.php`; local `frontend/js/jquery.min.js` in frontend/auth layouts | Plugin init, license alert, AJAX helpers. |
| Vue 2 | npm only (`resources/js/app.js:9,22,30-32`) | **Unused** — no Blade view uses `<example-component>`; it mounts on empty `#app` wrappers. Safe to remove in Phase 1/2 once Mix is gone. |
| DataTables (bundle + Select) | `master.blade.php`, `invoicePrint.blade.php`; init in `public/admin/js/myCustom.js` | Admin/report tables, export/print. |
| Select2 4.0.7 | three master layouts; ~40 forms | Enhanced selects. |
| Flatpickr | admin + frontend master | Date/time pickers. |
| Summernote 0.8.18 | admin master | Blog/mail-template rich text. |
| Chart.js 2.9.3 | admin master | Dashboard charts. |
| SweetAlert2 10 | admin, frontend, auth layouts | License alert + user feedback. |
| Font Awesome 5.7.2 (CDN + local) | all layouts | Icons. |
| Google Maps JS API | `frontend/master.blade.php:84` | Contact map + address autocomplete (`public/frontend/js/map.js`). **Replace with Leaflet + OSM in Phase 3.** |
| Google Fonts (Poppins/Nunito) | frontend/auth/default layouts, Sass, error views | Typography. |
| PayPal / Razorpay / Stripe SDKs, Flutterwave hosted checkout | payment layouts/views | Payment flows — keep behaviour intact. |
| Other: Popper, niceScroll, Moment, pdfmake, jqVectorMap, normalize.css, remote 404 SVG, demo form endpoint | admin master / `cancel.blade.php` / legacy landing | Misc UI helpers. |

### Inline code

19 inline `<style>` blocks (notably `errors/illustrated-layout.blade.php:14-456`, ~443 lines) and 6 inline `<script>` blocks, including the license gate in `master.blade.php:70-90`.

---

## 6. Test safety net and baseline

### Test database

The project has **no migrations for application tables** (only users, password_resets, failed_jobs and permission tables). The full schema and seed data live in `installer/eventright.sql` (37 tables, including Passport OAuth tables and seeded roles/templates/settings).

To keep tests isolated from the development database:

- `tests/seed-test-db.php` rebuilds a dedicated `odogwu_test` MySQL database from `installer/eventright.sql`. It refuses to target anything other than that `_test` database and reads credentials from `.env` without modifying them.
- `phpunit.xml` now sets `DB_CONNECTION=mysql` and `DB_DATABASE=odogwu_test`.
- All feature tests use `DatabaseTransactions`, so each test rolls back.
- Passport test keys are generated on the fly in the OS temp directory by `tests/TestCase.php`; no keys are stored in the repo.

Setup command (run once, or whenever the installer SQL changes):

```powershell
php tests/seed-test-db.php
vendor\bin\phpunit --testdox
```

### Tests added

| File | Coverage |
|---|---|
| `tests/Feature/UserAuthenticationTest.php` | Web registration (success/duplicate), web login success/failure, user API login token + device token |
| `tests/Feature/EventPagesTest.php` | Landing/index, event listing, event detail, ticket-selection page |
| `tests/Feature/CheckoutAndOrderTest.php` | Checkout tax display, order creation with children/taxes/commission, API coupon validation, API event taxes |
| `tests/Feature/TicketQrTest.php` | Admin QR ticket print; organization cannot print another organizer's ticket |
| `tests/Feature/ScannerApiTest.php` | Scanner login, non-scanner rejection, assigned events, scan validation/completion, wrong-event rejection |
| `tests/Concerns/CreatesFixtures.php` | Shared model fixtures (organizer, admin, scanner, customer, event, ticket, tax, coupon, order, child) |

Payment gateways were not contacted: the checkout test uses `payment_type=LOCAL`, which avoids Stripe/Flutterwave calls. No real keys or payment data were used.

### Baseline result (recorded 2026-11-19)

```
OK (20 tests, 88 assertions)
```

Before the new tests the suite contained only 2 placeholder tests (`OK (2 tests, 2 assertions)`). The app also served `/` with HTTP 200 under `php artisan serve`. Frontend build baseline: `npm install --no-audit --no-fund` then `npm run production` → compiled successfully (Sass deprecation warnings from Bootstrap 4 only).

### Baseline findings worth carrying forward

1. **Checkout trusts client input** — `payment`, `tax`, `coupon_discount` and `tax_data` are posted from the browser and not recalculated server-side (`FrontendController@createOrder:571-641`). Tests assert current behaviour; do not change it without approval.
2. **Scanner endpoint authorization gap** — `ScannerApiController@scanTicket` checks event/ticket match but not scanner assignment or role; `eventDetail`/`eventUsers` have no assignment check. Flag for the Phase 6 security review.
3. **Web `getOrder/{id}`** has no ownership check.
4. `eventDetail.blade.php:69` shows "No Paid Tickets found" when exactly **one** paid ticket exists (`== 1` instead of `== 0`); the test works around this by creating two tickets. Existing behaviour preserved.
5. `/get-code/{code}` takes an `order_child.id`, while the QR itself encodes `ticket_number`; scanner validation expects the ticket number.
6. `Event` append accessors query `Setting::find(1)` on serialization, so setting id 1 is effectively required everywhere.
7. Hard-coded branding and CDNs listed in §4–§5.

---

## 7. Licensing (flagged, not touched)

Per the hard constraint, no license/installer logic was changed:

- `LicenseController@adminLogin` calls `LicenseBoxAPI::verify_license()`.
- `LicenseController@saveEnvData` / `@saveAdminData` write environment/license values.
- `resources/views/master.blade.php:65-94` hides admin content when `general_settng.license_status != 1` and shows a "deactivate" alert.
- `resources/views/admin/frontpage.blade.php` contains installer/activation UI.

The QR test temporarily sets `license_status = 1` **inside a rolled-back transaction** so the admin layout renders; this does not bypass or alter the license check in application code.

---

## 8. Phase 0 verdict

**Done:** audit written, 20 critical-flow tests passing, isolated test DB documented, app verified to boot.

**Risk before Phase 1:** the pre-existing uncommitted changes in 28 tracked files (security fixes in controllers/payment callbacks) are still uncommitted and were not reviewed line-by-line beyond the payment-callback diff. They carry over on the new branch. They should be committed or explicitly acknowledged before framework upgrades so upgrade diffs stay readable.

**Next phase plan (Phase 1):** upgrade Laravel one major at a time (8 → 9 → 10 → 11 → 12), raising PHP requirements and replacing abandoned packages, then replace Mix with Vite, update Sail/PHP config, and run the 20-test suite after each major version with a commit at each step.
