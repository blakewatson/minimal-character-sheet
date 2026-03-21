# Technology Stack

**Analysis Date:** 2026-03-21

## Languages

**Primary:**
- PHP 8.4 - Backend server, routing, models, controllers, templating
- JavaScript (ES2015+) - Frontend Vue.js application, build tooling

**Secondary:**
- CSS - Tailwind v4 utility styles in `css/app.css`
- SQL - SQLite queries via Fat-Free Framework ORM

## Runtime

**Environment:**
- PHP 8.4 (CLI and web server)
- Node.js v22.17.1 (build tooling only)

**Package Manager:**
- PHP: Composer — lockfile present at `composer.lock`
- JS: npm — lockfile present at `package-lock.json`

## Frameworks

**Core:**
- Fat-Free Framework (F3) 3.8.2 — PHP micro-framework providing routing, templating, ORM (`bcosca/fatfree-core`)
- Vue.js 2.6.7 — Frontend reactive UI framework; entry points in `js/app.js`, `js/dashboard.js`, `js/print.js`
- Vuex 3.1.0 — State management for Vue; store defined in `js/store.js`

**CSS:**
- Tailwind CSS v4 via `@tailwindcss/postcss` ^4.1.18 — No separate config file; all configuration done in `css/app.css` using `@theme` and `@custom-variant` directives

**Build/Dev:**
- Laravel Mix 6 — Webpack wrapper; config in `webpack.mix.js`
- Sass 1.x / sass-loader 12.x — SCSS preprocessing (available but primary styles use PostCSS)
- vue-loader 15.x / vue-template-compiler 2.7.14 — Vue SFC compilation

**Formatting:**
- Prettier 3.6.2 with `prettier-plugin-tailwindcss` ^0.7.2

## Key Dependencies

**Critical:**
- `bcosca/fatfree-core` 3.8.2 — All routing, ORM, sessions, templating
- `vue` 2.6.7 — All frontend UI
- `vuex` 3.1.0 — All frontend state
- `quill` 2.0.3 — Rich text editor used in `js/components/QuillEditor.vue`; bubble CSS copied to `dist/quill.bubble.css`
- `notyf` 3.10.0 — Toast notification library used in frontend

**Infrastructure:**
- `vlucas/phpdotenv` v5.5.0 — `.env` file loading; required at startup in `index.php`
- `wildbit/postmark-php` v4.0.5 — Transactional email SDK used in `classes/controllers/Authentication.php`
- `guzzlehttp/guzzle` 7.7.0 — HTTP client (pulled in as Postmark SDK dependency)
- `ramsey/uuid` 4.9.0 — UUID generation (pulled in transitively)
- `spatie/ray` 1.42.0 — Debug tool (development only; should not be called in production)

**Vendored (non-npm):**
- `js/lib/markdown-it.min.js` — Markdown rendering; loaded as `window.markdownit` in templates
- `js/baguetteBox.min.js` — Lightbox for homepage image gallery

## Configuration

**Environment:**
- `.env` file required at project root (loaded via phpdotenv)
- Required: `POSTMARK_SECRET`
- Optional: `ENV` (set to `MAINTENANCE` to enable maintenance mode, `DEVELOPMENT` to route emails to Postmark blackhole address), `DEBUG` (F3 debug level integer)

**Build:**
- `webpack.mix.js` — Defines all JS/CSS compilation steps and versioning
- Output directory: `dist/`
- Asset versioning manifest: `dist/mix-manifest.json` (consumed by `mix()` helper in `index.php`)
- No `tailwind.config.js` — Tailwind v4 configuration lives entirely in `css/app.css`

## Platform Requirements

**Development:**
- PHP 8.4+
- Node.js 22+
- Composer
- npm
- SQLite support in PHP

**Production:**
- PHP 8.4+ web server
- Web server must block access to `/data` directory (contains SQLite file)
- SQLite — database auto-created at `data/db.sqlite3`
- Compiled assets served from `dist/`

---

*Stack analysis: 2026-03-21*
