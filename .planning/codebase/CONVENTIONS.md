# Coding Conventions

**Analysis Date:** 2026-03-21

## Naming Patterns

**Files:**
- Vue components: PascalCase — `Sheet.vue`, `QuillEditor.vue`, `AddContentDialog.vue`
- Sub-directories for grouped components: `js/components/SearchResults/`
- Plain JS modules: camelCase — `store.js`, `utils.js`, `mixins.js`, `i18n.js`, `settings.js`
- PHP classes: PascalCase matching class name — `Authentication.php`, `Sheet.php`, `User.php`

**Vue component names (in `name` property):**
- PascalCase: `name: 'Sheet'`, `name: 'QuillEditor'`, `name: 'Field'`

**Component registration (local):**
- Lowercase kebab or single word for tag name: `'text-section': TextSection`, `'trackable-fields': TrackableFields`
- Single-word components use lowercase: `ability: Ability`, `tabs: Tabs`

**Functions/methods:**
- camelCase: `saveSheetState`, `autosaveLoop`, `resetRetryState`, `updateBio`, `toggleProficiency`
- PHP: snake_case — `create_sheet`, `save_sheet`, `get_sheet_by_slug`, `email_confirmation_token`

**Variables:**
- JS: camelCase — `sheetSlug`, `isPublic`, `formBody`
- PHP: snake_case — `$sheet_data`, `$error_message`, `$clear_token`
- `var` is used extensively in older JS code alongside `const`/`let` in newer code

**Vuex state properties:**
- camelCase for most: `characterName`, `readOnly`, `hitDie`
- Snake_case for DB-sourced flags: `is_2024`
- Spell level fields use abbreviated prefix: `lvl1Spells`, `lvl2Spells`, etc.
- Quill/rich-text fields end in `Text`: `equipmentText`, `featuresText`, `backstoryText`

**Props:**
- camelCase in component definition: `readOnly`, `autoSize`, `initialContents`
- kebab-case in templates: `:read-only`, `:auto-size`, `:initial-contents`

**Events (emitted):**
- kebab-case strings: `'update-value'`, `'quill-text-change'`, `'update-collapsed'`, `'manual-save'`

## Code Style

**Formatting:**
- Prettier — config at `.prettierrc`
- Single quotes for strings (`"singleQuote": true`)
- `prettier-plugin-tailwindcss` for automatic class sorting in templates
- No explicit line length or tab width configured (Prettier defaults apply)

**Linting:**
- No ESLint config present — no automated linting enforced

**Indentation:**
- PHP: 4 spaces (observed throughout `classes/`)
- JS/Vue: 2 spaces (Prettier default)

## Import Organization

**Order in Vue SFCs (`.vue` files):**
1. External packages — `import Vue from 'vue'`, `import { Notyf } from 'notyf'`
2. Vuex helpers — `import { mapState, mapGetters } from 'vuex'`
3. Local utils — `import { throttle } from '../utils'`
4. Component imports — `import Abilities from './Abilities'`

**Path style:**
- Relative paths for local imports — `'./components/Sheet'`, `'../utils'`, `'./languages/en.json'`
- No path aliases configured

**Vue plugin usage:**
- Plugins registered at app entry: `Vue.use(i18nPlugin)` in `js/app.js`
- Filters registered globally: `Vue.filter('signedNumString', signedNumString)`

## Error Handling

**JS/Vue patterns:**
- `try/catch` with `console.error()` for async operations (fetch, clipboard, store dispatch)
- Early return pattern for guard clauses: `if (this.isPublic) return;`
- Promise `.catch()` on store dispatches: `.catch((reason) => console.log(reason))`
- Error objects augmented with `status` property before throwing: `error.status = response.status`
- Retry logic with exponential backoff implemented manually in `Sheet.vue`
- User-facing errors shown via Notyf toast notifications

**PHP patterns:**
- Early return with error template render for validation failures
- `error_log()` for server-side logging — no structured logger
- Return `false` from model methods when record not found (dry mapper)
- No exceptions thrown; errors communicated via return values or F3 hive variables

## Logging

**Framework:** `console.error` / `console.log` (no logging library)

**Patterns:**
- `console.error` for caught exceptions and fetch failures
- `console.log` for debug output (some debug `console.log` statements left in production code — e.g., `js/store.js:352`, `js/components/Tabs.vue:239`)
- PHP: `error_log()` for server-side issues, written to web server error log

## i18n

**Pattern:**
- Template usage: `{{ $t('Key') }}` — key is the English string itself
- JS usage (outside components): `import { t } from './i18n'; t('Key')`
- Fallback chain: current locale → English → raw key
- Translation files: `js/languages/en.json`, `js/languages/de.json`

## Comments

**When to comment:**
- Step-numbered inline comments on complex multi-phase methods (see `saveSheetState` in `js/components/Sheet.vue`)
- Brief inline comments for non-obvious guard clauses
- JSDoc on exported utility functions in `js/i18n.js`
- PHP: short inline comments for business logic steps

**Style:**
- JSDoc used in `js/i18n.js` (`@param`, `@returns`)
- No JSDoc on Vue component methods or Vuex mutations

## Function Design

**Size:** Methods can be long when implementing complex workflows (e.g., `saveSheetState` in `Sheet.vue` is ~130 lines, heavily commented)

**Parameters:**
- Vuex mutations and actions receive a single `payload` object: `{ field, val, id }`
- Vue component methods receive event values directly from template `$event`

**Return values:**
- Async component methods return `true`/`false` for success/failure
- Vuex mutations return nothing (side-effect only)
- PHP model methods return associative array on success, `false` on failure

## Module Design

**Exports:**
- Named exports for utility functions: `export function throttle(...)`, `export const signedNumString`
- Default export for Vuex store: `export default new Vuex.Store({...})`
- Vue plugin exported as named const: `export const i18nPlugin`

**Barrel files:**
- None used — each module imported directly by path

## Vue Component Structure Order

Within `.vue` `<script>` blocks, the Options API object consistently follows:
1. `name`
2. `props`
3. `data()`
4. `computed`
5. `watch`
6. `methods`
7. `components`
8. Lifecycle hooks (`created`, `mounted`, `beforeDestroy`)

---

*Convention analysis: 2026-03-21*
