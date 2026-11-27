# AGENTS.md — Modernization Instructions

You are modernizing an existing Laravel event management and ticketing application (EventRight-derived, with "Odogwu Fest" branding in some views). Read `overview.md` first for the project's structure and stack.

## Goal

Give the application a modern, polished stylish design and feel without making it look like AI slop while keeping Laravel as the backend and API. Existing behaviour (checkout, orders, tickets, scanning, APIs) must keep working throughout.

## Hard constraints

1. **Free only.** Use only free, open-source tools or services with a usable free tier. Never add a paid package, paid SaaS, or anything that needs a credit card. If a paid option seems necessary, stop and ask.
2. **Laravel stays** as backend and API.
3. **Do not break API contracts.** `/api/user/...`, `/api/organization/...` and `/api/scanner/...` are used by mobile/client apps. Do not rename routes, change request/response shapes, or change auth behaviour unless I explicitly approve it.
4. **Keep Passport** for API auth unless I approve a move to Sanctum. Existing mobile tokens would break.
5. **Licensing:** the code contains license-check and installer logic. Do not remove, bypass or alter it without asking me. Flag it in your report instead.
6. **Never commit secrets.** Do not touch real `.env` values, payment keys or production data.
7. **No destructive actions** (dropping tables, deleting migrations, force-pushing, rewriting history) without approval.

## Working rules

- Before each phase, state your plan in a few lines. After each phase, report what changed, what you tested, and anything risky.
- If something is ambiguous, choose the safest option, state your assumption, and continue. Ask me only for decisions that are costly to reverse.
- Do not do a big-bang rewrite. Migrate page by page and keep the app runnable after every commit.
- Run the test suite and a build (`composer install`, `npm run build`) before finishing each phase. Fix what you broke.

## Decisions already made (change only if I say so)

- **Frontend approach:** keep Blade, add **Tailwind CSS + Alpine.js**, and use **Livewire** only where real interactivity is needed (filters, search, cart/checkout steps). No SPA rewrite.
- **Build tool:** **Vite** replaces Laravel Mix.
- **Admin panel:** keep the current admin working through Phases 1–4. Phase 5 evaluates **Filament** (free) as a replacement.

---

## Phase 0 — Audit and safety net

1. Create a Git branch and confirm the app runs locally (use the existing Docker/Sail setup if it works).
2. Produce `docs/audit.md` covering: Laravel/PHP versions, outdated or abandoned packages, routes by area (web, frontend, api), Blade layouts and partials, CSS/JS assets and CDN dependencies, and where Bootstrap, jQuery and Vue 2 are actually used.
3. Add feature tests for the critical flows. At minimum:
   - user registration and login
   - event listing and event detail pages
   - checkout and order creation (with payment gateways mocked or faked)
   - coupon and tax calculation
   - ticket QR generation
   - scanner API: login, assigned events, ticket validation
4. Run the tests and record the baseline. Do not proceed if the critical flows cannot be covered; tell me why.

**Done when:** audit written, tests exist and pass, baseline recorded.

## Phase 1 — Upgrade the foundation

1. Upgrade Laravel one major version at a time (8 → 9 → 10 → 11 → 12), following the official upgrade guide for each. Rector (free) may be used to automate changes.
2. Raise the PHP requirement to what the target Laravel version needs. Replace abandoned packages with maintained free equivalents. Keep Passport, Spatie Permission and the payment packages working, upgrading them as needed.
3. Run tests after **each** major version. Commit after each.
4. Replace Laravel Mix with **Vite** (`vite.config.js`, `@vite` directives in layouts). Remove Mix config and unused Vue 2 scaffolding once nothing depends on it.
5. Update Docker/Sail config to match the new PHP version.

**Done when:** app runs on the latest supported Laravel, Vite builds assets, all Phase 0 tests pass.

## Phase 2 — Design system

1. Install **Tailwind CSS** and **Alpine.js** via Vite. Do not remove Bootstrap yet; scope new styles so both can coexist during migration.
2. Define design tokens once in the Tailwind config: colour palette (primary, accent, neutrals, success/warning/danger), border radius, shadows, spacing, type scale.
3. Typography: a free Google Font such as **Inter** or **Plus Jakarta Sans**. Self-host the font files if practical, for performance and privacy.
4. Icons: **Lucide** or **Heroicons** (replace Font Awesome gradually).
5. Build reusable **Blade components** in `resources/views/components/`: button, input, select, textarea, modal, dropdown, tabs, badge, card, event-card, ticket-card, alert/toast, skeleton loader, pagination, empty state.
6. Support **dark mode** from the start (class-based, with a toggle that remembers the choice).
7. Accessibility baseline: visible focus states, sufficient contrast (WCAG AA), semantic HTML, keyboard-operable components, labels on all form fields.
8. Create a simple internal style guide page (route available only in local/dev) that shows every component.

**Done when:** components exist, the style guide renders them in light and dark mode, and nothing in the existing site is broken.

## Phase 3 — Public site redesign

Redesign in this order, one page group per commit set:

1. Main layout: header/nav, footer, mobile menu, flash messages.
2. Home/landing: hero, featured and upcoming events, categories, organizer highlights, clear calls to action.
3. Event listing: search, filters (category, location, date, price), sort, skeleton loading, empty states. Use Livewire or Alpine for instant filtering.
4. Event detail: strong visual header, date/time/venue, ticket selection, organizer info, share buttons, reviews, FAQ, map.
5. Checkout and ticket purchase: clear step flow, order summary, coupon field, tax display, payment method selection, success and failure states. Keep all payment logic unchanged; only restyle.
6. User area: profile, my tickets, order details/invoice, favorites, following, reviews.
7. Auth pages: login, register, password reset.
8. Blogs, organizer pages, contact page.

Rules for this phase:
- Mobile-first. Test at 360px, 768px and 1280px widths.
- Preserve every existing route name, form field name and validation behaviour.
- Replace remaining Bootstrap/jQuery usage on each page as you redesign it, then remove the unused libraries only when nothing references them.
- Replace Google Maps with **Leaflet + OpenStreetMap** where maps are used (free, no API key).
- Optimize images: WebP where possible, lazy loading, explicit width/height to avoid layout shift.
- Branding: keep the existing brand identity unless I say otherwise. Move hard-coded "Odogwu Fest" text and colours into settings or config so the app is reusable.

**Done when:** all public pages use the new design system, checkout still works end to end, tests pass.

## Phase 4 — Free integration upgrades

Do only what is clearly beneficial and free. For each, explain the change before making it.

| Area | Free option |
|---|---|
| Rich text editor | Tiptap or Trix (replacing Summernote) |
| Charts | ApexCharts or Chart.js (self-hosted via npm, not CDN) |
| Date picker | Flatpickr (self-hosted via npm) |
| Select/search inputs | Tom Select (replacing Select2/jQuery dependency) |
| Alerts/toasts | Native Alpine toast component (replacing SweetAlert2 where simple) |
| Email (local) | Mailpit instead of Mailhog |
| Email (production) | Host SMTP, or a free tier such as Brevo |
| Scanner | Installable **PWA** using `html5-qrcode`, calling the existing `/api/scanner/...` endpoints |
| PWA | Web manifest, service worker for the public site and scanner, offline fallback page |

- Move all CDN-loaded libraries to npm packages bundled by Vite so the app does not depend on third-party CDNs.
- Twilio (paid per SMS) must stay optional and off by default. Do not remove existing support, just ensure the app works without it.
- Paystack, Flutterwave and Stripe have per-transaction fees but no setup cost; leave them in place.

## Phase 5 — Admin panel

1. Restyle the existing admin with the new design system (nav, tables, forms, dashboard cards) **or** propose replacing it with **Filament** (free). Present a short comparison and recommendation before doing the replacement.
2. If Filament is approved: install it, add Filament Shield (free) for Spatie roles and permissions, and migrate one resource at a time starting with the least risky (categories, locations, banners, FAQs). Keep old admin routes working until each resource is verified.
3. Keep every existing permission and role working. Do not weaken access control.
4. Preserve reports (customers, orders, revenue, settlements) and scanner/staff management.

**Done when:** admin is consistent with the new design (or fully on Filament), permissions behave as before, tests pass.

## Phase 6 — Polish and hardening

1. Performance: eager-load relations to remove N+1 queries, cache heavy queries, run `npm run build` and check bundle size, run Lighthouse (free) on key pages and aim for 90+.
2. SEO: keep `artesaos/seotools` working, add Open Graph and Twitter meta for event pages, sitemap, structured data (schema.org `Event`) for events.
3. Security review: validation on all forms, CSRF intact, rate limiting on login and API auth, authorization checks on organization and scanner endpoints, no debug mode in production config.
4. Remove dead code, unused packages, unused CSS/JS and unused Blade files. List everything removed in the report.
5. Update `README.md` so it describes this project: what it is, requirements, install steps, build commands, test commands, and how to add a new Blade component.

**Done when:** Lighthouse and accessibility checks are acceptable, README is accurate, all tests pass.

---

## Definition of done for the whole project

- App runs on a currently supported Laravel and PHP version.
- All critical-flow tests pass, and API behaviour is unchanged.
- Public site and admin share one consistent, responsive design with dark mode.
- No paid dependencies were introduced.
- No remaining CDN dependencies for core functionality.
- `docs/audit.md`, the style guide page and an updated `README.md` exist.

## Report format (end of each phase)

1. **What changed** (short bullet list)
2. **How I tested it** (commands run, results)
3. **Risks or things to check manually**
4. **Decisions I need from you** (if any)
5. **Next phase plan**
