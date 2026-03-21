# Architecture

**Analysis Date:** 2026-03-21

## Pattern Overview

**Overall:** Server-rendered MPA with a single-page Vue.js app embedded in the sheet view

**Key Characteristics:**
- PHP backend handles routing, authentication, and HTML rendering via Fat-Free Framework templates
- The character sheet page embeds a full Vue.js 2 + Vuex SPA inside a server-rendered shell
- Dashboard and other pages use plain JavaScript (no Vue) with server-rendered HTML
- Character sheet data is stored as a JSON blob in SQLite, retrieved by the server and injected into the page as a JS global (`window.sheet`)

## Layers

**Routing Layer:**
- Purpose: Map HTTP requests to controller methods
- Location: `index.php`
- Contains: All route definitions (GET/POST/DELETE), global helper functions, F3 bootstrap
- Depends on: Fat-Free Framework (`\Base`)
- Used by: All incoming HTTP requests

**Controller Layer:**
- Purpose: Handle request/response lifecycle, enforce auth, pass data to templates
- Location: `classes/controllers/`
- Contains: `Authentication.php`, `Dashboard.php`, `Admin.php`
- Depends on: Model layer, `\Template` for rendering
- Used by: Router in `index.php`

**Model Layer:**
- Purpose: Database access via F3's Active Record ORM
- Location: `classes/models/`
- Contains: `User.php`, `Sheet.php`, `Token.php`
- Depends on: F3 `\DB\SQL\Mapper`, SQLite at `data/db.sqlite3`
- Used by: Controllers

**Template Layer:**
- Purpose: Server-side HTML rendering
- Location: `templates/`
- Contains: F3 HTML templates; `header.html`/`footer.html` included as partials in every page
- Depends on: F3 `\Template`, controller-set variables via `$f3->set()`
- Used by: Controllers calling `\Template::instance()->render()`

**Frontend App Layer:**
- Purpose: Interactive character sheet UI
- Location: `js/app.js`, `js/components/Sheet.vue` and `js/components/`
- Contains: Vue.js 2 SPA mounted at `#sheet`, Vuex store (`js/store.js`)
- Depends on: `window.sheet` global injected by PHP template, `/sheet/@slug` POST endpoint for saves
- Used by: `templates/sheet.html`

**Frontend Dashboard Layer:**
- Purpose: Vanilla JS interactions on the dashboard (toggling public state, deleting sheets)
- Location: `js/dashboard.js`
- Contains: Plain DOM manipulation, fetch calls to backend API endpoints
- Depends on: `#csrf` hidden input for CSRF token, data attributes on DOM elements

## Data Flow

**Loading a Character Sheet:**

1. User navigates to `/sheet/@sheet_slug`
2. `Dashboard->sheet_single()` authenticates user, loads sheet from SQLite via `Sheet->get_sheet_by_slug()`
3. Sheet data is JSON-encoded and assigned to `$f3->set('sheet', ...)` as an escaped string
4. `templates/sheet.html` renders, injecting sheet data into `window.sheet` global via `header.html`
5. `js/app.js` bootstraps Vue, mounts `Sheet.vue` at `#sheet`
6. `Sheet.vue::created()` dispatches `initializeState` Vuex action with `window.sheet`
7. Vuex store parses JSON and populates all character sheet state

**Auto-Saving a Character Sheet:**

1. Any Vuex mutation triggers the `autosave` event via `$store.subscribe()`
2. `Sheet.vue` schedules a throttled save (5s window, 1s trailing wait)
3. `saveSheetState()` dispatches `getJSON` to serialize the Vuex store
4. Fetch POST to `/sheet/@slug` with form-encoded `name` and `data`, CSRF token in `X-AJAX-CSRF` header
5. `Dashboard->save_sheet()` verifies CSRF, validates JSON, calls `Sheet->save_sheet()`
6. Response returns `{ success, csrf }` — new CSRF token is written to `#csrf` hidden input

**Public Sheet Polling:**

1. Public sheet is loaded read-only (`readOnly: true` in Vuex state)
2. `Sheet.vue::mounted()` detects public mode, starts `setInterval` every 30 seconds
3. Each tick calls `refreshLoop()` which fetches `/sheet-data/@slug?updated_at=...`
4. `Dashboard->get_sheet_data()` returns `null` if unchanged, or full sheet data if updated
5. On update, Vuex `updateState` action merges new data, `quill-refresh` event fires

**Authentication Flow:**

1. Session stored server-side in SQLite `sessions` table via `\DB\SQL\Session`
2. `Authentication::bounce()` redirects to `/login` if `SESSION.email` is not set
3. CSRF tokens are generated once per session, stored in `SESSION.csrf`, sent back with every response
4. AJAX requests send CSRF via `X-AJAX-CSRF` header; form submissions via `POST.csrf` field

**State Management:**

- All character data lives in Vuex store (`js/store.js`)
- Store state is flat — all sheet fields at top level (no modules)
- Mutations are explicit and named per field type (e.g., `updateBio`, `updateVitals`, `addAttack`)
- `replaceState` mutation is used for bulk initialization and polling updates

## Key Abstractions

**Sheet.vue (orchestrator):**
- Purpose: Root component for the character sheet SPA; owns save/load lifecycle, tab navigation
- Location: `js/components/Sheet.vue`
- Pattern: Coordinates child section components, watches Vuex mutations for autosave, manages retry logic

**Vuex Store:**
- Purpose: Single source of truth for all character data; serialized to JSON for saves
- Location: `js/store.js`
- Pattern: Flat state object, named mutations per field group, `initializeState` / `updateState` actions for hydration

**Sheet Model (PHP):**
- Purpose: Active Record for the `sheet` SQLite table; handles slug generation, JSON encode/decode
- Location: `classes/models/Sheet.php`
- Pattern: Extends `\DB\SQL\Mapper`; `decode_sheet_data()` handles legacy double-encoded JSON

**Authentication Controller:**
- Purpose: Handles all auth flows; provides `bounce()`, CSRF helpers, and email token dispatch
- Location: `classes/controllers/Authentication.php`
- Pattern: Instantiated by Dashboard at construction time; `bounce()` redirects unauthenticated users

**listMixin:**
- Purpose: Shared mixin for dynamic list fields (auto-appends empty item as user types)
- Location: `js/mixins.js`
- Used by: Components that manage growable lists of items

**i18n plugin:**
- Purpose: Translation via `t(key)` — looks up key in current locale, falls back to English, then key itself
- Location: `js/i18n.js`, locale files in `js/languages/`
- Pattern: Vue plugin adding `$t`, `$getLocale`, `$setLocale` to all components

## Entry Points

**PHP Application:**
- Location: `index.php`
- Triggers: Every HTTP request (Apache routes all requests here via `.htaccess`)
- Responsibilities: Bootstrap F3, load env, define all routes, register error handler, run router

**Character Sheet SPA:**
- Location: `js/app.js`
- Triggers: Loaded on `/sheet/@slug` pages via `templates/footer.html`
- Responsibilities: Register Vue plugins, create event bus (`window.sheetEvent`), initialize markdown-it, mount `Sheet.vue`

**Dashboard JS:**
- Location: `js/dashboard.js`
- Triggers: Loaded on `/dashboard` via `templates/footer.html`
- Responsibilities: Bind event handlers for public-toggle checkboxes and delete buttons

**Print SPA:**
- Location: `js/print.js`
- Triggers: Loaded on `/print/@slug` pages
- Responsibilities: Mount `Print.vue` at `#print`

## Error Handling

**Strategy:** Inline error responses with `$f3->status()` for API endpoints; template re-renders with `$f3->set('error_message', ...)` for form flows; custom error page for unhandled errors

**PHP Patterns:**
- JSON API endpoints return `{ success: false, reason: '...', status: NNN }`
- Form handlers re-render the same template with error messages set in F3 hive
- `$f3->set('ONERROR', ...)` renders `templates/error.html` for all unhandled errors
- Model methods return `false` on not-found or failure; controllers check return value

**JavaScript Patterns:**
- `Sheet.vue` implements exponential backoff retry (1s, 3s, 6s) for save failures
- Retryable: 5xx, 429, network errors, CSRF failures
- Non-retryable: 4xx client errors
- `notyf` toast library used for user-facing error/success notifications

## Cross-Cutting Concerns

**Logging:** `error_log()` calls in PHP for JSON decode failures and other anomalies; no structured logging

**Validation:** PHP controllers validate CSRF, JSON integrity, and ownership before writes; no schema validation library

**Authentication:** Session-based via F3's SQL session driver; email stored in `SESSION.email`; CSRF tokens refreshed in every response and stored in hidden `#csrf` input for JS reads

---

*Architecture analysis: 2026-03-21*
