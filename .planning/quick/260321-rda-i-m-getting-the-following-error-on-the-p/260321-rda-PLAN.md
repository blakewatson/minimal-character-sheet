---
phase: quick
plan: 260321-rda
type: execute
wave: 1
depends_on: []
files_modified:
  - dist/assets/print-*.js
  - dist/assets/i18n-*.js
  - dist/.vite/manifest.json
autonomous: true
---

<objective>
Fix `TypeError: o.$t is not a function` on the print view.

Purpose: The committed dist files in `206bbca` were built from a stale version of `js/print.js` that predates commit `95d8d6e` (which added `app.use(i18nPlugin)` to the print entry point). The committed `print-w4aRwlnc.js` only calls `$.use(g)` (store) but is missing the second `.use()` call for the i18n plugin. The source code is already correct -- only the dist output needs rebuilding.

Root cause: The committed build artifact at `dist/assets/print-w4aRwlnc.js` ends with `$.use(g),$.config.globalProperties.$signedNumString=o,$.mount('#print')` -- only one `.use()` call (the store). A correct build produces `$.use(te),$.use(ne),...` -- two `.use()` calls (store + i18nPlugin). Without the i18n plugin installed, `$t` is never set on globalProperties, causing the TypeError when the Print component's render function calls `o.$t(...)`.

Output: Rebuilt dist files with i18n plugin properly included in the print bundle.
</objective>

<context>
@js/print.js (entry point -- already has app.use(i18nPlugin))
@js/i18n.js (i18n plugin that sets $t on globalProperties)
@vite.config.js (build config)
</context>

<tasks>

<task type="auto">
  <name>Task 1: Rebuild dist files and verify i18n plugin is in print bundle</name>
  <files>dist/assets/print-*.js, dist/assets/i18n-*.js, dist/.vite/manifest.json</files>
  <action>
    1. Run `npx vite build` to rebuild all dist assets from the current (correct) source.
    2. Verify the rebuilt print bundle contains TWO `.use()` calls (store + i18nPlugin) by inspecting the last line of the print JS asset. Specifically, confirm the pattern `$.use(X),$.use(Y)` appears (not just a single `.use()`).
    3. Verify `$t` appears in the i18n shared chunk's exports (grep for `globalProperties.$t` in the chunk).
    4. Stage and commit the rebuilt dist files.

    No source code changes needed -- `js/print.js` already correctly registers `app.use(i18nPlugin)`.
  </action>
  <verify>
    <automated>npx vite build && grep -c '\.use(' dist/assets/print-*.js | grep -v ':0' && grep 'globalProperties.\$t' dist/assets/i18n-*.js | head -1</automated>
  </verify>
  <done>The rebuilt print bundle includes both store and i18nPlugin registration. The `$t` function is available on all component instances in the print view, resolving the TypeError.</done>
</task>

</tasks>

<verification>
- `npx vite build` completes without errors
- The print bundle JS file contains two `.use()` calls (not one)
- The shared chunk exports the i18n install function that sets `globalProperties.$t`
</verification>

<success_criteria>
Print view loads without `TypeError: o.$t is not a function`. The i18n plugin is properly registered in the built print bundle, making `$t()` available to Print.vue and all child components.
</success_criteria>

<output>
After completion, update `.planning/quick/260321-rda-i-m-getting-the-following-error-on-the-p/260321-rda-SUMMARY.md`
</output>
