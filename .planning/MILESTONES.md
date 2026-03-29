# Milestones

## v1.1 Import/Export (Shipped: 2026-03-29)

**Phases completed:** 1 phases, 2 plans, 5 tasks

**Key accomplishments:**

- Dashboard export button downloads JSON (full store data) + Markdown (human-readable dump) via Blob URLs with sanitized filenames
- Dashboard import button opens a .json file picker, validates client-side, POSTs to /import-sheet which creates a new sheet via create_sheet_with_data()

---

## v1.0 Vue 3 Migration (Shipped: 2026-03-22)

**Phases completed:** 3 phases, 8 plans, 15 tasks

**Key accomplishments:**

- Vite 8 build config with Vue 3, Tailwind CSS v4 plugin, 4 entry points, and static CSS in public/ replacing Laravel Mix
- vite() PHP helper replacing mix(), updated templates with type="module" scripts, Laravel Mix artifacts deleted
- Vuex 4 createStore(), Vue 3 createApp() entry points, mitt event bus, and removal of all 15 Vue.set() calls across store and mixins
- All 26 Vue components updated for Vue 3 compatibility: lifecycle hooks, markRaw for Quill, filter-to-function migration, mitt event bus API, and .vue import extensions -- Vite builds succeed with zero errors
- Vuex store rewritten as reactive() composable with 40 named exports, stable spell IDs, and all-level collapsed normalization
- All 18 components migrated from Vuex to direct reactive store imports, autosave rewired to deep watch, Vuex uninstalled
- Deleted mix-manifest.json, removed migration-era comments from JS source, and updated CLAUDE.md to accurately describe Vue 3 + reactive() + Vite stack
- Production build verified clean, all user-facing features confirmed working identically to pre-migration Vue 2 version

---
