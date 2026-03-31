# Testing Patterns

**Analysis Date:** 2026-03-21

## Test Framework

**Runner:**
- None — no test framework is installed or configured
- No `jest.config.*`, `vitest.config.*`, or `phpunit.xml` present
- No test dependencies in `package.json` or `composer.json`

**Assertion Library:**
- Not applicable

**Run Commands:**
```bash
# No test commands available
# package.json scripts: dev, watch, prod only
```

## Test File Organization

**Location:**
- No test files exist anywhere in the codebase

**Naming:**
- No `.test.*` or `.spec.*` files found

**Structure:**
- Not applicable

## Test Structure

**Suite Organization:**
- Not applicable — no tests exist

**Patterns:**
- None established

## Mocking

**Framework:**
- None

**Patterns:**
- Not applicable

## Fixtures and Factories

**Test Data:**
- `data/sheet.json` and `data/shared.json` exist as development data fixtures, not test fixtures
- No programmatic test factories

**Location:**
- No dedicated fixtures directory

## Coverage

**Requirements:** None enforced

**View Coverage:**
```bash
# No coverage tooling configured
```

## Test Types

**Unit Tests:**
- Not present

**Integration Tests:**
- Not present

**E2E Tests:**
- Not present

## Manual Testing Surface

While no automated tests exist, the following areas represent the testable surface of the application:

**Frontend (Vue.js):**
- `js/components/Sheet.vue` — autosave loop, retry logic with exponential backoff, save/error states
- `js/store.js` — all Vuex mutations (state shape transformations are pure functions suitable for unit testing)
- `js/utils.js` — `throttle`, `signedNumString`, `copyHtmlToClipboard` are pure/near-pure functions
- `js/i18n.js` — `t()`, `getLocale()`, `setLocale()` are pure functions against localStorage

**Backend (PHP):**
- `classes/models/Sheet.php` — `decode_sheet_data()` has double-encoding detection logic worth testing
- `classes/models/User.php` — authentication logic
- `classes/controllers/Authentication.php` — registration, login, CSRF validation flows
- `classes/controllers/Dashboard.php` — sheet CRUD, public/private toggle

## Notes for Adding Tests

If tests are introduced, the following patterns would integrate well:

**Recommended JS test runner:** Vitest (compatible with the existing Vite-adjacent toolchain, supports Vue 2 via `@vue/test-utils`)

**High-value pure functions to test first:**
- `js/utils.js`: `throttle()` and `signedNumString()` have no external dependencies
- `js/i18n.js`: `t()` function (mock localStorage)
- `js/store.js` Vuex mutations: each mutation is a function of `(state, payload)` — ideal for unit testing without mounting components

**Suggested test file placement:** Co-located with source, e.g., `js/utils.test.js`, `js/store.test.js`

**PHP testing:** PHPUnit would be the standard choice; Fat-Free Framework has no built-in test utilities

---

*Testing analysis: 2026-03-21*
