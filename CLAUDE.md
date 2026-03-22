# CLAUDE.md

This file provides guidance to Claude Code when working with this repository.

## Development Commands

- `npm run dev` - Build assets for development
- `npm run watch` - Watch and rebuild during development
- `npm run prod` - Build optimized assets for production
- `composer install` - Install PHP dependencies
- After editing any `.js`, `.vue`, or `.html` file, run `npx prettier --write <file>` to format it

## Architecture Overview

D&D 5e character sheet web app with a PHP backend and Vue.js 3 frontend.

### Backend (PHP)

- **Framework**: Fat-Free Framework (F3) — routing, templating, ORM
- **Database**: SQLite at `data/db.sqlite3`
- **Email**: Postmark (signup confirmation, password reset)
- **Entry point**: `index.php` — all routes defined here
- **Models**: `classes/models/` — User, Sheet, Token
- **Controllers**: `classes/controllers/` — Authentication, Dashboard, Admin
- **Templates**: `templates/` — F3 HTML templates with `header.html`/`footer.html` partials

### Frontend (Vue.js 3)

- **Build**: Vite — config in `vite.config.js`
- **CSS**: Tailwind CSS v4 via `@tailwindcss/vite` — styles in `css/app.css`, no tailwind config file (v4 uses CSS-based configuration)
- **Entry points** (each compiled to `dist/`):
  - `js/app.js` — Character sheet (main app)
  - `js/dashboard.js` — User dashboard
  - `js/print.js` — Print view
- **State**: reactive() composable store in `js/store.js` — holds all character sheet data
- **Components**: `js/components/` — 26 Vue single-file components
  - `Sheet.vue` — main character sheet layout
  - `Abilities.vue`/`Ability.vue` — ability scores
  - `Skills.vue`, `Spells.vue`, `Attacks.vue`, `Equipment.vue`, `Bio.vue` — sheet sections
  - `Field.vue` — reusable form field component
  - `QuillEditor.vue` — rich text editing (Quill)
  - `Tabs.vue` — tab navigation
- **i18n**: `js/i18n.js` with JSON translation files in `js/languages/` (English, German). Uses `t(key)` for translations with English fallback.
- **Mixins**: `js/mixins.js` — shared `listMixin` for dynamic list fields
- **Settings**: `js/settings.js` — client-side settings (dark mode, textarea font, language) stored in localStorage
- **Utilities**: `js/utils.js` — shared helper functions

### Key Concepts

- Character sheets are stored as JSON blobs in SQLite
- The `is_2024` flag on sheets toggles D&D 2024 rules support
- Sheets can be public (read-only shareable link) or private
- Auto-save: changes POST to backend automatically
- Public sheets poll for updates via `/sheet-data/@slug`
- Asset versioning via Vite manifest for cache busting (`vite()` helper in `index.php`)

### Environment Setup

- Requires `.env` file with `POSTMARK_SECRET` and optional `ENV`, `DEBUG` vars
- SQLite database auto-created in `data/` directory
- Web server must block access to `/data` directory
- `ENV=MAINTENANCE` enables maintenance mode

<!-- GSD:project-start source:PROJECT.md -->
## Project

**Minimal Character Sheet — Vue 3 Migration**

A D&D 5e character sheet web app with a PHP (Fat-Free Framework) backend and Vue.js frontend. The app runs on Vue 3 + a `reactive()` composable store + Vite.

**Core Value:** The app must continue to work identically after migration — same features, same behavior, same PHP backend integration. No regressions in editing, autosave, print view, public sheets, or dashboard.

### Constraints

- **Tech stack**: Must keep PHP (Fat-Free Framework) backend, SQLite database — no backend changes
- **Build output**: Vite must output to `dist/` directory, same as current Mix setup
- **No dev server**: Using Vite as build tool only (not its dev server), since PHP serves the app
- **Branch**: All work on `vue-3` branch
<!-- GSD:project-end -->

<!-- GSD:stack-start source:codebase/STACK.md -->
## Technology Stack

## Languages
- PHP 8.4 - Backend server, routing, models, controllers, templating
- JavaScript (ES2015+) - Frontend Vue.js application, build tooling
- CSS - Tailwind v4 utility styles in `css/app.css`
- SQL - SQLite queries via Fat-Free Framework ORM
## Runtime
- PHP 8.4 (CLI and web server)
- Node.js v22.17.1 (build tooling only)
- PHP: Composer — lockfile present at `composer.lock`
- JS: npm — lockfile present at `package-lock.json`
## Frameworks
- Fat-Free Framework (F3) 3.8.2 — PHP micro-framework providing routing, templating, ORM (`bcosca/fatfree-core`)
- Vue.js 3.5 — Frontend reactive UI framework; entry points in `js/app.js`, `js/dashboard.js`, `js/print.js`
- reactive() composable — State management in `js/store.js` (plain Vue 3 reactivity, no external state library)
- Tailwind CSS v4 via `@tailwindcss/vite` ^4.2.2 — No separate config file; all configuration done in `css/app.css` using `@theme` and `@custom-variant` directives
- Vite 8 — Build tool; config in `vite.config.js`
- @vitejs/plugin-vue 6 — Vue 3 SFC compilation
- Prettier 3.6.2 with `prettier-plugin-tailwindcss` ^0.7.2
## Key Dependencies
- `bcosca/fatfree-core` 3.8.2 — All routing, ORM, sessions, templating
- `vue` 3.5 — All frontend UI
- `quill` 2.0.3 — Rich text editor used in `js/components/QuillEditor.vue`; bubble CSS copied to `dist/quill.bubble.css`
- `notyf` 3.10.0 — Toast notification library used in frontend
- `vlucas/phpdotenv` v5.5.0 — `.env` file loading; required at startup in `index.php`
- `wildbit/postmark-php` v4.0.5 — Transactional email SDK used in `classes/controllers/Authentication.php`
- `guzzlehttp/guzzle` 7.7.0 — HTTP client (pulled in as Postmark SDK dependency)
- `ramsey/uuid` 4.9.0 — UUID generation (pulled in transitively)
- `spatie/ray` 1.42.0 — Debug tool (development only; should not be called in production)
- `js/lib/markdown-it.min.js` — Markdown rendering; loaded as `window.markdownit` in templates
- `js/baguetteBox.min.js` — Lightbox for homepage image gallery
## Configuration
- `.env` file required at project root (loaded via phpdotenv)
- Required: `POSTMARK_SECRET`
- Optional: `ENV` (set to `MAINTENANCE` to enable maintenance mode, `DEVELOPMENT` to route emails to Postmark blackhole address), `DEBUG` (F3 debug level integer)
- `vite.config.js` — Defines all JS/CSS compilation steps and versioning
- Output directory: `dist/`
- Asset versioning manifest: `dist/.vite/manifest.json` (consumed by `vite()` helper in `index.php`)
- No `tailwind.config.js` — Tailwind v4 configuration lives entirely in `css/app.css`
## Platform Requirements
- PHP 8.4+
- Node.js 22+
- Composer
- npm
- SQLite support in PHP
- PHP 8.4+ web server
- Web server must block access to `/data` directory (contains SQLite file)
- SQLite — database auto-created at `data/db.sqlite3`
- Compiled assets served from `dist/`
<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->
## Conventions

## Naming Patterns
- Vue components: PascalCase — `Sheet.vue`, `QuillEditor.vue`, `AddContentDialog.vue`
- Sub-directories for grouped components: `js/components/SearchResults/`
- Plain JS modules: camelCase — `store.js`, `utils.js`, `mixins.js`, `i18n.js`, `settings.js`
- PHP classes: PascalCase matching class name — `Authentication.php`, `Sheet.php`, `User.php`
- PascalCase: `name: 'Sheet'`, `name: 'QuillEditor'`, `name: 'Field'`
- Lowercase kebab or single word for tag name: `'text-section': TextSection`, `'trackable-fields': TrackableFields`
- Single-word components use lowercase: `ability: Ability`, `tabs: Tabs`
- camelCase: `saveSheetState`, `autosaveLoop`, `resetRetryState`, `updateBio`, `toggleProficiency`
- PHP: snake_case — `create_sheet`, `save_sheet`, `get_sheet_by_slug`, `email_confirmation_token`
- JS: camelCase — `sheetSlug`, `isPublic`, `formBody`
- PHP: snake_case — `$sheet_data`, `$error_message`, `$clear_token`
- `var` is used extensively in older JS code alongside `const`/`let` in newer code
- camelCase for most: `characterName`, `readOnly`, `hitDie`
- Snake_case for DB-sourced flags: `is_2024`
- Spell level fields use abbreviated prefix: `lvl1Spells`, `lvl2Spells`, etc.
- Quill/rich-text fields end in `Text`: `equipmentText`, `featuresText`, `backstoryText`
- camelCase in component definition: `readOnly`, `autoSize`, `initialContents`
- kebab-case in templates: `:read-only`, `:auto-size`, `:initial-contents`
- kebab-case strings: `'update-value'`, `'quill-text-change'`, `'update-collapsed'`, `'manual-save'`
## Code Style
- Prettier — config at `.prettierrc`
- Single quotes for strings (`"singleQuote": true`)
- `prettier-plugin-tailwindcss` for automatic class sorting in templates
- No explicit line length or tab width configured (Prettier defaults apply)
- No ESLint config present — no automated linting enforced
- PHP: 4 spaces (observed throughout `classes/`)
- JS/Vue: 2 spaces (Prettier default)
## Import Organization
- Relative paths for local imports — `'./components/Sheet'`, `'../utils'`, `'./languages/en.json'`
- No path aliases configured
- Plugins registered at app entry: `app.use(i18nPlugin)` in `js/app.js`
- Global properties: `app.config.globalProperties.$signedNumString = signedNumString`
## Error Handling
- `try/catch` with `console.error()` for async operations (fetch, clipboard, store dispatch)
- Early return pattern for guard clauses: `if (this.isPublic) return;`
- Promise `.catch()` on store dispatches: `.catch((reason) => console.log(reason))`
- Error objects augmented with `status` property before throwing: `error.status = response.status`
- Retry logic with exponential backoff implemented manually in `Sheet.vue`
- User-facing errors shown via Notyf toast notifications
- Early return with error template render for validation failures
- `error_log()` for server-side logging — no structured logger
- Return `false` from model methods when record not found (dry mapper)
- No exceptions thrown; errors communicated via return values or F3 hive variables
## Logging
- `console.error` for caught exceptions and fetch failures
- `console.log` for debug output (some debug `console.log` statements left in production code — e.g., `js/store.js:352`, `js/components/Tabs.vue:239`)
- PHP: `error_log()` for server-side issues, written to web server error log
## i18n
- Template usage: `{{ $t('Key') }}` — key is the English string itself
- JS usage (outside components): `import { t } from './i18n'; t('Key')`
- Fallback chain: current locale → English → raw key
- Translation files: `js/languages/en.json`, `js/languages/de.json`
## Comments
- Step-numbered inline comments on complex multi-phase methods (see `saveSheetState` in `js/components/Sheet.vue`)
- Brief inline comments for non-obvious guard clauses
- JSDoc on exported utility functions in `js/i18n.js`
- PHP: short inline comments for business logic steps
- JSDoc used in `js/i18n.js` (`@param`, `@returns`)
- No JSDoc on Vue component methods or store functions
## Function Design
- Store mutation functions receive individual parameters: `updateBio(field, val)`, `addAttack()`
- Vue component methods receive event values directly from template `$event`
- Async component methods return `true`/`false` for success/failure
- PHP model methods return associative array on success, `false` on failure
## Module Design
- Named exports for utility functions: `export function throttle(...)`, `export const signedNumString`
- Named exports for store: `export { state, modifiers, proficiencyBonus, updateBio, ... }`
- Vue plugin exported as named const: `export const i18nPlugin`
- None used — each module imported directly by path
## Vue Component Structure Order
<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->
## Architecture

## Pattern Overview
- PHP backend handles routing, authentication, and HTML rendering via Fat-Free Framework templates
- The character sheet page embeds a full Vue.js 3 SPA inside a server-rendered shell
- Dashboard and other pages use plain JavaScript (no Vue) with server-rendered HTML
- Character sheet data is stored as a JSON blob in SQLite, retrieved by the server and injected into the page as a JS global (`window.sheet`)
## Layers
- Purpose: Map HTTP requests to controller methods
- Location: `index.php`
- Contains: All route definitions (GET/POST/DELETE), global helper functions, F3 bootstrap
- Depends on: Fat-Free Framework (`\Base`)
- Used by: All incoming HTTP requests
- Purpose: Handle request/response lifecycle, enforce auth, pass data to templates
- Location: `classes/controllers/`
- Contains: `Authentication.php`, `Dashboard.php`, `Admin.php`
- Depends on: Model layer, `\Template` for rendering
- Used by: Router in `index.php`
- Purpose: Database access via F3's Active Record ORM
- Location: `classes/models/`
- Contains: `User.php`, `Sheet.php`, `Token.php`
- Depends on: F3 `\DB\SQL\Mapper`, SQLite at `data/db.sqlite3`
- Used by: Controllers
- Purpose: Server-side HTML rendering
- Location: `templates/`
- Contains: F3 HTML templates; `header.html`/`footer.html` included as partials in every page
- Depends on: F3 `\Template`, controller-set variables via `$f3->set()`
- Used by: Controllers calling `\Template::instance()->render()`
- Purpose: Interactive character sheet UI
- Location: `js/app.js`, `js/components/Sheet.vue` and `js/components/`
- Contains: Vue.js 3 SPA mounted at `#sheet`, reactive() store (`js/store.js`)
- Depends on: `window.sheet` global injected by PHP template, `/sheet/@slug` POST endpoint for saves
- Used by: `templates/sheet.html`
- Purpose: Vanilla JS interactions on the dashboard (toggling public state, deleting sheets)
- Location: `js/dashboard.js`
- Contains: Plain DOM manipulation, fetch calls to backend API endpoints
- Depends on: `#csrf` hidden input for CSRF token, data attributes on DOM elements
## Data Flow
- All character data lives in reactive() store (`js/store.js`)
- Store state is flat — all sheet fields at top level (no modules)
- Mutation functions are named per field type (e.g., `updateBio`, `updateVitals`, `addAttack`)
- `initializeState` uses Object.assign for bulk state hydration and polling updates
## Key Abstractions
- Purpose: Root component for the character sheet SPA; owns save/load lifecycle, tab navigation
- Location: `js/components/Sheet.vue`
- Pattern: Coordinates child section components, uses watch(state, { deep: true }) for autosave, manages retry logic
- Purpose: Single source of truth for all character data; serialized to JSON for saves
- Location: `js/store.js`
- Pattern: Flat reactive() state object, named mutation functions, `initializeState` / `updateState` for hydration
- Purpose: Active Record for the `sheet` SQLite table; handles slug generation, JSON encode/decode
- Location: `classes/models/Sheet.php`
- Pattern: Extends `\DB\SQL\Mapper`; `decode_sheet_data()` handles legacy double-encoded JSON
- Purpose: Handles all auth flows; provides `bounce()`, CSRF helpers, and email token dispatch
- Location: `classes/controllers/Authentication.php`
- Pattern: Instantiated by Dashboard at construction time; `bounce()` redirects unauthenticated users
- Purpose: Shared mixin for dynamic list fields (auto-appends empty item as user types)
- Location: `js/mixins.js`
- Used by: Components that manage growable lists of items
- Purpose: Translation via `t(key)` — looks up key in current locale, falls back to English, then key itself
- Location: `js/i18n.js`, locale files in `js/languages/`
- Pattern: Vue plugin adding `$t`, `$getLocale`, `$setLocale` to all components
## Entry Points
- Location: `index.php`
- Triggers: Every HTTP request (Apache routes all requests here via `.htaccess`)
- Responsibilities: Bootstrap F3, load env, define all routes, register error handler, run router
- Location: `js/app.js`
- Triggers: Loaded on `/sheet/@slug` pages via `templates/footer.html`
- Responsibilities: Register Vue plugins, create event bus (`window.sheetEvent`), initialize markdown-it, mount `Sheet.vue`
- Location: `js/dashboard.js`
- Triggers: Loaded on `/dashboard` via `templates/footer.html`
- Responsibilities: Bind event handlers for public-toggle checkboxes and delete buttons
- Location: `js/print.js`
- Triggers: Loaded on `/print/@slug` pages
- Responsibilities: Mount `Print.vue` at `#print`
## Error Handling
- JSON API endpoints return `{ success: false, reason: '...', status: NNN }`
- Form handlers re-render the same template with error messages set in F3 hive
- `$f3->set('ONERROR', ...)` renders `templates/error.html` for all unhandled errors
- Model methods return `false` on not-found or failure; controllers check return value
- `Sheet.vue` implements exponential backoff retry (1s, 3s, 6s) for save failures
- Retryable: 5xx, 429, network errors, CSRF failures
- Non-retryable: 4xx client errors
- `notyf` toast library used for user-facing error/success notifications
## Cross-Cutting Concerns
<!-- GSD:architecture-end -->

<!-- GSD:workflow-start source:GSD defaults -->
## GSD Workflow Enforcement

Before using Edit, Write, or other file-changing tools, start work through a GSD command so planning artifacts and execution context stay in sync.

Use these entry points:
- `/gsd:quick` for small fixes, doc updates, and ad-hoc tasks
- `/gsd:debug` for investigation and bug fixing
- `/gsd:execute-phase` for planned phase work

Do not make direct repo edits outside a GSD workflow unless the user explicitly asks to bypass it.
<!-- GSD:workflow-end -->

<!-- GSD:profile-start -->
## Developer Profile

> Profile not yet configured. Run `/gsd:profile-user` to generate your developer profile.
> This section is managed by `generate-claude-profile` -- do not edit manually.
<!-- GSD:profile-end -->
