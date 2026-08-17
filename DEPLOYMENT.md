# Production checklist

Run through this before pointing a real domain at this site. Nothing here
is optional — each item is a genuine gap identified during a security
review of the current code (2026-08-17). Check off as you go.

## 1. Environment (`.env`)

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false` — **critical**. Left `true`, any unhandled error shows
      the full stack trace *and* environment values (DB credentials, app
      key, mail settings) to whoever triggered it. This is the single
      highest-impact item on this list.
- [ ] `APP_URL=https://your-real-domain` (not `http://localhost`) — feeds
      canonical URLs, the sitemap, and OG tags.
- [ ] `SESSION_SECURE_COOKIE=true` — only once HTTPS is actually working.
      Setting it before SSL is live will silently break login (the browser
      won't send a `Secure` cookie over plain HTTP).
- [ ] `ADMIN_EMAIL` / `ADMIN_PASSWORD` — change from the defaults
      (`admin@voidlayer.in` / `password`), then re-run:
      `php artisan db:seed --class=AdminUserSeeder`
- [ ] `MAIL_MAILER` — currently `log` (new leads never trigger an email).
      Set up real SMTP/Mailgun/SES if you want to be notified when a lead
      comes in, rather than having to check `/admin/leads`.

## 2. Web server (Apache / XAMPP)

- [ ] Document root points at `public/` — **never** the project root, or
      `.env` and the whole codebase become downloadable.
- [ ] `mod_rewrite` enabled (Laravel's `public/.htaccess` depends on it for
      clean URLs).
- [ ] Valid SSL certificate installed, HTTP → HTTPS redirect configured at
      the server level (Laravel's `URL::forceScheme('https')` only affects
      generated links, not incoming plain-HTTP requests).
- [ ] `expose_php = Off` in `php.ini` — stops PHP announcing its version in
      the `X-Powered-By` header (can't be set from Laravel; it's a php.ini
      setting).

## 3. Performance (do after `.env` is final — these cache it)

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Re-run `config:cache` after *any* `.env` change in production, or the app
keeps using the stale cached values.

## 4. Database

- [ ] Set up a real backup schedule for `dbvoid` — nothing exists today.
      Even a daily `mysqldump` cron job is better than nothing.
- [ ] Confirm the MySQL user Laravel connects as doesn't have more grants
      than it needs (no `DROP`/`GRANT` if avoidable).

## 5. Version control

- [ ] `git init` in this project — there's currently no history at all, so
      no way to diff or roll back a bad change. Make sure `.env` stays out
      of it (already in `.gitignore` by default).

## 6. Content

- [ ] Privacy Policy / Terms & Conditions (`/admin/pages`) were seeded as
      AI-drafted placeholder text so the pages weren't empty — get an
      actual legal read before treating them as your real policy,
      especially anything India-specific.
- [ ] Only one demo blog post exists (`/admin/posts`) — replace/add real
      content before pointing traffic at `/blog`.

## 7. Recommended before going live

- [ ] **Enable 2FA** on the admin account at `/admin/security` — save the
      recovery codes somewhere other than the same device (a password
      manager, not a screenshot on the phone you'd lose along with the app).
- [ ] If a team member only needs to write blog posts and manage leads,
      create them as `editor` at `/admin/users` rather than `admin` — editors
      can't reach Settings, SEO, Team, or Redirects.
- [ ] Fill in Settings > Integrations if wanted: Google Analytics, Meta
      Pixel, reCAPTCHA v2 (adds a checkbox to the public contact form on top
      of the existing honeypot).

## 8. Final pass before going live

```bash
php artisan test          # all 83 should pass
```

Then manually: log into `/admin` with the new password, click through every
section once, submit the public contact form, and check the site on an
actual mobile device (not just a resized browser window) — particularly the
admin sidebar's hamburger toggle.

---

## What's already handled (verified 2026-08-17/18, see `memory.md` for detail)

- SQL injection — no raw queries anywhere, Eloquent throughout.
- XSS — all user-submitted data rendered via escaped `{{ }}`; only
  admin-authored content uses raw HTML output, by design.
- CSRF — every state-changing form is protected.
- File uploads — validated as actual images, size-capped.
- Rate limiting — login, the 2FA challenge, and the contact form are all
  throttled per IP.
- Spam — honeypot field + allowlisted budget field on the contact form,
  optional reCAPTCHA v2 on top.
- Dependencies — `composer audit` clean as of this review; zero new composer
  packages were added for any feature, including 2FA (hand-rolled RFC 6238
  TOTP, verified against the official test vectors — see `tests/Unit/TotpTest.php`).
- Access control — `admin` vs `editor` roles, enforced server-side (not just
  hidden nav links); the last remaining admin can't be demoted or deleted.
- Two-factor auth — optional, self-service per account, with single-use
  recovery codes; a password alone never completes login once it's on
  (`Auth::validate()` checks the password without establishing a session).
- Security headers — `X-Frame-Options`, `X-Content-Type-Options`,
  `Referrer-Policy`, `Permissions-Policy`, and a same-origin `Content-Security-Policy`
  are set on every response by `app/Http/Middleware/SecurityHeaders.php`.
  `Strict-Transport-Security` is added automatically once requests arrive
  over HTTPS — nothing to configure for it beyond having SSL working.
