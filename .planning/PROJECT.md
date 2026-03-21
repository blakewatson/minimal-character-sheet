# Minimal Character Sheet — Vue 3 Migration

## What This Is

A D&D 5e character sheet web app with a PHP (Fat-Free Framework) backend and Vue.js frontend. The app is fully functional on Vue 2 + Vuex + Laravel Mix. This milestone migrates the frontend to Vue 3 + a `reactive()` composable store + Vite, with zero feature changes.

## Core Value

The app must continue to work identically after migration — same features, same behavior, same PHP backend integration. No regressions in editing, autosave, print view, public sheets, or dashboard.

## Requirements

### Validated

- ✓ Character sheet editing (abilities, skills, spells, attacks, equipment, bio) — existing
- ✓ Autosave on every state change — existing
- ✓ Print view — existing
- ✓ Public read-only sheet sharing — existing
- ✓ User dashboard — existing
- ✓ D&D 2024 rules toggle (is_2024 flag) — existing
- ✓ i18n support (English, German) — existing
- ✓ Dark mode and client-side settings — existing
- ✓ Rich text editing via Quill — existing

### Validated in Phase 01: Build Tool Migration

- ✓ Replace Laravel Mix (webpack) with Vite as build tool — Validated in Phase 01
- ✓ Replace mix() PHP helper with vite() helper for manifest-based asset loading — Validated in Phase 01
- ✓ Upgrade Vue 2.6 to Vue 3 with createApp bootstrap — Validated in Phase 01
- ✓ Replace global event bus (Vue instance) with mitt — Validated in Phase 01
- ✓ Update i18n plugin for Vue 3 plugin API — Validated in Phase 01
- ✓ Remove Vue.filter() usage, replace with function calls — Validated in Phase 01
- ✓ Upgrade Vuex 3 → 4 as intermediate step — Validated in Phase 01
- ✓ Update lifecycle hooks (beforeDestroy → beforeUnmount) — Validated in Phase 01
- ✓ Remove Vue.set() calls (Vue 3 proxy-based reactivity handles direct assignment) — Validated in Phase 01

### Active

- [ ] Replace Vuex store with reactive() composable
- [ ] Replace mapState/mapGetters/commit patterns across all 26+ components
- [ ] Replace $store.subscribe autosave with watch(state, ..., { deep: true })
- [ ] Remove Vuex dependency entirely

### Out of Scope

- New features — this is a pure migration, no new functionality
- Backend changes — PHP models, controllers, routes stay as-is
- CSS/Tailwind changes — already on v4, no changes needed
- Dashboard rewrite — dashboard.js is vanilla JS, not part of Vue migration
- Composition API conversion — staying with Options API for now, just removing Vuex

## Context

- Existing codebase with 29 SFCs in `js/components/`, a ~750-line Vuex store, and 3 JS entry points
- Work happens on the `vue-3` branch
- The PHP backend reads a build manifest for cache-busted asset paths — the manifest format changes from Mix to Vite
- Quill editor does manual DOM manipulation that may need careful testing after Vue 3 upgrade
- The Vuex store is a flat bag of ~50 state properties with mostly trivial mutations — ideal candidate for reactive() replacement
- Components use mapState, mapGetters, $store.commit, $store.dispatch, $store.subscribe, and direct $store.state access — all patterns need updating
- A detailed migration plan exists at `plans/vue3-migration.md` with component-by-component changelist

## Constraints

- **Tech stack**: Must keep PHP (Fat-Free Framework) backend, SQLite database — no backend changes
- **Build output**: Vite must output to `dist/` directory, same as current Mix setup
- **No dev server**: Using Vite as build tool only (not its dev server), since PHP serves the app
- **Branch**: All work on `vue-3` branch

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Vite as build-only (no dev server) | PHP backend serves the app; HMR integration not worth the complexity | ✓ Confirmed in Phase 01 |
| mitt for event bus | Lightweight, standard replacement for Vue 2 instance-as-event-bus pattern | ✓ Confirmed in Phase 01 |
| .vue extensions required | Vite ESM resolver needs explicit extensions; extensionless imports don't work without resolve.extensions config | ✓ Confirmed in Phase 01 |
| reactive() composable over Pinia | Store is a simple flat object; Pinia adds unnecessary abstraction for this use case | — Pending |
| watch(state, ..., { deep: true }) for autosave | Direct replacement for $store.subscribe; state object is small enough that perf is fine | — Pending |
| Keep Options API | Migration scope — convert store only, don't rewrite components to Composition API | — Pending |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd:transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd:complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-03-21 after Phase 01 complete — Vite build tool + Vue 3 + Vuex 4 migration done; Vuex → reactive() composable migration is next*
