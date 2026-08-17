# Memory

Append-only log of decisions, gotchas and fixes made on this project. Newest
entries at the top. For "what exists and how it fits together," see
`context.md` instead — this file is about *why*, and about traps already hit
so they don't get hit twice.

---

## 2026-08-18 — Full admin expansion: dynamic content, RBAC, media, integrations, 2FA

User asked for everything from a 4-part feature menu (dynamic homepage
content, admin workflow tools, marketing integrations, extra security) to be
built in one pass. Landed as four sections, each fully tested before moving
to the next — test count went 24 → 32 → 44 → 54 → 60 → 83 across the session.
Full list is in `context.md`; this entry is the *why* and the *what almost
went wrong*.

**Deliberate scope cuts** (so future-me doesn't "fix" these as oversights):
- The "Why Choose Us" homepage section was **not** made dynamic. It has a
  genuinely bespoke irregular grid (mixed `col-xl-4`/`col-xl-3`/`col-xl-5`/
  `col-xl-6` spans, alternating background-image cards) hand-tuned for
  exactly 6 items. Making it admin-manageable would mean either freezing the
  layout at 6 slots forever or redesigning it to tolerate an arbitrary count
  — neither was worth it next to the FeatureCard/TechBadge/Stat/Testimonial/
  Faq work, which covers the sections that were *actually* fake demo content
  (fake testimonial names) rather than hand-crafted marketing copy.
- FeatureCard (services + about_values cards) **did** get flattened from the
  original mixed grid to a uniform 3-column layout, for the opposite reason:
  once card count is admin-controlled, an asymmetric layout that assumes
  exactly 5 items breaks the moment someone adds a 6th.
- Media library has no picker integration into other image fields (posts,
  testimonials, etc. still upload directly). Flagged as the natural next
  step, not built — scope was already large.
- Redirects only cover the generic page catch-all, not `/blog/{slug}`. If an
  editor renames a post's slug, old links to it just 404 — no auto-redirect.

**Real bugs caught during this pass** (each reproduced by a failing test
before being fixed, not spotted by inspection):

- **`main.js` runs `$(".count").counterUp()` on every element with class
  `.count`, sitewide, and expects pure numeric text.** First draft of the
  dynamic about-stats partial wrapped `$stat->value` (admin free text —
  "50+", "1 day", "100%") in `<span class="count">`, which would have made
  the counterUp plugin try to animate "1 day" as a number. Caught by
  actually reading `main.js` before shipping, not by a test — but a test
  (`test_stat_value_is_not_rendered_with_counterup_class`) was added
  afterward specifically so this can't regress silently. Lesson: when
  wiring admin-free-text into a section that used to have a hardcoded
  number, check what legacy JS that number's CSS class triggers before
  reusing the class.
- **Admin routes for `Post`/`Page`/`Category` were still binding by slug**,
  same bug shape as the original conversion (see the entry below) — this
  time it broke locked-page editing with a 500 ("Undefined array key
  'slug'") because a `@disabled` slug input on locked pages doesn't submit
  at all, so `$data['slug'] ?: ...` saw a missing key, not an empty string.
  Fixed in `PageController`/`PostController`/`CategoryController` by using
  `$data['slug'] ?? null` before the `?:` fallback in all three — the same
  fix had already been applied once for `Page` before this session; it
  needed applying to the other two as well since they have the same
  optional-slug-field shape.
- **Test-only bug, not app**: an early `TwoFactorAuthTest` helper used
  `actingAs($user)` to enable 2FA, then tried to simulate a fresh guest
  login afterward in the same test method. `actingAs()` leaves the guard
  authenticated for the rest of the test, so `guest`-middleware routes
  (`/admin/login`, `/admin/2fa`) silently redirected away instead of
  running the controller — symptom was a redirect to `http://localhost`
  instead of the dashboard, with no obvious connection to the real cause.
  Fixed by calling `$this->post('/admin/logout')` at the end of the helper.
  Worth remembering for any future test that needs to go
  authenticated-setup → then real HTTP login flow in one method.

**Design decisions:**

- **RBAC**: two roles only (`admin`, `editor`), no granular permissions
  system. Editors get everything except Settings/SEO/Users/Redirects — the
  four things that can break the whole site or grant further access. Chosen
  over a permissions-table approach because the site has exactly two kinds
  of people who'll ever use it (the business owner, and whoever they hire to
  write blog posts) — a granular ACL would be solving a problem that doesn't
  exist yet. Nav links for admin-only sections are hidden from editors
  entirely (not shown-then-403'd) — a visible link to a page you can't open
  reads as a bug, not a permission boundary.
- **2FA has no composer dependency.** RFC 6238 TOTP is genuinely small
  (~80 lines including base32), and pulling in a package for it would be the
  first non-Laravel-core dependency added to the *admin* in this whole
  project — inconsistent with the "no CDN, no external dependency" rule
  already established for the rich-text editor. Verified against official
  RFC 6238 Appendix B vectors specifically because hand-rolling crypto-
  adjacent code without testing against a known-correct reference is how
  subtle timing/encoding bugs survive into production; self-consistency
  tests alone (verify what you just signed) would not have caught e.g. a
  base32 padding bug that happened to round-trip correctly with itself.
- **Login uses `Auth::validate()`, not `Auth::attempt()` + logout**, when
  2FA is enabled. `Auth::validate()` checks the password without ever
  establishing a session, so there's no login-then-logout dance and no
  window, even within a single request, where the account is "authenticated
  but 2FA-pending." Simpler to reason about than the alternative, and it's
  what Laravel Fortify does internally for the same reason.
- **Recovery codes are `encrypted:array`, not hashed.** Deliberately
  followed Laravel Fortify's precedent here: hashing would make the
  single-use "remove from array" logic need to hash-then-compare every
  stored code on every login attempt, and offers no real benefit over
  encryption-at-rest for an 8-item array that's shown to the user exactly
  once and only ever compared, never displayed again.
- **CSP allowlists GA/Meta Pixel/reCAPTCHA domains unconditionally**, not
  only when those integrations are actually configured. Computing the CSP
  per-request based on Settings would mean either a DB read on every single
  response (defeats the point of a lightweight security-headers middleware)
  or a cache-invalidation path just for header content. The domains are
  well-known, low-risk, and static; allowlisting them always means Settings
  > Integrations fields work the instant an admin fills them in, no
  redeploy or cache-clear required.

---

## 2026-08-17 — Security review + hardening pass

User asked directly whether the site was secure. Rather than answer from
general Laravel knowledge, actually checked this codebase: grepped for raw
SQL (`DB::raw`/`whereRaw`), unescaped Blade output (`{!! !!}`), missing
`@csrf` on state-changing forms, mass-assignment guards, upload validation
rules, and ran `composer audit`. Findings and what changed as a result:

**Confirmed already safe** (no code changes needed): no raw SQL anywhere —
Eloquent throughout, so no SQL injection surface. Every `{!! !!}` in the
codebase is either hardcoded content the developer wrote (SVG icon paths,
the star-rating markup) or admin-authored `Post`/`Page` content — never
user-submitted data (lead fields, search queries) render unescaped. CSRF
present on every POST/PUT/PATCH/DELETE form; the three forms without
`@csrf` are all `method="GET"` search forms, which is correct (GET doesn't
need it). `composer audit` returned zero known CVEs in current dependencies.

**Added** (real gaps, not hypothetical):
- `app/Http/Middleware/SecurityHeaders.php`, registered globally in
  `bootstrap/app.php`. Sets `X-Frame-Options`, `X-Content-Type-Options`,
  `Referrer-Policy`, `Permissions-Policy`, and a same-origin CSP on every
  response (public and admin). The CSP allows `'unsafe-inline'` for
  script/style — deliberate, not an oversight: the theme has inline
  `style="background-image:..."` attributes throughout the section
  partials, plus inline `<script>` blocks (hero GSAP animation, contact
  form budget-pill JS, admin sidebar toggle). A nonce-based strict CSP
  would need those refactored into external files/attributes first. What
  the current CSP *does* still stop: loading script/style/objects from an
  attacker-controlled external origin after an XSS bug, which is the part
  that actually limits blast radius. Confirmed via grep that the project
  has zero external CDN references, so `default-src 'self'` doesn't break
  anything.
- `Strict-Transport-Security` is conditional on `$request->secure()` —
  deliberately not sent over plain HTTP, since caching an HSTS rule for a
  domain whose SSL isn't confirmed working yet would be a self-inflicted
  outage.
- `AppServiceProvider::boot()` now calls `URL::forceScheme('https')`, but
  only `if ($this->app->environment('production'))` — guards against
  breaking every generated link during local `http://` development.
- `DEPLOYMENT.md`: a concrete pre-launch checklist, written from what this
  specific review actually found (not a generic template). Top item:
  `APP_DEBUG` is currently `true`, which would leak full stack traces and
  `.env` values (DB credentials, app key) to any visitor who triggers an
  error, once this is on a real domain. That single flag is the highest-
  impact unfixed item in the whole project right now.

Verified with the full 24-test suite (unaffected — headers are additive)
plus a manual curl pass confirming headers appear on both a public page and
`/admin/login`, and that all public routes still return 200 with the CSP
active.

**Not done, flagged in DEPLOYMENT.md instead of fixed silently:** default
admin password, `APP_DEBUG`/`APP_ENV` flip, HTTPS/SSL setup, DB backups, git
init. These are deployment-time decisions (the user needs a real domain and
server to do most of them), not something to silently change on a
still-local, still-in-development project.

---

## 2026-08-17 — Admin UI redesign: sidebar nav, light content area

User feedback: "everything is very dark" in the admin. Original admin.css
was a single dark palette (`--adm-bg: #0b0b0d` etc.) with a horizontal top
nav. Redesigned to the common dashboard pattern instead: **dark sidebar for
brand identity, light content area for readability** — tables and forms are
where an admin's eyes actually spend time, so that's where the contrast
budget went. Also moved navigation from a horizontal top bar into a fixed
left sidebar per explicit request.

- `admin.css` fully rewritten: new CSS variables split into content-area
  (`--adm-bg`, `--adm-panel`, …, now light) and sidebar (`--adm-sb-*`, still
  dark, keeps the lime accent `--adm-sb-accent`). `admin-forms.css` needed
  **no changes** — every rule in it already referenced the shared `var(--adm-*)`
  custom properties rather than hardcoded colors, so it picked up the new
  palette automatically. Worth remembering: that's exactly why the original
  file was built var-first — this redesign is the payoff.
- Sidebar nav icons are hand-written inline SVGs (in `admin/layout.blade.php`,
  simple stroke-style shapes), not an icon font or CDN — consistent with the
  project's "admin has zero external dependencies" rule from the previous
  session.
- Mobile: sidebar goes off-canvas (`transform: translateX(-100%)`) under a
  900px breakpoint, toggled by a hamburger button + overlay via a ~10-line
  inline script. **Gotcha caught before shipping:** first draft tried to
  show the overlay via a CSS sibling selector
  (`.adm-sidebar.is-open ~ .adm-sidebar-overlay`), which silently never
  matches because the overlay `<div>` is placed *before* `<aside
  class="adm-sidebar">` in the DOM — sibling combinators only select
  elements that come *after* in source order. Fixed by giving the overlay
  its own `data-sidebar-overlay` hook and toggling `.is-open` on it directly
  from the same JS that toggles the sidebar, rather than relying on a CSS
  relationship. If a similar overlay/backdrop pattern gets added elsewhere,
  toggle the class explicitly — don't reach for a sibling selector without
  checking DOM order first.
- Verified via the existing 24-test suite (no admin route/behavior changed,
  purely CSS + one Blade layout file) plus a manual login pass confirming
  all six admin sections render under the new sidebar.

**Where the frontend policy page links live** (asked in the same message):
`resources/views/partials/support-cta.blade.php`, inside `.support-cta-copyright`,
below the copyright line — a `.footer-links` nav that loops
`Page::footerLinks()` (see `context.md`). Present at the bottom of every
public page, easy to miss on a quick scroll since it's small and low-contrast
text against the dark footer band by design (secondary link, not a primary CTA).

---

## 2026-08-17 — Blog, admin CMS, dynamic settings/SEO

Built out from the static-site conversion (see entry below) into a full
content-managed site: leads admin, blog (posts + categories), admin-editable
policy pages, site-wide settings, and per-page SEO meta overrides.

**Real bugs found and fixed during testing** (not hypothetical — each one
reproduced against the live MySQL instance before being fixed):

- **Admin routes were binding by slug, not ID.** `Post`, `Page` and
  `Category` all override `getRouteKeyName()` to `slug` for clean public
  URLs (`/blog/my-post`). That model default leaked into the admin routes
  too, so `/admin/posts/{post}/edit` also resolved by slug. Editing a post
  and changing its slug in the same request is fine, but the *route itself*
  binding by a value the request might change is fragile, and it's needless
  coupling between the public URL scheme and the admin one. Fixed by binding
  admin routes explicitly by ID: `{post:id}`, `{page:id}`, `{category:id}`.
  Public routes keep `{post:slug}`. Worth remembering if a new admin
  resource route gets added later — default to `:id` there, always.

- **Disabled form inputs don't submit — locked page editing 500'd.**
  `admin/pages/form.blade.php` renders the slug field with `@disabled` when
  `$page->is_locked` (privacy policy, terms). Browsers never submit a
  disabled input, so `$request->validate()` didn't just get an empty string
  for `slug`, it got no key at all — `$data['slug']` threw "Undefined array
  key." Same shape of bug existed in the post and category controllers even
  though nothing disables those slug fields today. Fixed all three
  controllers to use `$data['slug'] ?? null` before the `?:` fallback.
  General lesson: any field that can be conditionally absent from a
  `disabled`/unchecked-checkbox input needs `??`, not just `?:`, in the
  controller — `?:` still requires the key to exist.

- **Leads' `budget` was originally free text.** A tampered/mis-encoded
  hidden input could reach the database and throw a raw SQL encoding error
  instead of failing validation cleanly (surfaced first as a false alarm —
  see "Tooling gotcha" below, but the underlying laxness was real). Fixed by
  making `Lead::BUDGETS` the single source of truth: the form pills render
  from it, and `ContactController` validates against it with `Rule::in()`.

**Design decisions:**

- Settings and per-page SEO overrides are cached with
  `Cache::rememberForever` and busted via model `saved`/`deleted` hooks —
  reads are then free. Initially wrote the settings fallback (missing table
  on first boot, before migrations run) by catching the exception *inside*
  the `rememberForever` closure — caught in review before it shipped: that
  pattern permanently caches the empty fallback, so the real settings would
  never load even after migrating, until someone manually cleared the cache.
  Moved the try/catch *outside* the cache call instead, so a failed read
  caches nothing and the next request tries the database again.
- `Post::scopePublished()` / `isLive()` check both `status = published` AND
  `published_at <= now()`, not just status. This is what makes "schedule for
  next week" work by just setting a future date — no separate scheduling
  system needed. Covered by `test_scheduled_post_is_hidden_until_its_publish_date`.
- Deleting a `Category` does not delete its `Post`s (FK is `nullOnDelete`).
  Stated explicitly in the admin UI copy so it doesn't read as a bug.
- The admin HTML editor (`admin/partials/editor.blade.php`) is a plain
  textarea with a tag-wrapping toolbar, not a CDN-loaded WYSIWYG (TipTap/
  Quill/etc.). Deliberate: the admin should not depend on a third-party CDN
  being reachable, and no build step exists in this project to bundle one
  locally. Revisit if the user asks for a richer editing experience.

**Tooling gotcha (cost real debugging time, not a code bug):**
`curl -F` (multipart form upload) from this machine's Git Bash, against
`php artisan serve`, returns `000` (connection dropped) — happens
regardless of whether the file path is POSIX- or Windows-style. Non-issue:
confirmed the app-level code is correct via
`tests/Feature/BlogTest.php::test_admin_can_create_a_post_with_a_featured_image`
and `::test_admin_can_replace_and_remove_the_featured_image`, which drive
the same controller through Laravel's HTTP test client
(`Illuminate\Http\UploadedFile::fake()`) instead of curl, and pass. **Don't
re-debug this** — it's the shell/curl combination, not the server. If manual
verification of uploads is ever needed again, use the Laravel test client or
a real browser, not `curl -F` from this Git Bash.

Also: `php artisan db:show` throws on this MariaDB/XAMPP instance
(`performance_schema.session_status` doesn't exist — that's a MySQL-only
introspection table MariaDB doesn't ship). Not a connection problem; a real
query (`DB::selectOne('select version()')`) confirmed the connection is
fine. Don't use `db:show` to sanity-check this DB.

**Test coverage added:** `tests/Feature/BlogTest.php` and
`tests/Feature/ContentTest.php`, 24 tests total, run against in-memory
SQLite (`php artisan test` — never touches the real `dbvoid` database).
Covers: publish/draft/schedule visibility, image upload/replace/remove,
category filtering, JSON-LD structured data, lead validation + honeypot,
locked-page editing/deletion, settings propagation, SEO override + noindex,
sitemap filtering, and admin auth guards on every admin route.

---

## 2026-08-17 — Initial React → Laravel conversion + leads backend

Converted the React/Vite "Void Layer" theme (`../new`) into a static-first
Laravel/Blade site, then added a real leads backend.

**Key decisions:**

- No JS build step. The React app loaded vendor JS (jQuery, GSAP, Bootstrap,
  Swiper, …) via npm imports in `loadLegacyVendor.ts`. Laravel just loads the
  same vendor files as classic `<script>` tags in the layout, same order.
  Simpler, no Vite/npm dependency for what's meant to stay a static-content
  site with a light backend.
- Every React section component became its own Blade partial under
  `resources/views/sections/{landing,about,services,contact}/` — kept the
  1:1 mapping so future changes are easy to trace back to the original.
- Contact form: originally just `mailto:`-redirected like the React version.
  Later wired to a real `POST /contact` → `Lead` model, with CSRF, allowlist
  validation on `budget`, and a honeypot field (`website`, must be empty).

**User's actual environment** (not assumptions — confirmed):
XAMPP Apache + MySQL/MariaDB, MySQL on port **3307** (not the 3306 default),
database `dbvoid` already existed before this project touched it. `.env` and
`.env.example` both point at this.

**Admin dashboard** (`/admin/*`): session-auth via Laravel's built-in
`Auth::attempt`, no separate package. Seeded via `AdminUserSeeder` reading
`ADMIN_EMAIL`/`ADMIN_PASSWORD` from `.env` (defaults: `admin@voidlayer.in` /
`password` — **must be changed before this is internet-facing**). Own
`admin.css`, isolated from the public theme's CSS so admin styling can't be
broken by a theme update and vice versa.
