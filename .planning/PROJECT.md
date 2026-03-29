# Minimal Character Sheet

## What This Is

A D&D 5e character sheet web app with a PHP (Fat-Free Framework) backend and Vue.js 3 frontend. The app runs on Vue 3 + a `reactive()` composable store + Vite, with PHP serving all routes and SQLite for storage.

## Core Value

A lightweight, zero-friction character sheet that just works — fast to load, easy to edit, auto-saves everything.

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
- ✓ Replace Laravel Mix with Vite as build tool — v1.0
- ✓ Replace mix() PHP helper with vite() helper — v1.0
- ✓ Upgrade Vue 2.6 to Vue 3 with createApp bootstrap — v1.0
- ✓ Replace global event bus with mitt — v1.0
- ✓ Update i18n plugin for Vue 3 plugin API — v1.0
- ✓ Remove Vue.filter() usage, replace with function calls — v1.0
- ✓ Upgrade Vuex 3 → 4 as intermediate step — v1.0
- ✓ Update lifecycle hooks (beforeDestroy → beforeUnmount) — v1.0
- ✓ Remove Vue.set() calls — v1.0
- ✓ Replace Vuex store with reactive() composable — v1.0
- ✓ Replace mapState/mapGetters/commit patterns across all components — v1.0
- ✓ Replace $store.subscribe autosave with watch(state, ..., { deep: true }) — v1.0
- ✓ Remove Vuex dependency entirely — v1.0
- ✓ All dead migration artifacts removed — v1.0
- ✓ CLAUDE.md accurately reflects Vue 3 + Vite + reactive() stack — v1.0
- ✓ All views verified: sheet editing, print view, dashboard, public read-only sheets — v1.0
- ✓ Autosave, Quill editors, list operations all work — v1.0

### Active

(No active requirements — next milestone not yet planned)

### Recently Validated

- ✓ Export character sheets as JSON and Markdown from dashboard — Phase 01
- ✓ Import character sheets from JSON file via dashboard — Phase 01

### Out of Scope

- Composition API conversion — staying with Options API, just removed Vuex
- Pinia adoption — store is a flat ~50-property bag; reactive() composable is sufficient
- SSR / Server-side rendering — PHP backend serves HTML
- Vue Router — app uses PHP routing
- Vite dev server / HMR — PHP serves the app; build-only mode is simpler
- New features — will be considered in future milestones
- Backend changes — PHP models, controllers, routes stay as-is

## Context

Shipped v1.0 with ~6,365 LOC JavaScript/Vue across 29 SFCs.
Tech stack: PHP 8.4 (Fat-Free Framework), Vue 3.5, Vite 8, Tailwind CSS v4, SQLite.
Store is a reactive() composable with 40 named exports in `js/store.js`.
All work done on `vue-3` branch over 2 days (2026-03-21 → 2026-03-22), 73 commits.

## Constraints

- **Tech stack**: PHP (Fat-Free Framework) backend, SQLite database
- **Build output**: Vite outputs to `dist/` directory
- **No dev server**: Vite as build tool only, PHP serves the app
- **Branch**: Work on `vue-3` branch

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Vite as build-only (no dev server) | PHP backend serves the app; HMR integration not worth the complexity | ✓ Good |
| mitt for event bus | Lightweight, standard replacement for Vue 2 instance-as-event-bus pattern | ✓ Good |
| .vue extensions required | Vite ESM resolver needs explicit extensions | ✓ Good |
| reactive() composable over Pinia | Store is a simple flat object; Pinia adds unnecessary abstraction | ✓ Good |
| watch(state, ..., { deep: true }) for autosave | Direct replacement for $store.subscribe; state object is small enough | ✓ Good |
| Keep Options API | Migration scope — convert store only, don't rewrite components | ✓ Good |
| Absorb Phase 2 into Phase 1 | @vitejs/plugin-vue2 incompatible with Vite 8 | ✓ Good — avoided version downgrade |

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
*Last updated: 2026-03-29 after Phase 01 (Import/Export Characters)*
