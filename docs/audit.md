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

API contracts, route names and auth behaviour were **not** changed. The duplicate inline `api.php` registration in `RouteServiceProvider` was removed during Phase 1 and re-verified; no API route names are duplicated.

---

## 4. Blade layouts, partials and branding

124 Blade files.

| Layout | Files using it | Notes |
|---|---|---|
| `resources/views/master.blade.php` | 64 admin views + `auth/login` | Includes `admin.layout.{header,sidebar,footer,breadcrumbs}`; contains the license-status content gate (lines 56–104). **Do not alter licensing markup without approval.** |
| `resources/views/frontend/master.blade.php` | 11 public views | Includes `frontend.layout.{search,header,footer}`; payment SDK + Google Maps bootstrap. |
| `resources/views/frontend/auth/app.blade.php` | 6 auth/profile views | Standalone auth layout. |
| `resources/views/layouts/app.blade.php` | 6 Laravel-default auth views + `home` | Only consumer of the old Mix-built `js/app.js` / `css/app.css`; migrated to `@vite` in Phase 1. |
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

---

## 9. Phase 1 addendum — foundation upgrade results (2026-11-19)

### Final versions

| Item | Final value |
|---|---|
| Laravel | `12.73.0` |
| PHP requirement | `^8.2` (local CLI 8.5.8) |
| PHPUnit | `11.5.58` |
| Passport | `12.4.3` (unchanged auth behaviour/token compatibility) |
| Spatie Permission | `6.25.0` |
| DataTables | `12.7.2` |
| Collision | `8.9.6` |
| OneSignal | `2.5.0` (existing app config keys still work) |
| Rave / Flutterwave | `dev-master` (`d3b11a6`) — see risk below |
| Paystack | `devrabiul/laravel-paystack v1.0` — MIT fork using the original `Unicodeveloper\Paystack` namespace and facade |
| Vite / Laravel plugin | `6.4.6` / `1.3.0` |
| Sail runtime | PHP `8.4` (`sail-8.4/app`) |

Laravel was upgraded one major at a time and committed separately: `6d9acd5` (9), `d26a17b` (10), `4b956e6` (11), `0287a58` (12).

### Package changes

Removed: `fideloper/proxy`, `fruitcake/laravel-cors`, `laravel-mix`, `vue`, `vue-template-compiler`, `cross-env`, `resolve-url-loader`, `sass-loader`, and the unmaintained `unicodeveloper/laravel-paystack`.

Added/replaced: Laravel-native trusted-proxy and CORS middleware, `laravel-vite-plugin`, `vite`, `devrabiul/laravel-paystack`, and updated versions of OneSignal, DataTables, Collision and PHPUnit.

Frontend migration:
- `webpack.mix.js` and `ExampleComponent.vue` removed; Vue 2 is gone.
- `resources/js/app.js` / `bootstrap.js` converted to ES modules so Vite actually bundles jQuery, Bootstrap, Popper, Lodash and axios.
- Sass `@import '~bootstrap/scss/bootstrap'` changed to the Vite-compatible path.
- `resources/views/layouts/app.blade.php` now uses `@vite([...])`; `/register` renders HTTP 200 with hashed Vite asset URLs.
- `public/build/` added to `.gitignore` and generated by `npm run build`.
- Old untracked `public/js/app.js`, `public/css/app.css` and `public/mix-manifest.json` are left in place but are no longer referenced.

Backend migration:
- Duplicate API route group removed from `app/Providers/RouteServiceProvider.php`; API route names verified to have no duplicates.
- `Event` and `Ticket` `$dates` converted to `$casts` (Laravel 10+).
- Trusted-proxy constants updated (Laravel 9+).
- License/installer logic and payment logic were not changed.

### Verification

```powershell
composer install --dry-run --no-interaction
php artisan --version                 # Laravel Framework 12.73.0
php artisan route:list --path=api --json
composer update --no-blocking --no-interaction   # final advisories: none
npm install --no-audit --no-fund
npm run build
vendor\bin\phpunit --testdox          # OK (20 tests, 88 assertions)
```

`php artisan serve` + GET `/register` returned HTTP 200 and included `/build/assets/app-*.css` and `/build/assets/app-*.js`.

### Risks and manual checks

1. **Rave / Flutterwave uses `dev-master`.** No tagged release supports Laravel 11/12. The lock file pins commit `d3b11a6`, but this should be revisited if an upstream tagged release appears.
2. **Paystack fork.** `devrabiul/laravel-paystack` keeps the same namespace/facade and config keys, and `getAuthorizationUrl()` / `getPaymentData()` are unchanged. Real Paystack checkout still needs a manual smoke test with test keys (the automated suite uses `LOCAL` payments).
3. **Local `ext-sodium`.** Passport 12 requires it; it was enabled in the local `php.ini`. Production must have `ext-sodium` installed.
4. **npm install scripts.** npm 12 blocked post-install scripts for `esbuild` and `@parcel/watcher`. `npm run build` works, but `npm run watch` may need `npm install-scripts approve esbuild @parcel/watcher` on this machine.
5. **Sass warnings.** Bootstrap 4 SCSS emits many deprecation warnings under Sass 1.108; they do not affect the build output.
6. **Pre-existing uncommitted work.** The original 28 tracked-file modifications (including payment-callback changes) are still uncommitted and were not staged with the upgrade commits.
7. **CDN assets remain** in the current Blade layouts. They are untouched in Phase 1 and will be removed page-by-page in Phase 3/4.

Licensing was not touched, flagged as required by the hard constraints.

---

## 10. Phase 2 addendum — design system (2026-11-19)

### Stack and tokens

| Item | Value |
|---|---|
| Tailwind CSS | `3.4.19` (PostCSS + Autoprefixer, Vite pipeline) |
| Alpine.js | `3.17.5` (started from `resources/js/app.js`) |
| Icons | `technikermathe/blade-lucide-icons ^3.208` (Composer, no CDN) |
| Type | Self-hosted Plus Jakarta Sans via `@fontsource/plus-jakarta-sans` |
| Utility prefix | `tw-`, so Bootstrap utilities are not overridden on unmigrated pages |
| Dark mode | Class strategy; because of the `tw-` prefix the class is `.tw-dark` |

Design tokens live in `tailwind.config.js`: brand gold `primary`, deep-ink `ink`, restrained teal `accent`/`success`, neutral greys, `warning`, `danger`, Plus Jakarta Sans type scale, small type scale extension, `card`/`stub`/`pill` radii, card/focus shadows, and extended spacing. Preflight is disabled so Bootstrap remains intact. The only decorative motif is the ticket-stub divider in `resources/css/app.css`.

### Files and components

New reusable components in `resources/views/components/`: `button`, `input`, `select`, `textarea`, `modal`, `dropdown`, `tabs`, `badge`, `card`, `event-card`, `ticket-card`, `alert`, `toast`, `skeleton`, `pagination`, `empty-state`, `theme-toggle`, `theme-init`, plus the internal `style-guide-samples` partial.

A local/dev-only gallery is available at `/style-guide` (`routes/dev.php`, conditionally required by `RouteServiceProvider`). It renders every component in light and dark containers. Existing pages and API routes are unchanged in Phase 2.

### Verification

```powershell
npm run build                         # OK; self-hosted fonts emitted under public/build/assets
vendor\bin\phpunit --testdox          # OK (20 tests, 88 assertions)
php artisan route:list --path=style-guide --json
php artisan route:list --path=api --json
```

GET `/style-guide` through `php artisan serve` returned HTTP 200 with Vite asset URLs, Alpine `x-data` and both preview wrappers. Compiled CSS contains `.tw-dark` selectors for `dark:tw-*` utilities; API route names remain duplicate-free.

### Risks and manual checks

1. Bootstrap 4 and its Sass deprecation warnings remain because public/admin pages have not been migrated yet.
2. The new components are not yet used by public pages; Phase 3 will adopt them page by page.
3. npm 12 still blocks install scripts for `esbuild` and `@parcel/watcher`; `npm run build` works, but `npm run watch` may need approval.
4. Visual checks at 360px, 768px and 1280px should be repeated during Phase 3 when the components are used in real layouts.
5. License/installer logic, payment logic, API contracts and Passport auth were not touched.

---

## 11. PHP 8.2 compatibility fix (2026-11-19)

### Root cause

The local CLI used for Composer was PHP 8.5.8, so dependency resolution silently selected packages requiring PHP 8.4.1+. The web runtime is PHP 8.2.12, causing the fatal error in `vendor/composer/platform_check.php`. The blocking packages were Symfony 8.1 components and `lcobucci/clock` 3.7.

### Fix

- Added `config.platform.php: 8.2.12` to `composer.json` so dependency resolution always targets the real runtime.
- Downgraded `lcobucci/clock` to `3.3.1`.
- Downgraded `symfony/clock` to `v7.4.21`, `symfony/css-selector` to `v7.4.22`, `symfony/event-dispatcher` to `v7.4.17`, `symfony/string` to `v7.4.22`, `symfony/translation` to `v7.4.23`, and `symfony/yaml` to `v7.4.23`.
- Regenerated Composer autoload files; `platform_check.php` now requires PHP `>= 8.2.0`.

### Verification

```powershell
composer why-not php 8.2.12              # no conflicts
C:\xampp\php\php.exe -v                  # PHP 8.2.12
C:\xampp\php\php.exe artisan --version   # Laravel Framework 12.73.0
C:\xampp\php\php.exe vendor\bin\phpunit --testdox  # OK (20 tests, 88 assertions)
npm run build                            # OK
```

`php artisan serve` using the XAMPP PHP 8.2.12 binary returned HTTP 200 for both `/` and `/style-guide`, with no platform-check exception.

No API contracts, payment logic, Passport auth or licensing logic were changed.

---

## 12. Phase 3 progress — public shell and home (2026-11-19)

This is the first Phase 3 commit set: the shared public shell and the root home page. Other public pages still use their existing legacy layouts.

### What changed

- Added `config/brand.php` for public-facing defaults (name, headline, about text, location, social links). Hard-coded "Odogwu Fest" copy on the home page now comes from config; admin-editable name/logo/favicon/email/copyright still come from `Setting`.
- Added `resources/views/layouts/public.blade.php` with Vite assets, theme init, skip link, flash messages and the public header/footer.
- Added `x-public-header`, `x-public-footer`, `x-public-nav-links` and `x-flash-messages` components. The header is mobile-first with an Alpine menu, accessible labels and a persisted theme toggle.
- Rebuilt `resources/views/web/home.blade.php`: editorial hero, category quick search (existing `category` field on `/all-events`), feature strip, upcoming-event cards, category grid, organizer highlights, three-step explainer and organizer CTA. Empty states are handled when no events/categories exist.
- Added `PublicViewServiceProvider` and `PublicHomeComposer` so the home page gets events, categories, price bounds, category counts and organizers without editing the pre-existing modified `FrontendController` or route files. Aggregate append accessors are disabled on listing models to avoid per-card queries.
- Extended `tests/Feature/EventPagesTest.php` to assert the new home shell renders the brand and a published event, plus a graceful empty state.

### Verification

```powershell
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe vendor\bin\phpunit --testdox   # OK (21 tests, 92 assertions)
npm run build                                      # OK
```

`php artisan serve` on PHP 8.2.12 returned HTTP 200 for `/` and `/style-guide`. The home response contains Vite asset URLs, the hero, header navigation, event/empty sections and the footer, with no PHP exception. Headless Chrome rendered the page at 360px, 768px and 1280px; screenshots were kept outside the repository for manual visual review.

### Risks and manual checks

1. Only `/` uses the new shell. `/index`, `/all-events`, event detail, checkout and user pages still carry Bootstrap/jQuery/CDN code and will be migrated in later Phase 3 commit sets.
2. The theme toggle uses the Phase 2 `.tw-dark` class and persisted `ds-theme` value. It was smoke-tested in the style guide, but a visual dark-mode pass on the real home page is still recommended.
3. Event/category images fall back to bundled local placeholder images when a record has no upload.
4. `__('...')` translation strings were kept wherever the legacy pages used them; untranslated strings fall back to English.
5. API contracts, Passport auth, payment logic and license/installer logic were not touched.

---
## 13. Phase 3 progress — event listings (2026-11-19)

This commit set migrates the event guide, event listing, category listing and event-type listing pages to the new public shell. Controller methods, route names/paths and GET field names were not changed.

### What changed

- Rebuilt `resources/views/web/landing.blade.php` for `/index`: editorial hero, local placeholder image, category/type quick search that submits to the existing `/all-events` route, and a responsive upcoming-events grid.
- Rebuilt `resources/views/frontend/events.blade.php` for `/all-events`, `/events-category/{id}/{name}` and `/event-type/{type}`: server-side filters are untouched; Alpine adds instant page-local search, category/type/date filtering, sorting, skeleton loading, result counts and an empty state. The existing `category`, `type`, `duration` and `date` GET fields are preserved, as are active-filter chips.
- Rebuilt `resources/views/frontend/allCategory.blade.php` for `/all-category`: category cards with lazy images, explicit image dimensions and accessible links.
- Added `app/View/Composers/EventListingComposer.php` and registered it for `web.landing` and `frontend.events` so those views get active categories and minimum ticket prices without editing the pre-existing modified `FrontendController`.
- Added `x-public-event-card` and `x-favorite-button`. The favourite control calls the existing `/add-favorite/{id}/{type}` JSON endpoint with `fetch` instead of legacy jQuery/SweetAlert and keeps the same response handling. `resources/views/web/home.blade.php` now reuses the shared event card.
- Replaced remaining Bootstrap/jQuery/Font Awesome markup on these listing pages with the Phase 2 design system and Lucide icons. Bootstrap remains bundled for pages not yet migrated.

### Verification

```powershell
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe vendor\bin\phpunit --testdox   # OK (24 tests, 113 assertions)
npm run build                                      # OK; existing Bootstrap Sass deprecation warnings only
```

Tests now cover `/index`, `/all-events?category=...`, `/all-events?type=...`, `/events-category/{id}/{name}`, `/event-type/{type}` and `/all-category`, including exclusion of non-matching events.

HTTP smoke checks on PHP 8.2.12 returned HTTP 200 for `/`, `/style-guide`, `/index`, `/all-events`, `/all-events?type=online`, `/all-category` and `/event-type/all`. Headless Chrome rendered `/all-events` at 360px, 768px and 1280px, plus `/index` at 360px and `/all-category` at 1280px; screenshots were saved outside the repository for manual review.

### Risks and manual checks

1. Alpine filtering is page-local: changing a filter updates the current page instantly, while the **Apply** button still performs the existing server-side GET reload.
2. The existing controller still fatal-errors on an invalid `category` query value because it calls `Category::find($id)->name`; this pre-existing behaviour was deliberately not changed.
3. The new favourite button depends on the existing authenticated `/add-favorite/{id}/{type}` endpoint and its `success` JSON field. It was tested structurally, not through a real logged-in browser session.
4. Event detail, checkout, user area, auth, blog and contact pages still use the legacy Bootstrap/jQuery layout and will be migrated in later Phase 3 commit sets.
5. Dark mode and the 360px/768px/1280px layouts should be reviewed visually from the captured screenshots.
6. API contracts, Passport auth, payment logic and license/installer logic were not touched.

---

## 14. Phase 3 progress — event detail and ticket selection (2026-11-19)

### What changed

- Rebuilt `resources/views/frontend/eventDetail.blade.php` for `/event/{id}/{name}` on the shared public layout: editorial hero, date/time/venue and type chips, favourite and share controls, Alpine gallery thumbnails, escaped description, tags, organizer card, reviews with star ratings, venue card with an OpenStreetMap link, static FAQ details, and a sticky ticket sidebar with paid/free tabs.
- Rebuilt `resources/views/web/tickets.blade.php` for `/eventdetail/{id}/{name}`: real paid/free tickets from the existing controller payload, event summary card, and working login/checkout links. All hard-coded placeholder ticket cards and prices were removed.
- Added `x-event-ticket-offer`, used by both pages. It preserves the original `checkout/{ticketId}` link for signed-in attendees and the original `user/login` redirect for guests, keeps ticket numbers visible, and shows sold-out state without offering checkout.
- Fixed a latent authenticated-user bug in `x-favorite-button`: an Alpine expression was being passed to a Blade component as PHP, causing a 500. The active state is now rendered server-side while the button itself stays Alpine-driven.
- The legacy detail view hid a single paid ticket because of a `count == 1` check. The redesign shows all tickets supplied by the controller; no checkout or order logic changed.
- Extended `tests/Feature/EventPagesTest.php` to cover detail content, gallery/organizer/reviews/map/FAQ sections, paid and free tickets, guest login redirect, authenticated checkout link, sold-out behaviour, and removal of placeholder pricing.

### Verification

```powershell
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe vendor\bin\phpunit --testdox   # OK (25 tests, 133 assertions)
npm run build                                      # OK; existing Bootstrap Sass deprecation warnings only
```

Feature tests exercise both routes through the Laravel HTTP kernel with fixture events and tickets. The local database has no published events, so HTTP smoke checks for these two data-dependent routes were not meaningful; the earlier listing-page smoke checks still pass and the Vite build succeeds.

### Risks and manual checks

1. The venue card links to OpenStreetMap rather than embedding an interactive map. Leaflet can be added in Phase 4 when map dependencies are reviewed.
2. Share uses the browser Clipboard API; it requires a secure context in real browsers. The link itself is also always selectable.
3. Reviews are displayed exactly as supplied by the controller, which does not filter by `status`; this legacy behaviour was not changed.
4. Event descriptions are escaped and line breaks preserved, so admin-entered HTML is shown as text. This is safer than rendering raw HTML and does not affect stored data.
5. Checkout, payment gateways, Passport auth, API contracts and license/installer logic were not touched.

---

## 15. Phase 3 progress — checkout (2026-11-19)

### What changed

- Rebuilt `resources/views/frontend/checkout.blade.php` on `layouts.public`: three-step progress header, event/ticket details, accessible quantity stepper, payment method cards, sticky order summary with tax lines, coupon field, and secure-checkout messaging.
- Added `resources/js/checkout.js` as a small Vite entry. It recreates the legacy quantity/total recalculation and the PayPal, Razorpay and Stripe selection behaviour without jQuery or `frontend/js/custom.js`. All original hidden field names, radio values, gateway container IDs and the `form_submit` button ID remain unchanged.
- The new script keeps the original USD/EUR minor-unit conversion for the hidden `payment`/`stripe_payment` values, and fixes a latent JavaScript typo in the legacy quantity handler that prevented `stripe_payment` from being refreshed after quantity changes.
- Gateway SDKs are now loaded only when enabled for the ticket/payment settings. PayPal and Razorpay scripts are pushed from the checkout view; Stripe Checkout is still injected when Stripe is selected, matching the legacy flow.
- The coupon field is rendered as a disabled, clearly-labelled control with the original hidden `coupon_id`/`coupon_discount` values and a note that coupons are applied in the mobile app. The legacy web page had no working coupon handler, so no new client-side discount calculation was introduced.
- Extended `tests/Feature/CheckoutAndOrderTest.php` to lock the form action, hidden field names, tax data, free-ticket totals, gateway radio values, SDK loading, and the absence of the legacy Bootstrap/jQuery assets.

### Verification

```powershell
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe vendor\bin\phpunit --testdox   # OK (27 tests, 168 assertions)
node --check resources\js\checkout.js              # OK
npm run build                                      # OK; existing Bootstrap Sass deprecation warnings only
```

A temporary rendered checkout response was served from the local PHP development server and captured with headless Chrome at 360px, 768px and 1280px. Screenshots were written outside the repository for manual review. The preview used fixture data and was removed after capture.

### Risks and manual checks

1. Real PayPal, Razorpay, Stripe and Flutterwave payments still need a browser smoke test with gateway test keys. Automated tests verify field names and SDK loading only.
2. PayPal and Razorpay remain externally hosted SDKs because they are required by those payment providers. They are not core CSS/JS and are loaded only on checkout when enabled.
3. Flutterwave still redirects through the existing `createOrder` → `web/create-payment/{id}` flow; those pre-existing modified payment views were not touched.
4. The coupon field is intentionally not a new discount calculator. Web coupon application was non-functional before this change; the API-based coupon flow used by the mobile app is unchanged and still covered by tests.
5. Server-side order creation, taxes, commission, notifications, redirects, Passport auth, API contracts and license/installer logic were not changed.

---

## 16. Phase 3 progress — user area (2026-11-19)

### What changed

- Rebuilt `resources/views/frontend/profile.blade.php` on `layouts.public`: dark profile hero, avatar with photo upload, inline bio editor, account links, and accessible tabs for saved events, saved blogs and followed organisers. Saved blogs now use the existing `/blog-detail/{id}/{slug}` route instead of a dead `javascript:void(0)` link.
- Rebuilt `resources/views/frontend/userTickets.blade.php`: upcoming/past order list, rich order detail panel, payment toggler, invoice link and a custom review dialog. All legacy hooks are preserved: `.event-data`, `data-order-id`, `viewPayment()`, `#payment-data`, `.open-addReview`, `#reviewModel`, `#order_id`, `#rate-1`…`#rate-5`, `name="rate"` and `name="message"`.
- Rebuilt `resources/views/frontend/user_profile.blade.php` and `resources/views/frontend/auth/changePassword.blade.php` on the public layout while keeping the original actions, field names, `name="login"` form marker and validation messages.
- Added `resources/js/user-area.js` as a small Vite entry. It replaces the jQuery-dependent profile/ticket behaviours with vanilla JS but keeps the original global functions (`addFavorite`, `follow`, `viewPayment`, `addRate`) and element IDs so no controller or route had to change. It also provides local status messages instead of SweetAlert2 for these simple actions.
- Added `tests/Feature/UserAreaTest.php` covering profile rendering, saved items, all account links, ticket detail hooks, both form contracts, inline JSON endpoints (`/add-bio`, `/add-favorite`, `/add-followList`) and review creation.
- Fixed a 360px-only layout issue in the profile page by adding `tw-min-w-0` to grid items and an explicit one-column mobile grid. The remaining wide elements are the intentionally horizontally scrollable tab strip inside the card; document width is now exactly 360px.

### Verification

```powershell
node --check resources\js\user-area.js                       # OK
npm run build                                                # OK; existing Bootstrap Sass deprecation warnings only
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe vendor\bin\phpunit --filter UserAreaTest --testdox  # OK (6 tests, 77 assertions)
C:\xampp\php\php.exe vendor\bin\phpunit --testdox           # OK (33 tests, 245 assertions)
```

Temporary rendered responses were captured outside the repository and measured in headless Chrome at 360px, 768px and 1280px. Document and body scroll widths matched the viewport on all four user-area pages; no unintended horizontal overflow was found. The temporary preview test and HTML files were not added to the commit.

### Risks and manual checks

1. The upload-profile-image, favourite, follow and review endpoints were exercised through their existing controllers, but a browser smoke test with a real account is still useful for the photo preview and modal focus behaviour.
2. The order detail renderer assumes the JSON payload shape from `FrontendController@getOrder`; that controller was read only and left untouched.
3. Legacy `public/frontend/js/custom.js` still exists for pages that have not been migrated; it is no longer loaded by these redesigned views.
4. Pre-existing modified controllers, routes and `resources/views/master.blade.php` were not staged or altered. Passport auth, API contracts, order/payment logic and license/installer logic are unchanged.

---

## 17. Phase 3 progress — authentication pages (2026-11-19)

### What changed

- Rebuilt `resources/views/frontend/auth/login.blade.php`, `register.blade.php`, `resetPassword.blade.php` and `orgRegister.blade.php` on `layouts.public`, replacing the styled-components CSS class soup and Bootstrap/jQuery/CDN shell with the shared design system.
- Preserved every original form action (`/user/login`, `/user/register`, `/user/resetPassword`, `/user/org-register`), the hidden `url` return field, all input names (`name`, `last_name`, `email`, `phone`, `password`, `confirm_password`, `first_name`, `name`, `country`), existing `data-qa` hooks and the partner/organizer links.
- Session flash messages now render through the shared alert component: login/reset errors and reset success messages keep their original text.
- Organizer registration now uses the existing design-system select for countries while retaining the `Currency`-backed options and `old('country')` selection.
- Added `tests/Feature/AuthPagesTest.php` covering all four page contracts plus rendering of server validation errors in the new components.

### Verification

```powershell
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe vendor\bin\phpunit --filter AuthPagesTest --testdox  # OK (5 tests, 67 assertions)
C:\xampp\php\php.exe vendor\bin\phpunit --testdox           # OK (38 tests, 312 assertions)
npm run build                                                # OK; existing Bootstrap Sass deprecation warnings only
```

The four pages were measured in headless Chrome with device-metric emulation at 360px, 768px and 1280px. Document/body scroll widths matched the viewport on every page and no unintended horizontal overflow was found.

### Risks and manual checks

1. Password reset still sends a randomly generated password by email through the existing controller; only the page was restyled.
2. Organizer signup still redirects to the legacy `/login` route after creation, matching the original behaviour.
3. Existing registration/login endpoint tests remain green; a browser smoke test is still useful for real password-reset email delivery under the configured SMTP/Mailpit setup.
4. No controller, route, validation rule, Passport behaviour, API contract, payment logic or license/installer logic was changed.

---

## 18. Phase 3 progress — blogs, organizers and contact (2026-11-19)

### What changed

- Rebuilt `resources/views/frontend/blog.blade.php` and `resources/views/frontend/blogDetail.blade.php` on `layouts.public`. The index now uses responsive article cards with lazy-loaded images, category/date metadata, accessible favourite controls and a graceful empty state; the detail page keeps the original escaped description, category, tags, session flash message and `/blog-detail/{id}/{slug}` links.
- Updated `resources/views/components/favorite-button.blade.php` so `type="blog"` reads `AppUser::favorite_blog` instead of the event favourites column. Existing event usage is unchanged.
- Rebuilt `resources/views/frontend/orgDetail.blade.php` with a profile hero, contact details, follow control, event cards and the existing paginator. The legacy `onclick="follow(id)"` hook is preserved through the new `resources/js/public-social.js` Vite entry.
- Rebuilt `resources/views/frontend/contact.blade.php` on the public layout, preserving the original `action="#"`, `method="post"` and `name`/`email`/`subject`/`message` fields. The Google Maps CDN script is no longer used on this page.
- Added free, self-hosted `leaflet` via npm plus `resources/js/contact-map.js`. It renders an OpenStreetMap tile map from the existing `default_lat`/`default_long` settings, removes the fallback message once initialised and shows a neutral fallback when coordinates are not configured. No API key is required.
- Added `tests/Feature/RemainingPublicPagesTest.php` covering published-only blog listings, blog favourites, blog detail content/flash messages, organizer profile/events/follow hooks and the contact form/map contract without legacy Bootstrap/jQuery/CDN assets.
- `resources/views/frontend/home.blade.php` was left untouched: the only route calling `FrontendController@home` is commented out in `routes/frontend.php`, so the view is currently dead code. It is flagged here rather than removed during this view-migration phase.

### Verification

```powershell
node --check resources\js\public-social.js                  # OK
node --check resources\js\contact-map.js                    # OK
npm run build                                               # OK; Leaflet CSS/JS bundled, existing Bootstrap Sass warnings only
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe vendor\bin\phpunit --filter RemainingPublicPagesTest --testdox # OK (4 tests, 47 assertions)
C:\xampp\php\php.exe vendor\bin\phpunit --testdox           # OK (42 tests, 359 assertions)
```

Temporary fixture-backed renders of all four pages were measured in headless Chrome at 360px, 768px and 1280px. Document and body scroll widths matched the viewport with no unintended horizontal overflow. The contact page was also loaded over HTTP from a temporary static server; Chrome reported an initialised Leaflet container with six OSM tiles and no fallback message. Temporary previews were kept outside the repository and removed from the test suite before commit.

### Risks and manual checks

1. The contact form still has no server-side submission target (`action="#"`) and no validation rules, matching the original page; only the presentation changed.
2. The OSM tile layer requires outbound access to `tile.openstreetmap.org`. The fallback message is shown when coordinates are missing, but offline tile loading will leave an empty map background.
3. The follow button was verified at the HTTP/HTML contract level and through the existing JSON endpoint tests; a browser smoke test with a real attendee account is still useful.
4. `public/build/` is generated output and was not staged. Pre-existing modified controllers/routes/master layout and license/installer logic were not touched. Passport auth, API contracts, order/payment logic and validation behaviour are unchanged.

## 19. Phase 4 addendum — dependency audit and local mail (2026-11-19)

### What changed

- Audited every external URL under `resources/views` and `resources/sass`. The 62 references fall into four groups:
  1. **Intentionally external payment SDKs/hosted checkouts** — Razorpay, PayPal, Stripe and Flutterwave. These are provider-hosted payment flows and were left unchanged.
  2. **Self-hosted replacements already delivered** — the redesigned public pages use Vite-bundled Tailwind/Alpine/Lucide and the contact page uses npm-bundled Leaflet + OpenStreetMap tiles. No API key is required.
  3. **Legacy/admin CDN hubs still present** — Bootstrap, jQuery, Popper, Select2, Summernote, Flatpickr, SweetAlert2, Chart.js, DataTables, moment.js, pdfmake and nicescroll in `resources/views/master.blade.php`, `resources/views/frontend/master.blade.php`, `resources/views/frontend/auth/app.blade.php`, `resources/views/admin/frontpage.blade.php`, `resources/views/admin/order/invoicePrint.blade.php` and `resources/views/cancel.blade.php`. These remain until Phase 5 or until the user approves editing the pre-existing modified master layout.
  4. **Font CDN references** — Google Fonts in `resources/sass/app.scss`, `resources/views/welcome.blade.php`, `resources/views/layouts/app.blade.php`, the three error layouts and the two legacy frontend master layouts.
- Replaced the unmaintained `mailhog/mailhog:latest` image with the free MIT-licensed `axllent/mailpit:latest`. The Compose service is still named `mailhog` and still exposes SMTP `1025` and web UI `8025`, so existing `MAIL_HOST=mailhog` values in real and example `.env` files keep working. Real `.env` values were not touched.

### Verification

```powershell
C:\xampp\php\php.exe vendor\bin\phpunit --testdox           # OK (42 tests, 359 assertions)
npm run build                                               # OK; existing Bootstrap Sass deprecation warnings only
```

### Risks and manual checks

1. Mailpit has a different web UI from Mailhog, but the SMTP/Web ports and Compose service name are unchanged. Run `sail up -d mailhog` (or the equivalent local command) and visit `http://localhost:8025` to confirm the new UI before relying on it for password-reset emails.
2. The remaining legacy/admin CDN dependencies are not core to the redesigned public site. They are deliberately deferred because `resources/views/master.blade.php` is pre-existing user work and the admin panel is scheduled for Phase 5.
3. The external payment SDKs are required by the existing hosted checkout flows and were not replaced with local packages.
4. License/installer logic was not inspected or altered. No API contract, auth behaviour, route, request field, validation rule or order/payment logic changed in this addendum.
