# Roadmap: Vue 3 Migration

## Overview

This migration moves the Minimal Character Sheet frontend from Vue 2 + Vuex + Laravel Mix to Vue 3 + reactive() composable + Vite, with zero feature changes. The phases follow strict dependency ordering: build tool first (so framework issues are isolated from build issues), then Vue 3 upgrade with Vuex 4 as a bridge, then store replacement, then cleanup and verification. Each phase produces a working, testable application.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [ ] **Phase 1: Build Tool Migration** - Replace Laravel Mix with Vite, update PHP asset helper, keep Vue 2 working
- [ ] **Phase 2: Vue 3 Framework Upgrade** - Upgrade to Vue 3 with Vuex 4 bridge, fix all breaking API changes
- [ ] **Phase 3: Store Migration** - Replace Vuex with reactive() composable, update all 26 components
- [ ] **Phase 4: Cleanup and Verification** - Remove dead artifacts, update docs, verify full feature parity

## Phase Details

### Phase 1: Build Tool Migration
**Goal**: The app builds and runs identically using Vite instead of Laravel Mix, with PHP templates loading assets via a new manifest helper
**Depends on**: Nothing (first phase)
**Requirements**: BUILD-01, BUILD-02, BUILD-03, BUILD-04, BUILD-05, BUILD-06, BUILD-07
**Success Criteria** (what must be TRUE):
  1. Running `npm run dev` and `npm run prod` produces compiled assets in `dist/` via Vite
  2. PHP templates load JS and CSS assets through the vite() helper reading `dist/.vite/manifest.json`
  3. The character sheet app loads and functions in the browser with no console errors (still Vue 2 at this point)
  4. Laravel Mix, webpack.mix.js, and webpack-related npm dependencies are removed from the project
**Plans**: 2 plans

Plans:
- [ ] 01-01-PLAN.md — Install Vite, create config, set up public/ dir, consolidate CSS, remove Mix deps
- [ ] 01-02-PLAN.md — Replace PHP mix() helper with vite(), update templates, delete Mix artifacts

### Phase 2: Vue 3 Framework Upgrade
**Goal**: The app runs on Vue 3 with all breaking API changes resolved, using Vuex 4 as a temporary bridge so existing store patterns still work
**Depends on**: Phase 1
**Requirements**: VUE-01, VUE-02, VUE-03, VUE-04, VUE-05, VUE-06, VUE-07, VUE-08, VUE-09, VUE-10
**Success Criteria** (what must be TRUE):
  1. All 26 Vue components compile and render under Vue 3 with no console errors or warnings
  2. The Quill rich text editor loads, accepts input, and saves content without breaking (markRaw applied)
  3. Event-driven features (autosave triggers, cross-component communication) work via mitt instead of Vue instance event bus
  4. Template filters replaced with function calls -- ability modifiers display signed numbers correctly (e.g., "+3", "-1")
  5. All entry points (app.js, print.js) bootstrap via createApp and the app functions end-to-end
**Plans**: TBD

Plans:
- [ ] 02-01: TBD
- [ ] 02-02: TBD

### Phase 3: Store Migration
**Goal**: Vuex is fully removed and replaced with a reactive() composable; all components import state directly and autosave works via watch()
**Depends on**: Phase 2
**Requirements**: STORE-01, STORE-02, STORE-03, STORE-04, STORE-05, STORE-06, STORE-07, STORE-08, STORE-09, STORE-10
**Success Criteria** (what must be TRUE):
  1. The store is a single reactive() object with computed refs for derived values -- no Vuex dependency in package.json
  2. All 26 components read and write state via direct imports (no mapState, mapGetters, $store.commit, or $store.dispatch)
  3. Autosave fires correctly after edits -- exactly one save per change, no infinite loops
  4. List operations (add/remove spells, attacks, equipment) work correctly with stable IDs (no random ID generation in computed properties)
  5. Dynamic state access patterns in SpellGroup, SpellList, and List components work with the reactive store
**Plans**: TBD

Plans:
- [ ] 03-01: TBD
- [ ] 03-02: TBD

### Phase 4: Cleanup and Verification
**Goal**: The codebase is clean of migration artifacts and every user-facing feature works identically to the pre-migration version
**Depends on**: Phase 3
**Requirements**: CLEAN-01, CLEAN-02, CLEAN-03, CLEAN-04, CLEAN-05
**Success Criteria** (what must be TRUE):
  1. No dead files remain (webpack.mix.js, mix-manifest.json, old build configs)
  2. CLAUDE.md accurately reflects the new build commands and architecture
  3. Character sheet editing, print view, public read-only sheets, and dashboard all function correctly
  4. Autosave, Quill editors, and all list operations (skills, spells, attacks, equipment) work without regressions
**Plans**: TBD

Plans:
- [ ] 04-01: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3 -> 4

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Build Tool Migration | 0/2 | Planning complete | - |
| 2. Vue 3 Framework Upgrade | 0/0 | Not started | - |
| 3. Store Migration | 0/0 | Not started | - |
| 4. Cleanup and Verification | 0/0 | Not started | - |
