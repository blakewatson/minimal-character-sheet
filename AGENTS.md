# AGENTS.md

Guidance for coding agents working in this repository.

## Project

Minimal Character Sheet is a D&D 5e character sheet web app. The backend is PHP with Fat-Free Framework and SQLite; the frontend is Vue 3 compiled by Vite and served by PHP templates.

Preserve existing behavior unless the task explicitly asks for a change. Pay special attention to editing, autosave, print view, public read-only sheets, authentication, and the dashboard.

## Development Commands

- `composer install` - Install PHP dependencies
- `npm install` - Install frontend/build dependencies
- `npm run dev` - Build frontend assets for development
- `npm run watch` - Watch and rebuild frontend assets during development
- `npm run prod` - Build optimized frontend assets
- After editing any `.js`, `.vue`, or `.html` file, run `npx prettier --write <file>`

Vite is used as a build tool only. PHP serves the app; do not introduce a separate Vite dev-server workflow unless the task specifically calls for it.

## Runtime And Configuration

- PHP 8.x with SQLite support
- Node.js for asset builds
- SQLite database at `data/db.sqlite3`
- `.env` is loaded from the project root by `vlucas/phpdotenv`
- Required env: `POSTMARK_SECRET`
- Common optional env: `POSTMARK_FROM`, `ENV`, `DEBUG`, `ALLOW_SIGNUPS`, `ADMIN_ONLY`, `FONT_AWESOME_KIT_URL`, `RANDOM_ORG_API_KEY`
- `ENV=MAINTENANCE` renders the maintenance page
- `ENV=DEVELOPMENT` sends Postmark messages to the Postmark blackhole test address

Keep private files out of the web root in deployments. At minimum, the web server must block access to `data/`, `.env`, `migrations/`, and `etl/`.

## Backend Architecture

- `index.php` bootstraps F3, loads env, configures SQLite/cache, defines all routes, and registers the error handler
- `classes/controllers/` contains request handlers:
  - `Authentication.php` handles auth, CSRF helpers, and email tokens
  - `Dashboard.php` handles sheet dashboard, CRUD, public toggles, imports, saves, and print/public sheet views
  - `Admin.php` handles admin-facing routes
- `classes/models/` contains F3 ORM mappers:
  - `User.php`
  - `Sheet.php`
  - `Token.php`
- `templates/` contains F3 templates, with `header.html` and `footer.html` as shared partials
- Character sheets are persisted as JSON blobs in SQLite
- `Sheet::decode_sheet_data()` handles legacy double-encoded sheet JSON
- Vite asset cache busting uses the manifest via helper functions in `index.php`

## Frontend Architecture

- Vite config: `vite.config.js`
- Tailwind CSS v4 entry: `css/app.css`; there is no `tailwind.config.js`
- Vite entry points compiled to `dist/`:
  - `js/app.js` - main character sheet app
  - `js/dashboard.js` - dashboard behavior
  - `js/print.js` - print view
  - `css/app.css` - shared styles
- Vue components live in `js/components/`
- `js/components/Sheet.vue` is the root character sheet component
- `js/store.js` is the flat Vue `reactive()` store and the source of truth for sheet data
- `js/i18n.js` loads translations from `js/languages/` with English fallback
- `js/settings.js` stores client-side settings in `localStorage`
- `js/mixins.js` provides shared list behavior for growable list fields
- `js/utils.js` contains shared helpers

The PHP template injects sheet data as `window.sheet`. The Vue app saves changes with POST requests to `/sheet/@slug`. Public read-only sheets poll `/sheet-data/@slug` for updates.

## Key Concepts

- The `is_2024` flag toggles D&D 2024 rules support
- Sheets can be private or public read-only share links
- Autosave is coordinated in `Sheet.vue` with retry/backoff behavior
- Retryable save failures include 5xx, 429, CSRF failures, and network errors
- Non-retryable 4xx failures should surface as user-facing errors
- User-facing notifications use `notyf`
- Rich text editing uses Quill via `QuillEditor.vue`
- Markdown rendering uses the bundled `js/lib/markdown-it.min.js`

## Code Style

- Follow the existing style in nearby files
- JS/Vue is formatted with Prettier using single quotes and the Tailwind plugin
- PHP uses 4-space indentation
- JS/Vue uses 2-space indentation
- Vue components use PascalCase filenames
- Plain JS modules use camelCase filenames
- PHP classes use PascalCase filenames matching class names
- PHP functions and variables generally use snake_case
- JS functions and variables generally use camelCase
- DB-sourced flags may use snake_case in JS, such as `is_2024`
- Spell level fields use names like `lvl1Spells`, `lvl2Spells`
- Rich-text fields generally end in `Text`, such as `equipmentText`

## Vue Conventions

- Register local components consistently with surrounding code
- Component prop names are camelCase in script and kebab-case in templates
- Custom events are kebab-case strings
- Use the i18n helper for user-facing text: `{{ $t('Key') }}` in templates and `t('Key')` in JS modules
- Keep the store flat unless a task explicitly requires a broader state refactor
- Prefer existing store mutation functions over ad hoc direct mutation from components

## Error Handling And Logging

- Backend JSON endpoints return a consistent success/failure payload
- Form handlers usually re-render the same template with an error message
- Model methods commonly return `false` for not-found/failure rather than throwing
- Frontend async code uses `try/catch`, `console.error`, and user-facing `notyf` messages where appropriate
- Avoid leaving new debug `console.log` calls in production paths
- Use `error_log()` for server-side diagnostics when needed

## Change Guidelines

- Keep edits scoped to the requested behavior
- Avoid backend schema or route changes unless the task requires them
- Do not alter generated files in `dist/` by hand; rebuild assets instead
- Do not commit secrets, `.env`, SQLite data, cache files, or local temporary files
- If a change touches autosave, public sheets, auth, imports, or print rendering, test the affected flow as directly as the local setup allows
