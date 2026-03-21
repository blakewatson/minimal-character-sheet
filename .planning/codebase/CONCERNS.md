# Codebase Concerns

**Analysis Date:** 2026-03-21

## Tech Debt

**Vue 2 / Vuex 3 / Laravel Mix — Active Migration Pending:**
- Issue: Frontend stack is Vue 2.6 + Vuex 3 + Laravel Mix (webpack). All three are EOL or superseded. A 4-phase migration plan to Vue 3 + Vite + `reactive()` composable exists but is not started.
- Files: `js/app.js`, `js/print.js`, `js/store.js`, `js/mixins.js`, `js/i18n.js`, all 29 `.vue` files in `js/components/`
- Impact: Vue 2 receives no security updates. webpack via Laravel Mix is significantly slower than Vite. Migration has to happen in one coordinated push across all component files.
- Fix approach: Follow `plans/vue3-migration.md` — four phases: (1) Vite, (2) Vue 3, (3) replace Vuex with `reactive()`, (4) cleanup.

**`Vue.prototype` i18n Plugin — Vue 2 Only API:**
- Issue: `js/i18n.js` installs via `Vue.prototype.$t = t`. This API does not exist in Vue 3; the plugin must use `app.config.globalProperties` instead.
- Files: `js/i18n.js` (line 37)
- Impact: Will throw a runtime error the moment Vue 3 is installed.
- Fix approach: Change `install(Vue)` to `install(app)` and replace `Vue.prototype.$t` with `app.config.globalProperties.$t`.

**`Vue.set()` in Store and Mixins — Vue 2 Only API:**
- Issue: `js/store.js` contains 14 `Vue.set()` calls for reactive array/object assignment. `js/mixins.js` has 1 call on line 13. `Vue.set` does not exist in Vue 3.
- Files: `js/store.js` (mutations: `updateAbilityScore`, `updateSkillProficiency`, `updateSavingThrow`, `updateSpellName`, `updateSpellPrepared`, `updateSpellCollapsed`, `updateSpellSlots`, `updateExpendedSlots`, `updateListField`, `updateCoins`), `js/mixins.js` (line 13)
- Impact: All of these break silently or throw on Vue 3.
- Fix approach: Replace all `Vue.set(obj, key, val)` with direct assignment `obj[key] = val` — Vue 3's Proxy-based reactivity tracks these automatically.

**`$store.subscribe` Event Bus Pattern — Tightly Coupled:**
- Issue: `Sheet.vue` uses `window.sheetEvent = new Vue()` as an event bus and wires autosave via `this.$store.subscribe()`. Both patterns are removed in Vue 3 (the event bus requires `mitt`, and `subscribe` requires a deep `watch`).
- Files: `js/app.js` (line 10), `js/components/Sheet.vue` (lines 138–147), `js/components/QuillEditor.vue` (lines 192–199), `js/store.js` (line 736)
- Impact: App won't initialize or save in Vue 3 without these being replaced.
- Fix approach: Create `js/emitter.js` with `mitt`. Replace `$emit`/`$on`/`$off` with `emit`/`on`/`off`. Replace `$store.subscribe` with `watch(state, ..., { deep: true })`.

**`beforeDestroy` Lifecycle Hook — Renamed in Vue 3:**
- Issue: `js/components/QuillEditor.vue` (line 196) and `js/components/Sheet.vue` (line 422) use `beforeDestroy`. Vue 3 renamed this to `beforeUnmount`.
- Files: `js/components/QuillEditor.vue`, `js/components/Sheet.vue`
- Impact: Cleanup callbacks (removing event listeners) will not run in Vue 3, causing memory leaks.
- Fix approach: Rename `beforeDestroy` to `beforeUnmount` in both files.

**Template Filter Syntax — Removed in Vue 3:**
- Issue: Vue 2 pipe filter syntax `{{ value | signedNumString }}` is removed in Vue 3 and will cause compile errors.
- Files: Any component using `| signedNumString` — search `js/components/` for this pattern.
- Impact: Template compilation will fail on any component using filters.
- Fix approach: Convert to function calls `{{ $filters.signedNumString(value) }}` or import and call directly.

**`objectIsEmpty` Utility — Dead Code:**
- Issue: `js/store.js` defines `objectIsEmpty` (lines 741–749) at module scope but it is never called anywhere in the codebase.
- Files: `js/store.js` (lines 741–749)
- Impact: Low — just dead code adding confusion.
- Fix approach: Remove the function.

**Commented-Out XP Getters:**
- Issue: `js/store.js` has two Vuex getters (`getLevelByXp`, `getXpByLevel`) commented out (lines 239–251). This represents abandoned feature work.
- Files: `js/store.js`
- Impact: Low — dead commented code adds noise.
- Fix approach: Delete the commented block or implement the feature.

**`is_compact` Column — Dead Database Column:**
- Issue: Migration `migrations/002.php` adds an `is_compact` column to the `sheet` table, but no controller, model, or frontend code reads or writes this column.
- Files: `migrations/002.php`
- Impact: Low — orphaned schema column.
- Fix approach: Drop the column or implement the feature.

**`deleted_at` Soft Delete Column — Never Used:**
- Issue: Migration `migrations/004.php` adds a `deleted_at` column intended for soft deletes, but `Sheet::delete_sheet()` calls `$this->erase()` (hard delete) and no query filters by `deleted_at IS NULL`.
- Files: `migrations/004.php`, `classes/models/Sheet.php` (line 153–158)
- Impact: Medium — soft delete column exists but deletion is still hard. If soft deletes were intended for data recovery, the feature is incomplete.
- Fix approach: Either implement soft delete (filter queries by `deleted_at IS NULL`, set `deleted_at` on delete) or remove the column and the admin restore feature's assumption.

**ETL Scripts Left in Web Root:**
- Issue: `etl/sheets.php` and `etl/users.php` are one-time data import scripts left in the web root. An `.htaccess` in `etl/` presumably blocks access, but their presence is a maintenance risk.
- Files: `etl/sheets.php`, `etl/users.php`, `etl/.htaccess`
- Impact: Low — operational risk if `.htaccess` is misconfigured.
- Fix approach: Move these to a non-web-accessible location (e.g., a `scripts/` directory outside the document root) or delete them if no longer needed.

**Dropbox-Conflicted SQLite File in Repository:**
- Issue: `data/db (Blake Watson's conflicted copy 2025-12-26).sqlite3` is a Dropbox conflict file sitting in the `data/` directory alongside the production database.
- Files: `data/db (Blake Watson's conflicted copy 2025-12-26).sqlite3`
- Impact: Medium — indicates the database is synced via Dropbox, which is a fragile and potentially data-corrupting setup for a live SQLite file.
- Fix approach: Delete the conflict file. Consider moving the SQLite database out of a cloud-synced folder for production deployments.

---

## Security Considerations

**`make_sheet_public` — Missing `return` After 403 Response:**
- Risk: In `Dashboard::make_sheet_public()`, when the session email does not match the sheet owner, the code sets a 403 status and echoes a JSON error — but does NOT `return`. Execution falls through to `$sheetObj->save()` and the success response, allowing any authenticated user to toggle the `is_public` flag on any sheet.
- Files: `classes/controllers/Dashboard.php` (lines 257–266)
- Current mitigation: None — this is an active authorization bypass bug.
- Recommendations: Add `return;` immediately after the `echo json_encode(...)` on line 259.

**`rand()` Used for Slug Generation — Not Cryptographically Secure:**
- Risk: `Sheet::random_slug()` generates 10-character slugs using `rand(0, 51)`. PHP's `rand()` is not cryptographically secure and produces predictable sequences.
- Files: `classes/models/Sheet.php` (lines 183–192)
- Current mitigation: Slugs are checked for uniqueness before use, but they remain guessable.
- Recommendations: Replace `rand()` with `random_int()` which uses a CSPRNG. Also consider increasing slug length from 10 to 16+ characters.

**No Rate Limiting on Authentication Endpoints:**
- Risk: The login (`POST /login`), registration (`POST /register`), and password reset (`POST /request-password-reset`) endpoints have no rate limiting, throttling, or account lockout. Brute-force attacks on passwords and token enumeration are unrestricted.
- Files: `index.php` (route definitions), `classes/controllers/Authentication.php`
- Current mitigation: Honeypot fields and CSRF tokens slow automated form submissions, but they do not limit repeated requests.
- Recommendations: Implement request rate limiting at the web server (nginx/Apache) or application level (e.g., track failed login attempts per IP/email in the database).

**No Email Validation on Registration:**
- Risk: `Authentication::register()` passes the POST email directly to `User::create()` without validating format. Any string is accepted.
- Files: `classes/controllers/Authentication.php` (line 49), `classes/models/User.php` (line 14)
- Current mitigation: None.
- Recommendations: Add `filter_var($email, FILTER_VALIDATE_EMAIL)` before creating the user.

**No Password Minimum Length on Registration or Reset:**
- Risk: There is no minimum password length check during registration or password reset. Empty passwords or single-character passwords are accepted.
- Files: `classes/controllers/Authentication.php` (lines 40–45, 310–316)
- Current mitigation: None.
- Recommendations: Add a minimum length check (e.g., `strlen($pw1) < 8`) before hashing.

**Sheet Data Injected as Raw JSON via `addslashes` — Fragile XSS Defense:**
- Risk: Sheet data and character name are injected into `<script>` globals using `addslashes(json_encode(...))`. The `| raw` filter in `templates/footer.html` and `templates/print.html` disables F3's auto-escaping. `addslashes` is not a robust XSS defense for `<script>` context injection — a carefully crafted character name with `</script>` can break out.
- Files: `classes/controllers/Dashboard.php` (lines 105–107, 302–304), `templates/footer.html` (lines 5–7), `templates/print.html` (lines 42–44)
- Current mitigation: Partial — `addslashes` helps but is not sufficient for script context.
- Recommendations: Use `json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)` to produce HTML-safe JSON that cannot break out of a `<script>` block.

**`v-html` Used with User-Controlled Content in Print View:**
- Risk: `js/components/Print.vue` uses `v-html` for `weaponNotes` (line 210) and `notes` (line 242) fields, which are stored user-authored content. If a user embeds HTML/script in these fields, it renders unescaped in the print view.
- Files: `js/components/Print.vue` (lines 210, 242)
- Current mitigation: Content is user's own data (read their own sheet), but public sheets share content with any viewer.
- Recommendations: Sanitize these fields with DOMPurify before passing to `v-html`, or switch to text interpolation.

**Session Fixation — No Session Regeneration on Login:**
- Risk: `Authentication::login()` sets `SESSION.email` but does not regenerate the session ID. An attacker who can set a victim's session cookie can authenticate as them.
- Files: `classes/controllers/Authentication.php` (line 189)
- Current mitigation: F3's session handler provides some protection, but explicit regeneration is missing.
- Recommendations: Call `session_regenerate_id(true)` after a successful login.

**Admin Endpoints Have No CSRF Protection on GET:**
- Risk: Admin pages (`/admin`, `/admin/users`) are protected by auth and admin-role checks but use GET requests to display sensitive user data. While read-only, they're also not behind any additional auth layer.
- Files: `classes/controllers/Admin.php`, `index.php` (lines 72–74)
- Current mitigation: Admin role check is in place.
- Recommendations: Low risk as-is, but consider adding 2FA or IP restriction for admin routes in the future.

---

## Performance Bottlenecks

**Public Sheet Polling — Fixed 30-Second Interval with No Back-Off:**
- Problem: Public read-only sheets call `refreshLoop()` every 30 seconds indefinitely via `setInterval`, even if the browser tab is hidden or the user is inactive.
- Files: `js/components/Sheet.vue` (lines 394–397, 340–357)
- Cause: `setInterval` fires regardless of page visibility; no `visibilitychange` event listener pauses it.
- Improvement path: Add a `document.addEventListener('visibilitychange', ...)` handler to pause the interval when the tab is hidden, resume when visible.

**Full Vuex State Serialized on Every Mutation for Autosave:**
- Problem: `Sheet.vue` subscribes to every Vuex store mutation. Each mutation triggers `throttledSave`, which calls `JSON.stringify(state)` on the entire store. The store is ~50 fields including large Quill delta objects.
- Files: `js/components/Sheet.vue` (lines 138–147), `js/store.js` (action `getJSON`)
- Cause: No dirty-checking; the entire state is serialized on every keystroke (after throttle delay).
- Improvement path: Acceptable at current data sizes. When migrating to Vue 3 `reactive()`, using a deep `watch` with debounce is equivalent and no worse.

**`get_all_sheets` Decodes Sheet JSON for Every Sheet on Dashboard:**
- Problem: `Sheet::get_all_sheets()` calls `decode_sheet_data()` on every sheet's `data` field. The dashboard only needs metadata (name, slug, is_public, is_2024) — decoding full character sheet JSON for every row is wasteful.
- Files: `classes/models/Sheet.php` (lines 102–122)
- Cause: The `decode_sheet_data()` call is in the loop without a way to skip it.
- Improvement path: Add a `$include_data` parameter to `get_all_sheets()` and skip JSON decoding for dashboard list views.

---

## Fragile Areas

**`make_sheet_public` — Authorization Bypass (See Security Above):**
- Files: `classes/controllers/Dashboard.php` (lines 244–267)
- Why fragile: Missing `return` after 403 means subsequent `$sheetObj->save()` always runs.
- Safe modification: Add `return;` after line 259. Do not add more code below the authorization check without also adding a return.
- Test coverage: None.

**`decode_sheet_data` — Double-Encoding Detection:**
- Files: `classes/models/Sheet.php` (lines 160–181)
- Why fragile: Contains legacy double-decode detection (`if (is_string($decoded))`). This workaround silently re-decodes data and logs a warning. It exists because old data was stored double-encoded; the fix path for older records is not enforced.
- Safe modification: Do not change JSON encoding/decoding without understanding which legacy sheets still have double-encoded data. Any refactor should preserve the double-decode fallback until all data is confirmed clean.
- Test coverage: None.

**`Sheet::random_slug()` — Character Bank Off-By-One Risk:**
- Files: `classes/models/Sheet.php` (lines 183–192)
- Why fragile: The character bank string `'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'` has 52 characters (indices 0–51), and `rand(0, 51)` is correct. But the magic number `51` must be manually kept in sync with the bank string length — easy to break if the bank is changed.
- Safe modification: Replace `rand(0, 51)` with `rand(0, strlen($bank) - 1)` and switch to `random_int()`.

**`initializeState` — Manual Field Migration in JavaScript:**
- Files: `js/store.js` (lines 624–694, action `initializeState`)
- Why fragile: The action manually patches missing fields on loaded data (e.g., adding `weaponNotes`, `collapsed`, `notes` to old records). Every time a new field is added to the state shape, a matching migration check must be added here. Missing one causes `undefined` errors for old sheets.
- Safe modification: When adding a new state field, always add a corresponding `if (!field.hasOwnProperty('newField'))` migration check in `initializeState`.
- Test coverage: None.

**`window.sheet` Global Injection — Implicit Coupling:**
- Files: `templates/footer.html` (line 5), `js/components/Sheet.vue` (line 389, `JSON.parse(sheet)` in `mounted`), `js/store.js` (action `initializeState` receives `window.sheet`)
- Why fragile: The PHP backend injects sheet data as a JavaScript global string (`window.sheet = "..."`). The Vue component reads it in `mounted()` as a bare `sheet` reference (not `window.sheet`). This works because unqualified globals resolve to `window`, but it's an implicit dependency. If the template changes the variable name, the JS silently receives `undefined`.
- Safe modification: Change `JSON.parse(sheet)` in `Sheet.vue` to `JSON.parse(window.sheet)` for explicit reference. Consider using a `<meta>` tag or data attribute instead of a global script injection.

---

## Test Coverage Gaps

**No Tests Exist:**
- What's not tested: The entire application — all PHP controllers, models, and all Vue components.
- Files: All files in `classes/`, all files in `js/components/`, `js/store.js`
- Risk: Any refactor (especially the Vue 3 migration) can introduce regressions with no safety net. The `make_sheet_public` authorization bug described above would have been caught by a basic controller test.
- Priority: High — especially before executing the Vue 3 migration.

**Authorization Logic Untested:**
- What's not tested: `Dashboard::save_sheet()`, `Dashboard::delete_sheet()`, `Dashboard::make_sheet_public()` ownership checks; `Authentication::verify_csrf()` and `verify_ajax_csrf()`.
- Files: `classes/controllers/Dashboard.php`, `classes/controllers/Authentication.php`
- Risk: Authorization bypass (confirmed in `make_sheet_public`) goes undetected.
- Priority: High.

**State Migration Logic Untested:**
- What's not tested: `initializeState` and `updateState` actions in `js/store.js`, especially the field-backfill migration logic.
- Files: `js/store.js` (lines 624–737)
- Risk: Old sheet formats silently corrupt or lose data on load.
- Priority: Medium.

---

## Leftover Debug Code

**`console.log` Statements in Production Code:**
- Files:
  - `js/store.js` line 352: `console.log('setting modifier override', payload.modifierOverride)` — logs on every skill modifier override change.
  - `js/components/Tabs.vue` line 239: `console.log('isRetrying', newVal)` — logs on every retry state change.
  - `js/components/AddContentDialog.vue` line 273: `console.log('no results')` — logs on empty search.
  - `js/components/SearchResults/SpellDetails.vue` line 128: `console.log('No spell provided')`.
  - `js/home.js` line 4: `console.log('baguetteBox is defined')`.
- Impact: Low — noise in browser devtools and minor info leakage.
- Fix approach: Remove all `console.log` calls before production builds. Consider using a build-time strip plugin or lint rule.

**`error_log('Deleting sheet ' . $id)` in Production Path:**
- Files: `classes/models/Sheet.php` (line 154)
- Impact: Writes to server error log on every sheet deletion — normal operations pollute error logs, making real errors harder to spot.
- Fix approach: Remove this `error_log` call or move it behind a debug flag.

---

*Concerns audit: 2026-03-21*
