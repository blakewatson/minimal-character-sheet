# Feature Research: Vue 2 to Vue 3 Migration

**Domain:** Frontend framework migration (Vue 2 + Vuex + Laravel Mix to Vue 3 + reactive() + Vite)
**Researched:** 2026-03-21
**Confidence:** HIGH

## Feature Landscape

### Table Stakes (Must Do for a Correct Migration)

These are non-negotiable. Skipping any of these means the migration is incomplete or broken.

| Feature | Why Required | Complexity | Notes |
|---------|-------------|------------|-------|
| Replace `new Vue()` with `createApp()` | Vue 3 bootstrap API; old API does not exist | LOW | Affects `js/app.js` and `js/print.js` only |
| Replace `Vue.filter()` with function calls | Filters removed in Vue 3; templates won't compile | LOW | Only one filter (`signedNumString`); use `$filters.signedNumString()` via globalProperties |
| Replace event bus (`new Vue()` as emitter) | `$on`/`$off`/`$once` removed from Vue 3 instances | LOW | mitt is ~200 bytes, drop-in replacement. Create `js/emitter.js` |
| Update i18n plugin install signature | `Vue.prototype` replaced by `app.config.globalProperties` | LOW | One-line change in `js/i18n.js` |
| Rename lifecycle hooks | `beforeDestroy`/`destroyed` removed | LOW | Rename to `beforeUnmount`/`unmounted`. Known in `QuillEditor.vue` |
| Remove all `Vue.set()` / `$set()` calls | Vue 3 proxy reactivity handles direct assignment; `Vue.set` does not exist | LOW | 14 instances in `store.js`, 1 in `mixins.js`. Replace with direct assignment |
| Upgrade Vuex 3 to Vuex 4 (interim) | Vuex 3 incompatible with Vue 3; Vuex 4 is the bridge | LOW | Change `new Vuex.Store()` to `createStore()`, remove `Vue.use(Vuex)` |
| Replace Vuex with reactive() composable | Vuex is deprecated; reactive() is the right fit for this flat, simple store | HIGH | Rewrites `store.js` entirely. Touches all 26+ components to replace mapState/mapGetters/commit |
| Replace `$store.subscribe` with `watch()` | Vuex subscription API gone when Vuex removed | LOW | `watch(state, callback, { deep: true })` in `Sheet.vue` |
| Replace Laravel Mix with Vite | Laravel Mix is unmaintained; Vite is the standard build tool | MEDIUM | New `vite.config.js`, update npm scripts, update PHP manifest helper |
| Replace PHP `mix()` helper with `vite()` helper | Vite manifest format differs from Mix manifest | LOW | Different JSON structure: Vite uses `dist/.vite/manifest.json` with `{ "file": "assets/app-hash.js" }` |
| Update template asset references | All `mix()` calls in PHP templates must become `vite()` | LOW | Search `templates/` for `mix(` calls |
| Handle `v-model` changes on components | Vue 3 changes `v-model` prop from `value` to `modelValue`, event from `input` to `update:modelValue` | MEDIUM | Audit all components using `v-model`; `Field.vue` is the primary candidate |
| Remove vue-template-compiler | Vue 2-only package; Vue 3 compiles via `@vitejs/plugin-vue` | LOW | Just uninstall it |
| Handle Quill CSS bundling | Mix manually copied quill CSS; Vite needs an import or copy plugin | LOW | Import `quill/dist/quill.bubble.css` in `css/app.css` |

### Differentiators (Nice-to-Have Improvements During Migration)

These are not required for correctness but represent opportunities to improve the codebase while already touching every file.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| Add `emits` option declarations | Vue 3 best practice for documenting component contracts; improves tooling and warnings | LOW | Add `emits: ['update:modelValue', ...]` to components that emit events. Mechanical but not blocking |
| Use `<script setup>` syntax | Reduces boilerplate, better performance (compile-time optimizations) | HIGH | Requires rewriting component `<script>` blocks. Explicitly out of scope per PROJECT.md (keeping Options API) |
| Migrate to Pinia instead of reactive() | DevTools integration, official recommendation for larger apps | MEDIUM | Overkill for this app. Store is a flat ~50-property bag with 2 getters. reactive() is the right call. Pinia adds unnecessary abstraction |
| TypeScript adoption | Better type safety, IDE support | HIGH | Orthogonal to migration. Do it later if desired, not during migration |
| Use Vite dev server with PHP proxy | HMR for faster development iteration | MEDIUM | Requires configuring Vite proxy to PHP backend. Decided against in PROJECT.md -- complexity not worth it for this project size |
| Centralize mutation logic in store functions | Vue docs recommend methods on the store rather than direct state mutation from components | LOW | Good practice but the existing mutations are trivial setters. Direct assignment is fine for this codebase |
| Add Vue DevTools support labels | Name stores/components for better DevTools experience | LOW | Minor DX improvement. Can add later |

### Anti-Features (Do NOT Do During Migration)

These are tempting but will derail the migration, increase risk, or conflict with the stated scope.

| Feature | Why Tempting | Why Problematic | Alternative |
|---------|-------------|-----------------|-------------|
| Composition API conversion | "While we're touching every component anyway..." | Doubles the scope. Options API works fine in Vue 3. Mixing concerns (migration + refactor) increases bug surface | Keep Options API. Convert individual components later if desired |
| Full Pinia adoption | "It's the official recommendation" | Store is trivially simple (~50 flat properties, 2 getters, no modules). Pinia adds indirection without benefit. reactive() is explicitly endorsed by Vue docs for simple stores | Use reactive() composable. Migrate to Pinia later only if store complexity grows |
| SSR support | "Vue 3 makes SSR easier" | App is PHP-rendered with client-side Vue. No SSR need exists. Adding SSR changes the entire architecture | Keep PHP server rendering. Vue handles client-side interactivity only |
| Add new features | "Since we're rebuilding the frontend..." | Migration goal is zero feature change. New features during migration make it impossible to verify correctness (can't diff behavior) | Complete migration first, verify parity, then add features in a separate milestone |
| Vue Router integration | "Should we add client-side routing?" | App uses PHP routing with separate entry points (app, dashboard, print). Adding Vue Router changes the architecture fundamentally | Keep PHP-routed multi-entry-point architecture |
| Replace Quill with TipTap/ProseMirror | "Quill is old, newer editors exist" | Quill works. Replacing it during migration adds an untested variable. If Quill has Vue 3 issues, fix them -- don't swap the editor | Keep Quill. Test carefully for DOM manipulation conflicts with Vue 3's proxy reactivity |
| Use @vue/compat migration build | "Incremental migration is safer" | Adds complexity for a small app. The codebase is 26 components with a flat store -- the breaking changes are well-understood and mechanical. Compat build is designed for large apps where you can't migrate everything at once | Migrate directly. The app is small enough for a clean cutover |

## Feature Dependencies

```
[Vite build setup]
    |
    +--requires--> [PHP vite() helper]
    |                  |
    |                  +--requires--> [Template asset reference updates]
    |
    +--enables--> [Vue 3 upgrade]
                      |
                      +--requires--> [createApp() bootstrap]
                      +--requires--> [Event bus replacement (mitt)]
                      +--requires--> [i18n plugin update]
                      +--requires--> [Filter removal]
                      +--requires--> [Lifecycle hook renames]
                      +--requires--> [Vue.set() removal]
                      +--requires--> [v-model prop/event changes]
                      +--requires--> [Vuex 3 -> 4 upgrade]
                      |
                      +--enables--> [Vuex -> reactive() replacement]
                                        |
                                        +--requires--> [Store rewrite]
                                        +--requires--> [Component store access updates (all 26)]
                                        +--requires--> [Autosave watch() replacement]
                                        +--requires--> [Vuex uninstall]
```

### Dependency Notes

- **Vite setup must come first:** It changes how assets are built and loaded. Getting this working before touching Vue code means you have a known-good build pipeline for all subsequent changes.
- **Vue 3 upgrade requires Vuex 4 as a bridge:** You cannot run Vuex 3 on Vue 3. Upgrading Vuex to v4 temporarily keeps the store working while you migrate components to Vue 3 patterns.
- **Vuex removal depends on Vue 3 being stable:** The reactive() composable uses Vue 3's `reactive()`, `computed()`, and `watch()` APIs. These must be available (Vue 3 installed) before the store can be rewritten.
- **Component updates are the long tail:** Every component touches the store. This is the highest-volume work but is mechanical -- the same pattern repeated 26 times.
- **Event bus is independent of store migration:** mitt can be introduced at any point during the Vue 3 upgrade. It has no dependency on store architecture.

## MVP Definition

### Phase 1: Build Tool Migration (Vite)

- [x] Replace Laravel Mix with Vite -- build pipeline must work before touching framework code
- [x] Create `vite.config.js` with 3 JS entry points + CSS
- [x] Replace PHP `mix()` helper with `vite()` helper
- [x] Update all template asset references
- [x] Handle Quill CSS bundling
- [x] Remove webpack/Mix dependencies
- [x] Verify: app works identically with new build

### Phase 2: Vue 3 Upgrade (with Vuex 4 bridge)

- [x] Upgrade vue@3, vuex@4, install mitt
- [x] Rewrite `createApp()` bootstrap in entry points
- [x] Replace event bus with mitt
- [x] Update i18n plugin
- [x] Remove filters, replace with function calls
- [x] Rename lifecycle hooks
- [x] Remove all `Vue.set()` calls
- [x] Audit and fix v-model usage on custom components
- [x] Upgrade Vuex 3 to 4 (`createStore()`)
- [x] Verify: full app works on Vue 3

### Phase 3: Store Migration (Vuex to reactive())

- [x] Rewrite `js/store.js` as reactive() composable with computed getters
- [x] Update all 26 components: replace mapState/mapGetters/commit with direct state access
- [x] Replace `$store.subscribe` with `watch(state, ..., { deep: true })`
- [x] Replace `$store.dispatch` with direct function calls
- [x] Update `listMixin` in `mixins.js`
- [x] Uninstall Vuex
- [x] Verify: full app works without Vuex

### Phase 4: Cleanup

- [x] Delete `webpack.mix.js`, old manifests
- [x] Update `CLAUDE.md` and documentation
- [x] Full regression test: edit, autosave, print, public sheets, dashboard

## Feature Prioritization Matrix

| Feature | Migration Value | Implementation Cost | Priority |
|---------|----------------|---------------------|----------|
| Vite build setup | HIGH (unblocks everything) | MEDIUM | P1 |
| PHP manifest helper | HIGH (app won't load without it) | LOW | P1 |
| createApp() bootstrap | HIGH (Vue 3 won't run without it) | LOW | P1 |
| Event bus (mitt) | HIGH (runtime errors without it) | LOW | P1 |
| Vue.set() removal | HIGH (does not exist in Vue 3) | LOW | P1 |
| Filter removal | HIGH (templates won't compile) | LOW | P1 |
| i18n plugin update | HIGH (runtime errors without it) | LOW | P1 |
| Lifecycle hook renames | HIGH (warnings, eventual breakage) | LOW | P1 |
| Vuex 3 to 4 upgrade | HIGH (bridge step) | LOW | P1 |
| v-model component audit | MEDIUM (may cause silent bugs) | MEDIUM | P1 |
| Store rewrite (reactive()) | HIGH (removes deprecated dependency) | HIGH | P1 |
| Component store updates (x26) | HIGH (required for store rewrite) | HIGH | P1 |
| Autosave watch() | HIGH (data loss without autosave) | LOW | P1 |
| `emits` option declarations | LOW (DX improvement) | LOW | P2 |
| Centralize store mutations | LOW (code quality) | LOW | P3 |

**Priority key:**
- P1: Required for migration correctness -- the app is broken without it
- P2: Should do during migration for code quality, but not blocking
- P3: Can be deferred to a future pass

## Sources

- [Vue 3 Migration Guide - Breaking Changes](https://v3-migration.vuejs.org/breaking-changes/)
- [Vue.js Official - State Management](https://vuejs.org/guide/scaling-up/state-management.html)
- [Pinia - Migrating from Vuex](https://pinia.vuejs.org/cookbook/migration-vuex.html)
- [Vue FAQ - State management in Vue 3](https://vue-faq.org/en/development/stores.html)
- [HeroDevs - What It Really Takes to Migrate from Vue 2 to Vue 3](https://www.herodevs.com/blog-posts/what-it-really-takes-to-migrate-from-vue-2-to-vue-3)
- [Monterail - Vue 3 Migration Case Study](https://www.monterail.com/blog/vue-3-migration-case-study)

---
*Feature research for: Vue 2 to Vue 3 Migration*
*Researched: 2026-03-21*
