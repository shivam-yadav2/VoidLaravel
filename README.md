# Void Layer — Laravel site

Marketing site (converted from the React/Vite app in `../new`) with a full
content-managed backend: lead capture, a blog, admin-editable policy pages, a
dynamic homepage/about/services CMS, role-based admin accounts, a media
library, marketing integrations, redirects, and optional two-factor auth —
all editable from `/admin` without touching code.

**See also:** [`context.md`](context.md) for how the project is put
together, [`memory.md`](memory.md) for decisions and gotchas hit along the
way, [`DEPLOYMENT.md`](DEPLOYMENT.md) for the production checklist. Read
those before making structural changes or going live.

## Requirements

- PHP 8.2+ with `pdo_mysql`
- XAMPP MySQL/MariaDB
- Composer

## Database

Configured in `.env` to match the local XAMPP instance:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=dbvoid
DB_USERNAME=root
DB_PASSWORD=
```

Set up (or reset) the schema, admin account and default content:

```bash
php artisan migrate
php artisan storage:link
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=ContentSeeder
php artisan db:seed --class=HomepageContentSeeder
```

All three seeders use `firstOrCreate` and are safe to re-run — they never
overwrite an admin's edits.

## Running

```bash
php artisan serve
```

To serve through Apache instead, point the vhost **document root at `public/`**
(never the project root — that would expose `.env`).

## Admin

| | |
|---|---|
| URL | `/admin/login` |
| Email | `admin@voidlayer.in` |
| Password | `password` |

**Change the password before this is reachable from anywhere but localhost.**
Credentials come from `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env`; edit those and
re-run `AdminUserSeeder`.

From `/admin` you can manage:

- **Leads** — contact form submissions: search, status filters, unread
  markers, detail view, delete, CSV export.
- **Blog** — posts with a featured image, category, draft/published status,
  scheduled publishing, reading time, and per-post SEO fields.
- **Categories** — deleting one keeps its posts; they just become
  uncategorised.
- **Pages** — generic content pages. Privacy Policy and Terms & Conditions
  ship pre-seeded and *locked*: editable, not deletable, slug fixed. Add
  more pages freely.
- **Testimonials / FAQs / Feature cards / Tech badges / Stats** — the
  homepage, About, and Services content blocks that used to be hardcoded
  (including fake placeholder testimonial names) are now fully admin-managed.
- **Media library** — standalone image uploads; copy a URL to use anywhere.
- **Team** *(admin only)* — add/edit/remove accounts, each `admin` or
  `editor`. Editors can't reach Settings, SEO, Team, or Redirects. The last
  remaining admin can't be demoted or deleted.
- **Security** *(every account)* — optional TOTP two-factor auth (Google
  Authenticator, Authy, 1Password, …), self-service per account, with
  one-time recovery codes.
- **SEO** *(admin only)* — per-page meta title/description/social
  image/noindex for the fixed pages. Blog posts and content pages carry
  their own meta fields on their own edit screens.
- **Settings** *(admin only)* — contact details, social links, footer copy,
  and Integrations (Google Analytics, Meta Pixel, reCAPTCHA v2, custom
  head/body code) — all optional, off until filled in.
- **Redirects** *(admin only)* — 301/302 redirects for old URLs, with a hit
  counter. Only fires for URLs that don't match a real page or route.

## Structure

```
resources/views/
  layouts/app.blade.php      shared chrome (head, header, offcanvas, footer, vendor JS, tracking)
  partials/seo.blade.php     central meta tag renderer (admin overrides win)
  partials/tracking-{head,body}.blade.php   GA/Meta Pixel/custom code
  partials/                  header, offcanvas, support-cta
  pages/                     home, services, about, contact, blog/index, blog/show, page
  sections/                  one partial per original React section component —
                              several now query their own CMS data (testimonials, FAQs, …)
  admin/                     one folder per resource; layout.blade.php has the sidebar nav

public/assets/                theme CSS/JS/images, copied from the React app
  css/custom.css               hero headline + contact page + recaptcha styles
  css/blog.css                 blog grid, post detail, policy page, .prose styles
  css/admin.css, admin-forms.css   admin UI: dark sidebar, light content area, no CDN

config/site.php               settings defaults + which fields render on
                               /admin/settings and /admin/seo — edit this file
                               to add a new setting or a new SEO-manageable page

app/Support/Totp.php          RFC 6238 TOTP for 2FA (no composer dependency)
app/Rules/Recaptcha.php       server-side reCAPTCHA v2 verification
app/Http/Middleware/SecurityHeaders.php   CSP + security headers, every response
```

Routes are in `routes/web.php`. Public write endpoint: `POST /contact`. The
catch-all `GET /{page}` for admin-managed pages (which also checks the
`redirects` table) is registered **last** so it can never shadow a real route.

## Notes

- The theme's jQuery/GSAP/Bootstrap/Swiper vendor scripts load as plain
  `<script>` tags in the original order — there is **no JS build step**.
- The contact form posts to Laravel with CSRF, server-side validation, a
  honeypot field, and optional reCAPTCHA v2 (once configured in Settings).
  `budget` is validated against `Lead::BUDGETS`, which also renders the pills.
- `POST /contact` is rate limited to 8/min per IP; admin login and the 2FA
  challenge to 6/min each.
- The admin's rich text editor is a dependency-free plain textarea with a
  toolbar (`admin/partials/editor.blade.php`) — no CDN, works offline.
  Content is stored and rendered as raw HTML; safe because only authenticated
  admins can write it. Same trust model applies to the custom head/body code
  and reCAPTCHA/TOTP secrets — all admin-only surfaces.
- `php artisan test` runs the full suite (83 tests, including a TOTP unit
  test verified against the official RFC 6238 vectors) against in-memory
  SQLite — it never touches the real `dbvoid` database.
