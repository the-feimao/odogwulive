# Project Overview

## What this project is

This repository contains a Laravel-based event management and ticketing web application. It provides a public-facing website for browsing events and purchasing tickets, along with authenticated areas for administrators and event organizers to manage platform content and operations. It also exposes API endpoints for end users, organizations, and ticket scanners (e.g. for mobile/client applications).

Based on bundled installer assets (`installer/eventright.sql`, installer scripts), the codebase appears to be derived from an **EventRight**-style event/ticketing platform, with deployment-specific branding/content present in some templates (for example, references to "Odogwu Fest" in frontend views and admin UI text).

## Core capabilities (inferred from codebase)

- **Public website/frontend**
  - Home/landing experience and event discovery
  - Event listing, filtering, category/type views, event details
  - Organizer pages, blogs, contact page
  - User registration/login/reset, profile management, favorites/following, reviews, and ticket history

- **Ticketing & orders**
  - Event tickets, checkout, and order creation
  - Order details/invoices and QR code-related flows
  - Coupons/discounts, taxes, and payment status handling

- **Administration & organization management**
  - Admin/organization dashboards
  - Management for events, tickets, categories, locations, banners, blogs, FAQs, feedback, users/app users, roles/permissions, coupons, taxes, notifications/templates, languages, and settings
  - Scanner/staff management and event check-in related workflows
  - Reporting (customers, orders, revenue, settlements, etc.)

- **APIs**
  - **User API** (`/api/user/...`): authentication, profile, events/categories/search, favorites/following, coupons/taxes, orders, reviews, notifications
  - **Organization API** (`/api/organization/...`): auth/profile, event/ticket/coupon/tax/FAQ management, followers, notifications, media/gallery actions
  - **Scanner API** (`/api/scanner/...`): auth/profile, assigned events, attendee/order lookup, ticket scanning/validation

## Technology stack

### Backend
- **Language:** PHP (`^7.3|^8.0` per `composer.json`)
- **Framework:** Laravel 8 (`laravel/framework` ^8.12)
- **Authentication & authorization:**
  - Laravel session/auth scaffolding (`laravel/ui`)
  - Laravel Passport (`laravel/passport`) for API authentication
  - Spatie Laravel Permission (`spatie/laravel-permission`) for roles/permissions
- **Database/ORM:** Eloquent ORM with Laravel migrations/seeders/factories (MySQL-oriented configuration in `.env.example` and Sail setup)
- **API/utilities & packages (selected):**
  - `yajra/laravel-datatables-oracle` (server-side DataTables)
  - `artesaos/seotools` (SEO metadata)
  - `simplesoftwareio/simple-qrcode` (QR code generation)
  - `tanmuhittin/laravel-google-translate` (translation support)
  - `guzzlehttp/guzzle`, `fideloper/proxy`, `fruitcake/laravel-cors`, etc.
- **Notifications & messaging integrations:** OneSignal (`berkayk/onesignal-laravel`), Twilio SDK (`twilio/sdk`), mail configuration via Laravel Mail
- **Payment integrations (present in dependencies/config/routes/controllers):**
  - Paystack (`unicodeveloper/laravel-paystack`)
  - Flutterwave/Rave (`kingflamez/laravelrave`, Flutterwave-related routes)
  - Stripe (`stripe/stripe-php`)
  - PayPal-related client configuration referenced in admin layout

### Frontend / UI
- **Server-rendered views:** Laravel Blade templates (`resources/views/...`)
- **Asset pipeline:** Laravel Mix 5 with Webpack (`webpack.mix.js`)
  - Compiles `resources/js/app.js` and `resources/sass/app.scss` to `public/js` and `public/css`
- **Frontend libraries (from `package.json` / views):**
  - Bootstrap 4, jQuery, Popper.js, Axios, Lodash
  - Vue.js 2 (scaffolded) and `vue-template-compiler`
  - Sass (`sass`, `sass-loader`)
  - Admin/public UI also uses CDN-loaded assets in templates (e.g. DataTables, Select2, Flatpickr, Summernote, Chart.js, SweetAlert2, Font Awesome)

### Infrastructure / local development
- **Docker / Laravel Sail configuration:** `docker-compose.yml` defines services for the app, MySQL 8, Redis, and Mailhog (with optional Selenium/Memcached entries commented out)
- **Installer assets:** `installer/` directory includes SQL and helper/install scripts
- **Testing:** PHPUnit configuration present (`phpunit.xml`) with feature/unit test scaffolding under `tests/`

## Project structure (high-level)

- `app/` — application code (HTTP controllers/middleware, Eloquent models, providers, mail, etc.)
- `routes/` — route definitions (`web.php`, `frontend.php`, `api.php`, console/channels)
- `resources/` — Blade views, language files, frontend JS/Sass source
- `database/` — migrations, factories, seeders
- `config/` — Laravel and package configuration (app, database, mail, payments, permissions, SEO, OneSignal, etc.)
- `public/` — web root and compiled/static assets
- `installer/` — installation-related scripts and bundled SQL (`eventright.sql`)
- `tests/` — automated test scaffolding
- `docker-compose.yml`, `composer.json`, `package.json`, `webpack.mix.js` — environment, dependency, and build configuration

## Notes

- `README.md` in the repository is the default Laravel README and does not describe this specific product.
- The application includes license/installation-related logic and settings (e.g. license checks in admin views and installer helpers), which may be relevant when setting up or deploying this codebase.
- Some branding/content in views is customized for a specific deployment; core entities and routes indicate a general-purpose event/ticketing platform foundation.