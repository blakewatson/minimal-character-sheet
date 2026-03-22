# Roadmap: Vue 3 Migration

## Overview

This migration moves the Minimal Character Sheet frontend from Vue 2 + Vuex + Laravel Mix to Vue 3 + reactive() composable + Vite, with zero feature changes. The phases follow strict dependency ordering: build tool first with Vue 3 upgrade (merged due to Vite 8 plugin incompatibility), then store replacement, then cleanup and verification. Each phase produces a working, testable application.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [ ] **Phase 1: Build Tool Migration + Vue 3 Upgrade** - Replace Laravel Mix with Vite, upgrade to Vue 3 with Vuex 4, fix all breaking API changes
- [x] ~~**Phase 2: Vue 3 Framework Upgrade**~~ - **ABSORBED into Phase 1** (D-01: @vitejs/plugin-vue2 incompatible with Vite 8, so Vue 3 upgrade must happen in same phase as build tool migration)
- [ ] **Phase 3: Store Migration** - Replace Vuex with reactive() composable, update all 18 components
- [ ] **Phase 4: Cleanup and Verification** - Remove dead artifacts, update docs, verify full feature parity

## Phase Details

### Phase 1: Build Tool Migration + Vue 3 Upgrade
**Goal**: The app builds and runs identically using Vite instead of Laravel Mix, with Vue 3 + Vuex 4 and all breaking API changes resolved
**Depends on**: Nothing (first phase)
**Requirements**: BUILD-01, BUILD-02, BUILD-03, BUILD-04, BUILD-05, BUILD-06, BUILD-07, VUE-01, VUE-02, VUE-03, VUE-04, VUE-05, VUE-06, VUE-07, VUE-08, VUE-09, VUE-10
**Success Criteria** (what must be TRUE):
  1. Running `npm run dev` and `npm run prod` produces compiled assets in `dist/` via Vite
  2. PHP templates load JS and CSS assets through the vite() helper reading `dist/.vite/manifest.json`
  3. The character sheet app loads and functions in the browser with no console errors
  4. Laravel Mix, webpack.mix.js, and webpack-related npm dependencies are removed from the project
  5. All 26 Vue components compile and render under Vue 3 with no console errors or warnings
  6. The Quill rich text editor loads, accepts input, and saves content without breaking (markRaw applied)
  7. Event-driven features (autosave triggers, cross-component communication) work via mitt instead of Vue instance event bus
  8. Template filters replaced with function calls -- ability modifiers display signed numbers correctly
  9. All entry points (app.js, print.js) bootstrap via createApp and the app functions end-to-end
**Plans**: 4 plans

Plans:
- [x] 01-01-PLAN.md — Install Vite, create config, set up public/ dir, consolidate CSS, remove Mix deps
- [x] 01-02-PLAN.md — Replace PHP mix() helper with vite(), update templates, delete Mix artifacts
- [x] 01-03-PLAN.md — Migrate store, entry points, i18n, event bus, and mixins to Vue 3 API
- [x] 01-04-PLAN.md — Fix component lifecycle hooks, markRaw, filter syntax, verify full build

### ~~Phase 2: Vue 3 Framework Upgrade~~ (ABSORBED into Phase 1)

> **Note:** This phase was merged into Phase 1 per decision D-01. The `@vitejs/plugin-vue2` package is incompatible with Vite 8 (supports only Vite 3-7) and requires Vue ^2.7.0-0 (project uses 2.6.7). Per user decision, the Vue 3 upgrade happens alongside the build tool migration rather than using an older Vite version or community plugins.
>
> All VUE-01 through VUE-10 requirements are covered by Phase 1 plans 01-03 and 01-04.

### Phase 3: Store Migration
**Goal**: Vuex is fully removed and replaced with a reactive() composable; all components import state directly and autosave works via watch()
**Depends on**: Phase 1
**Requirements**: STORE-01, STORE-02, STORE-03, STORE-04, STORE-05, STORE-06, STORE-07, STORE-08, STORE-09, STORE-10
**Success Criteria** (what must be TRUE):
  1. The store is a single reactive() object with computed refs for derived values -- no Vuex dependency in package.json
  2. All 18 components that access the store read and write state via direct imports (no mapState, mapGetters, $store.commit, or $store.dispatch)
  3. Autosave fires correctly after edits -- exactly one save per change, no infinite loops
  4. List operations (add/remove spells, attacks, equipment) work correctly with stable IDs (no random ID generation in computed properties)
  5. Dynamic state access patterns in SpellGroup, SpellList, and List components work with the reactive store
**Plans**: 2 plans

Plans:
- [x] 03-01-PLAN.md — Rewrite store.js from Vuex to reactive() composable with named exports
- [x] 03-02-PLAN.md — Migrate all 18 components to direct store imports, rewire autosave, remove Vuex

### Phase 4: Cleanup and Verification
**Goal**: The codebase is clean of migration artifacts and every user-facing feature works identically to the pre-migration version
**Depends on**: Phase 3
**Requirements**: CLEAN-01, CLEAN-02, CLEAN-03, CLEAN-04, CLEAN-05
**Success Criteria** (what must be TRUE):
  1. No dead files remain (webpack.mix.js, mix-manifest.json, old build configs)
  2. CLAUDE.md accurately reflects the new build commands and architecture
  3. Character sheet editing, print view, public read-only sheets, and dashboard all function correctly
  4. Autosave, Quill editors, and all list operations (skills, spells, attacks, equipment) work without regressions
**Plans**: 2 plans

Plans:
- [x] 04-01-PLAN.md — Delete dead Mix artifacts, clean migration comments, update CLAUDE.md for Vue 3 stack
- [ ] 04-02-PLAN.md — Build verification and human feature parity testing

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 3 -> 4 (Phase 2 absorbed into Phase 1)

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Build Tool Migration + Vue 3 Upgrade | 0/4 | Planning complete | - |
| ~~2. Vue 3 Framework Upgrade~~ | - | Absorbed into Phase 1 | - |
| 3. Store Migration | 0/2 | Planning complete | - |
| 4. Cleanup and Verification | 0/2 | Planning complete | - |
