# Context

What this project is, how it's put together, and where things live. Read this
first when picking the work back up.

## What this is

A Laravel rebuild of the React/Vite site in `../new` (the "Void Layer" theme):
a full content-managed site with lead capture, a blog, admin-editable policy
pages, a dynamic homepage/about/services CMS, site-wide settings, per-page
SEO, role-based admin accounts, a media library, redirects, optional
reCAPTCHA, and optional TOTP two-factor auth for admin logins — all editable
from `/admin` without touching code.

## Stack

- Laravel 12, PHP 8.2, Blade views (no frontend build step — no Vite, no npm)
- MySQL/MariaDB via XAMPP: host `127.0.0.1`, port `3307`, database `dbvoid`
- Theme's original jQuery/GSAP/Bootstrap/Swiper vendor JS, loaded as plain
  `<script>` tags in `resources/views/layouts/app.blade.php`, same order as
  the React app's `loadLegacyVendor.ts`
- Admin UI: dark sidebar + light content area, dependency-free
  (`admin.css`, `admin-forms.css`) — never touches the public site's CSS
- Zero new composer dependencies added for any feature in this project,
  including 2FA (hand-rolled RFC 6238 TOTP — see `app/Support/Totp.php`)

## Structure

```
app/
  Http/Controllers/           SiteController, BlogController, PageController,
                               ContactController, SitemapController
  Http/Controllers/Admin/     AuthController (login + 2FA challenge),
                               DashboardController, LeadController,
                               PostController, CategoryController,
                               PageController, TestimonialController,
                               FaqController, FeatureCardController,
                               TechBadgeController, StatController,
                               MediaController, UserController,
                               TwoFactorController, RedirectController,
                               SettingController, SeoController
  Models/                     Lead, Post, Category, Page, Setting, SeoMeta,
                               Testimonial, Faq, FeatureCard, TechBadge, Stat,
                               Media, Redirect, User
  Support/SiteConfig.php      read-side facade over Setting, shared as `$site`
  Support/Totp.php            RFC 6238 TOTP — see "Two-factor auth" below
  Rules/Recaptcha.php         server-side reCAPTCHA v2 verification rule
  Http/Middleware/
    SecurityHeaders.php       CSP + security headers on every response
    EnsureAdmin.php           'admin.only' route middleware alias

config/site.php               defaults + admin field definitions: settings
                               groups, seo_pages registry — single source of
                               truth for what /admin/settings and /admin/seo render

resources/views/
  layouts/app.blade.php       public site shell (head, vendor scripts, tracking)
  partials/seo.blade.php      central meta tag renderer — see "SEO" below
  partials/tracking-{head,body}.blade.php   GA/Meta Pixel/custom code, admin-configured
  partials/{header,offcanvas,support-cta}.blade.php
  pages/                      home, services, about, contact, blog/index,
                               blog/show, page (generic content page)
  sections/                   one partial per original React section component;
                               testimonial/faq/feature-highlights/about-values/
                               services-list/about-stats now query their own
                               data from the CMS models (see "Content model")
  admin/                      admin layout (sidebar nav) + one folder per
                               resource; security.blade.php and
                               two-factor-challenge.blade.php for 2FA

public/assets/
  css/custom.css               hero headline + contact page + recaptcha styles
  css/blog.css                 blog grid, post detail, policy page, .prose
  css/admin.css                 admin chrome: sidebar (dark) + content (light)
  css/admin-forms.css           admin form controls, dashboard, media grid, 2FA UI

database/migrations/           see git log for exact order; roughly: leads,
                               settings, pages, categories, posts, seo_meta,
                               testimonials, faqs, feature_cards, tech_badges,
                               stats, users.role, media, redirects,
                               users.two_factor_*
database/seeders/
  AdminUserSeeder.php           creates/updates the admin login from .env
  ContentSeeder.php              settings defaults, 2 locked policy pages, 3 categories
  HomepageContentSeeder.php      moves the original hardcoded homepage copy
                                 (testimonials, FAQs, tech badges, stats,
                                 feature cards) into the database

tests/Unit/TotpTest.php         TOTP verified against official RFC 6238 vectors
tests/Feature/                  BlogTest, ContentTest, HomepageContentTest,
                               MediaLibraryTest, UserRolesTest,
                               RedirectsAndIntegrationsTest, RecaptchaTest,
                               TwoFactorAuthTest — 83 tests total, run against
                               in-memory SQLite, never touch `dbvoid`
```

## Routing model

`routes/web.php`, in this order:

1. Named public routes (`/`, `/services`, `/about`, `/contact`, `/blog`, …)
2. `/admin/*`:
   - `guest` middleware: login, the 2FA challenge (`/admin/2fa`)
   - `auth` middleware: everything else, further split —
     - plain `auth`: leads, blog, categories, pages, homepage content
       (testimonials/faqs/feature-cards/tech-badges/stats), media library,
       security (2FA self-service) — anything an **editor** can touch
     - `auth` + `admin.only`: settings, SEO, users, redirects — anything
       that can break the whole site or grant further access
3. **Catch-all last**: `Route::get('/{page}', [PageController::class, 'show'])`.
   Takes a raw string, *not* an implicit `Page` binding — `PageController::show`
   looks up the `Page` itself, and if none matches, falls through to the
   `redirects` table before finally 404ing. Registered last so a page slug
   (or redirect) can never shadow a real route. Covered by
   `test_page_slug_cannot_shadow_a_real_route` and the redirect tests in
   `RedirectsAndIntegrationsTest`.

Admin resource routes bind by **ID** (`{post:id}`, `{page:id}`,
`{category:id}`), not slug — editing a post's slug must not break its own
edit URL mid-request. Public routes bind by **slug** (`{post:slug}` etc.) for
clean URLs. Mixing these up was a real bug caught during testing — see
`memory.md`.

## Content model

- **Lead**: contact form submissions. `budget` is validated against
  `Lead::BUDGETS` (allowlist), not free text. If reCAPTCHA is configured
  (see "Integrations"), `g-recaptcha-response` is also required and verified
  server-side before a lead is created.
- **Post**: `status` (draft/published) + `published_at`. `scopePublished()` /
  `isLive()` require *both* published status *and* a `published_at` that has
  arrived, so scheduling a future post just works.
- **Category**: posts `nullOnDelete` — deleting a category never deletes its
  posts, they just become uncategorised.
- **Page**: generic admin-authored page. `is_locked` pages (privacy policy,
  terms) can be edited but not deleted, and their slug input is disabled in
  the form — see the disabled-input gotcha in `memory.md`.
- **Testimonial / Faq / TechBadge / Stat**: simple sortable content blocks
  (`is_active`, `sort_order`). Their section partials query
  `Model::active()->get()` directly and render nothing (not an empty state,
  just nothing) when there's no active content — see
  `test_sections_render_nothing_when_empty_rather_than_erroring`.
- **FeatureCard**: one table, `group` column (`services` | `about_values`)
  distinguishes the Services page "What We Build" cards from the About page
  "How We Work" cards — same shape, one admin screen with a group tab instead
  of two near-identical CRUD screens. **Design trade-off**: the original
  hand-tuned mixed grid (3 normal-width cards + 2 wide cards on Services) was
  replaced with a uniform 3-column grid, since the card count is now
  admin-controlled and unpredictable — an asymmetric layout assumes an exact
  count.
- **Media**: standalone upload library (`/admin/media`) — files aren't tied
  to a specific post/page, just uploaded and referenced by copying the URL.
  No picker integration into other image fields yet (each still has its own
  direct upload) — that'd be the natural next step.
- **Redirect**: `from_path` normalized to a leading-slash, no-trailing-slash
  form (`Redirect::normalize()`) so `/old`, `old`, `/old/` all match one row.
  Only fires for URLs that don't match a real route/page — see routing model
  above. Tracks a `hits` counter.
- **Setting**: flat key-value store, `Setting::all_settings()` cached
  forever, busted on any write. Exposed everywhere as `$site` via
  `SiteConfig` (bound in `AppServiceProvider`). Field list lives in
  `config/site.php` under `groups` (includes an `integrations` group — GA ID,
  Meta Pixel ID, reCAPTCHA keys, custom head/body code) — add a key there and
  it appears on `/admin/settings` automatically, no controller change needed.
- **SeoMeta**: per-fixed-page meta override, keyed by a string (`home`,
  `services`, `blog.index`, …) registered in `config/site.seo_pages`. Blog
  posts and `Page` rows carry their own `meta_title`/`meta_description`
  columns instead.
- **User**: `role` (`admin` | `editor`, see `User::ROLES`). Admins reach
  everything; editors are blocked (403, via `EnsureAdmin` middleware) from
  Settings, SEO, Users, and Redirects, and those links are hidden from their
  sidebar entirely (`admin/layout.blade.php` checks `auth()->user()->isAdmin()`
  before adding the `Site` nav group). `UserController` refuses to delete or
  demote the *last* remaining admin — see `UserRolesTest`.

## SEO

Everything routes through `resources/views/partials/seo.blade.php`, included
once in the layout `<head>`. Controllers pass a `$seo` array (`key`, `title`,
`description`, `image`, `type`, `published_time`, …); the partial merges in
`SeoMeta::overridesFor($seo['key'])` (admin edits always win), falls back to
`$site->site_description` / the site name, and emits title, canonical, OG,
Twitter card, and JSON-LD.

To make a new fixed page's meta admin-editable: add an entry to
`config/site.seo_pages`, pass `'key' => '...'` in that controller's `$seo`
array. Nothing else needed.

## Integrations (Settings > Integrations)

All optional, all off until an admin fills in the relevant field:

- **Google Analytics** (`google_analytics_id`) / **Meta Pixel**
  (`meta_pixel_id`): rendered by `partials/tracking-head.blade.php`, included
  once in the public layout only (never in `/admin`).
- **reCAPTCHA v2** (`recaptcha_site_key` / `recaptcha_secret_key`): once both
  are set, the contact form renders the checkbox widget and
  `ContactController::store` requires + verifies `g-recaptcha-response` via
  `App\Rules\Recaptcha` (calls Google's siteverify API — mocked in tests with
  `Http::fake()`, see `RecaptchaTest`).
- **Custom head/body code** (`custom_head_code` / `custom_body_code`): raw
  HTML/JS, rendered unescaped. Same trust model as blog/page content — only
  an authenticated admin can set it.

`app/Http/Middleware/SecurityHeaders.php`'s CSP unconditionally allowlists
Google Tag Manager/Analytics, Meta Pixel, and Google reCAPTCHA domains (script-
src, connect-src, frame-src, img-src) so these work the instant they're
configured — the middleware doesn't need to know whether they're actually on.

## Two-factor auth

Self-service, per-account, at `/admin/security` — any role can enable it for
their own login, independent of admin/editor permissions.

- `app/Support/Totp.php`: hand-rolled RFC 6238 TOTP (HMAC-SHA1, 30s period, 6
  digits) plus its own base32 encode/decode. **Verified against the official
  RFC 6238 Appendix B test vectors**, not just self-consistency — see
  `tests/Unit/TotpTest.php`. No QR code generation (would need an image
  library or a third-party API call that would see the secret in plaintext);
  setup shows a manual entry key instead, which every authenticator app accepts.
- Login flow (`AuthController`): `Auth::validate()` checks the password
  *without* logging in. If the account has 2FA
  (`User::hasTwoFactorEnabled()` — a secret AND a confirmed timestamp, so a
  secret sitting mid-setup never gates login), the user id goes into
  `session('2fa.user.id')` and the request redirects to `/admin/2fa`; only a
  correct TOTP code or an unused recovery code there calls `Auth::login()`.
  There is no intermediate state where the request is "half logged in."
- 8 single-use recovery codes are issued when 2FA is confirmed (and can be
  regenerated), for the lost-device case. Shown exactly once via a flashed
  session value, never stored in plaintext anywhere retrievable afterward
  (`two_factor_recovery_codes` is `encrypted:array` cast, and a used code is
  removed from the array immediately).
- Disabling requires re-entering the current password (`current_password`
  validation rule).
- `TwoFactorAuthTest` covers the full loop: setup → wrong-code rejection →
  confirm → password-required login → correct/wrong TOTP → recovery code
  (and its single-use property) → disable → the secret never appearing in
  `$user->toArray()`.

## Security headers

`app/Http/Middleware/SecurityHeaders.php`, registered globally in
`bootstrap/app.php`, on every response (public and admin): `X-Frame-Options`,
`X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, a CSP
(`'unsafe-inline'` allowed for script/style — the theme has inline
`style="background-image:..."` and a few inline `<script>` blocks; a
nonce-based strict CSP would need those extracted first), and
`Strict-Transport-Security` sent only once `$request->secure()` is true.

## Local environment

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=dbvoid
DB_USERNAME=root
DB_PASSWORD=
```

Admin login: `admin@voidlayer.in` / `password` (from `ADMIN_EMAIL` /
`ADMIN_PASSWORD` in `.env`, applied by `AdminUserSeeder`; role is always
`admin` for this seeded account).

Setup from scratch:
```
php artisan migrate
php artisan storage:link
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=ContentSeeder
php artisan db:seed --class=HomepageContentSeeder
```

Tests run against in-memory SQLite (`phpunit.xml`), never touch `dbvoid`:
```
php artisan test
```

## Known non-issues (checked, not bugs)

- `php artisan db:show` throws on this MariaDB instance
  (`performance_schema.session_status` missing) — that command probes a
  MySQL-only table. Real queries work fine; don't use `db:show` to sanity
  check the connection, use a real query instead.
- On this machine, `curl -F` (multipart) from Git Bash silently fails
  (`000`) against `php artisan serve` for file uploads specifically — not a
  server bug. Image upload is verified instead via `Storage::fake()` +
  `UploadedFile::fake()` in the test suite, which exercises the real
  controller code without going through curl.

## Not done / deliberately out of scope

- No rich WYSIWYG editor — `admin/partials/editor.blade.php` is a plain
  textarea with a toolbar that wraps the selection in HTML tags. Dependency-
  free on purpose.
- No image resizing/optimization on upload; no picker integration between
  the Media library and other image fields (each still uploads directly).
- No RSS feed for the blog.
- Contact form has no outbound email sending yet (`MAIL_MAILER=log` by
  default) — submissions land in `leads` and the admin dashboard, but no
  notification email fires. `Redirect`-level tracking for renamed blog post
  slugs doesn't exist either — the redirect manager only covers the generic
  page catch-all, not `/blog/{slug}`.
