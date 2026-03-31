# External Integrations

**Analysis Date:** 2026-03-21

## APIs & External Services

**Transactional Email:**
- Postmark — Sends account confirmation emails and password reset emails
  - SDK: `wildbit/postmark-php` v4.0.5
  - Auth: `POSTMARK_SECRET` env var
  - Sender address: `minimalcharactersheet@blakewatson.com` (hardcoded in `classes/controllers/Authentication.php`)
  - In `DEVELOPMENT` env, emails are routed to Postmark's blackhole address (`test@blackhole.postmarkapp.com`)
  - Used in: `Authentication->email_token()`, `Authentication->email_confirmation_token()`, `Authentication->email_password_reset_token()` in `classes/controllers/Authentication.php`

## Data Storage

**Databases:**
- SQLite
  - Connection: `sqlite:data/db.sqlite3` (hardcoded in `index.php`)
  - Client: Fat-Free Framework ORM (`\DB\SQL` and `\DB\SQL\Mapper`)
  - Session storage: F3 SQL session handler (`\DB\SQL\Session`) — sessions persisted to `sessions` table in same SQLite file
  - Models: `classes/models/User.php`, `classes/models/Sheet.php`, `classes/models/Token.php`
  - Migrations: `migrations/001.php` through `migrations/004.php`
  - Character sheet data stored as a JSON blob in the `data` column of the `sheet` table

**File Storage:**
- Local filesystem only — no cloud file storage

**Caching:**
- None — no Redis, Memcached, or similar

## Authentication & Identity

**Auth Provider:**
- Custom — no third-party auth provider (no OAuth, no Auth0, etc.)
  - Implementation: Email + password with `password_hash()` / `password_verify()` (PHP built-in bcrypt)
  - Session: F3 SQL-backed sessions; user email stored in `SESSION.email`
  - Email confirmation required before login is permitted
  - CSRF protection via token stored in session, verified on all POST forms (`Authentication->set_csrf()`, `Authentication->verify_csrf()`)
  - Ajax CSRF verification via `X-Ajax-Csrf` request header (`Authentication->verify_ajax_csrf()`)
  - Honeypot fields used on all auth forms to catch bots
  - Password reset tokens: time-limited (1 hour), one-time-use, stored hashed in `reset_token` column of user table

## Monitoring & Observability

**Error Tracking:**
- None — no Sentry, Bugsnag, or similar

**Debug Tool:**
- Spatie Ray 1.42.0 (`spatie/ray`) — local debug tool; available in dev, should not be used in production
- F3 built-in error handling: custom error template at `templates/error.html`, debug level set by `DEBUG` env var

**Logs:**
- PHP `error_log()` used minimally (e.g., logging maintenance mode activation in `index.php`)
- No structured logging framework

## CI/CD & Deployment

**Hosting:**
- Not detected in codebase — no Dockerfile, Procfile, or platform config files present

**CI Pipeline:**
- Not detected — no `.github/workflows/`, `.circleci/`, or similar

## Environment Configuration

**Required env vars:**
- `POSTMARK_SECRET` — Postmark API key; enforced via `$dotenv->required()` in `index.php`; app will not start without it

**Optional env vars:**
- `ENV` — Set to `MAINTENANCE` to show maintenance page before any routing; set to `DEVELOPMENT` to redirect emails to Postmark blackhole
- `DEBUG` — Integer passed to F3's `DEBUG` setting (0 = no debug output)

**Secrets location:**
- `.env` file at project root (not committed to git)

## Webhooks & Callbacks

**Incoming:**
- None — no webhook receiver endpoints

**Outgoing:**
- None — no outgoing webhooks

## Frontend External Resources

**Fonts:**
- IBM Plex Mono — referenced in `css/app.css` via `--font-mono` theme variable; loaded via system or CDN in HTML templates (not bundled)

**i18n:**
- Translation files are local JSON: `js/languages/en.json`, `js/languages/de.json`
- No external translation service

## Public Sheet Polling

The frontend polls the backend for updates on public (read-only) sheets:
- Endpoint: `GET /sheet-data/@sheet_slug` handled by `Dashboard->get_sheet_data()` in `classes/controllers/Dashboard.php`
- Returns sheet data only if the sheet has been updated since last poll; this is an internal app mechanism, not a third-party integration

---

*Integration audit: 2026-03-21*
