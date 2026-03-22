# Requirements: Vue 3 Migration

**Defined:** 2026-03-21
**Core Value:** The app must continue to work identically after migration — no regressions

## v1 Requirements

### Build Tool

- [x] **BUILD-01**: Replace Laravel Mix with Vite as build tool, outputting to `dist/`
- [x] **BUILD-02**: Create vite() PHP helper that reads `dist/.vite/manifest.json` for cache-busted asset paths
- [x] **BUILD-03**: Add `type="module"` to script tags in PHP templates
- [x] **BUILD-04**: Consolidate vendor CSS (Quill) via CSS @import instead of file copying
- [x] **BUILD-05**: Switch from @tailwindcss/postcss to @tailwindcss/vite plugin
- [x] **BUILD-06**: Remove Laravel Mix, webpack.mix.js, mix-manifest.json, and unused build dependencies
- [x] **BUILD-07**: Update package.json scripts (dev, watch, prod) for Vite

### Vue Framework

- [x] **VUE-01**: Upgrade Vue 2.6 to Vue 3.5 with createApp bootstrap
- [x] **VUE-02**: Upgrade Vuex 3 to Vuex 4 as intermediate bridge (createStore API)
- [x] **VUE-03**: Replace Vue instance event bus with mitt (emit/on/off pattern)
- [x] **VUE-04**: Update i18n plugin install signature for Vue 3 (app.config.globalProperties)
- [x] **VUE-05**: Replace all template filter syntax (| signedNumString) with function calls
- [x] **VUE-06**: Rename lifecycle hooks: beforeDestroy → beforeUnmount, destroyed → unmounted
- [x] **VUE-07**: Wrap Quill editor instance with markRaw() to prevent proxy interference
- [x] **VUE-08**: Remove all Vue.set() calls — Vue 3 proxy reactivity handles direct assignment
- [x] **VUE-09**: Audit and update v-model usage on custom components (value/input → modelValue/update:modelValue)
- [x] **VUE-10**: Update entry points (app.js, print.js) to use createApp pattern

### Store Migration

- [x] **STORE-01**: Rewrite Vuex store as reactive() composable with exported state, computed refs, and plain functions
- [x] **STORE-02**: Replace mapState in all components with computed properties referencing imported state
- [x] **STORE-03**: Replace mapGetters with imported computed refs (modifiers, proficiencyBonus)
- [x] **STORE-04**: Replace $store.commit calls with direct state assignments across all components
- [x] **STORE-05**: Replace $store.dispatch calls with direct function imports (getJSON, initializeState)
- [x] **STORE-06**: Replace $store.subscribe autosave with watch(state, ..., { deep: true })
- [x] **STORE-07**: Fix SpellList.vue computed property that generates random IDs (move to creation time)
- [x] **STORE-08**: Update listMixin to use direct array assignment instead of Vue.set
- [x] **STORE-09**: Handle dynamic state access patterns (state[this.listField]) in SpellGroup, SpellList, List components
- [x] **STORE-10**: Remove Vuex dependency entirely (npm uninstall, remove app.use(store))

### Cleanup

- [x] **CLEAN-01**: Delete webpack.mix.js, mix-manifest.json, and other Laravel Mix artifacts
- [x] **CLEAN-02**: Update CLAUDE.md with new build commands and architecture
- [x] **CLEAN-03**: Update .gitignore for Vite output (dist/.vite/)
- [ ] **CLEAN-04**: Verify all views work: sheet editing, print view, dashboard, public read-only sheets
- [ ] **CLEAN-05**: Verify autosave, Quill editors, skill/spell/attack list operations all function correctly

## v2 Requirements

### Optional Improvements

- **OPT-01**: Debounce autosave watcher for better performance
- **OPT-02**: Extract store helper functions for common component patterns
- **OPT-03**: Convert components from Options API to Composition API (future milestone)

## Out of Scope

| Feature | Reason |
|---------|--------|
| Composition API conversion | Migration scope — change store only, don't rewrite component structure |
| Pinia adoption | Store is a flat ~50-property bag; Pinia adds abstraction without benefit |
| SSR / Server-side rendering | PHP backend serves HTML; no need for Vue SSR |
| Vue Router | App uses PHP routing; no client-side routing needed |
| Quill editor replacement | Works fine, just needs markRaw() fix |
| @vue/compat migration build | Unnecessary for 26 components with well-understood breaking changes |
| New features | Pure migration — no new functionality |
| Backend changes | PHP models, controllers, routes stay as-is |
| Vite dev server / HMR | PHP serves the app; build-only mode is simpler |

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| BUILD-01 | Phase 1 | Complete |
| BUILD-02 | Phase 1 | Complete |
| BUILD-03 | Phase 1 | Complete |
| BUILD-04 | Phase 1 | Complete |
| BUILD-05 | Phase 1 | Complete |
| BUILD-06 | Phase 1 | Complete |
| BUILD-07 | Phase 1 | Complete |
| VUE-01 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-02 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-03 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-04 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-05 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-06 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-07 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-08 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-09 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| VUE-10 | Phase 1 (absorbed from Phase 2, per D-01) | Complete |
| STORE-01 | Phase 3 | Complete |
| STORE-02 | Phase 3 | Complete |
| STORE-03 | Phase 3 | Complete |
| STORE-04 | Phase 3 | Complete |
| STORE-05 | Phase 3 | Complete |
| STORE-06 | Phase 3 | Complete |
| STORE-07 | Phase 3 | Complete |
| STORE-08 | Phase 3 | Complete |
| STORE-09 | Phase 3 | Complete |
| STORE-10 | Phase 3 | Complete |
| CLEAN-01 | Phase 4 | Complete |
| CLEAN-02 | Phase 4 | Complete |
| CLEAN-03 | Phase 4 | Complete |
| CLEAN-04 | Phase 4 | Pending |
| CLEAN-05 | Phase 4 | Pending |

**Coverage:**
- v1 requirements: 32 total
- Mapped to phases: 32
- Unmapped: 0

---
*Requirements defined: 2026-03-21*
*Last updated: 2026-03-21 — VUE-01 through VUE-10 moved from Phase 2 to Phase 1 (D-01 triggered)*
