---
phase: 01-build-tool-migration
plan: 03
subsystem: frontend
tags: [vue3, vuex4, mitt, createApp, createStore, event-bus]

# Dependency graph
requires:
  - phase: 01-build-tool-migration/01-01
    provides: Vite build config with @vitejs/plugin-vue
provides:
  - Vuex 4 store with createStore() API
  - Vue 3 entry points (app.js, print.js) with createApp()
  - mitt-based event bus replacing Vue instance event bus
  - Vue 3 compatible i18n plugin using globalProperties
  - Reactive-safe mixins without Vue.set()
  - Global filter replacement via globalProperties.$signedNumString
affects: [01-build-tool-migration/01-04]

# Tech tracking
tech-stack:
  added: [mitt]
  patterns: [createApp mount pattern, globalProperties for filters, mitt event bus]

key-files:
  modified:
    - js/store.js
    - js/app.js
    - js/print.js
    - js/i18n.js
    - js/mixins.js
    - package.json

key-decisions:
  - "signedNumString filter registered as globalProperties.$signedNumString for template access"
  - "mitt event bus on window.sheetEvent with .emit()/.on()/.off() API (no $ prefix)"

patterns-established:
  - "Vue 3 app bootstrap: createApp(Component) -> app.use(plugins) -> app.mount(selector)"
  - "Filter replacement: app.config.globalProperties.$filterName = filterFn"
  - "Event bus: window.sheetEvent = mitt() with .emit/.on/.off"

requirements-completed: [VUE-01, VUE-02, VUE-03, VUE-04, VUE-05, VUE-08, VUE-10]

# Metrics
duration: 2min
completed: 2026-03-21
---

# Phase 01 Plan 03: Vue 3 Core JS Migration Summary

**Vuex 4 createStore(), Vue 3 createApp() entry points, mitt event bus, and removal of all 15 Vue.set() calls across store and mixins**

## Performance

- **Duration:** 2 min
- **Started:** 2026-03-21T23:45:00Z
- **Completed:** 2026-03-21T23:47:00Z
- **Tasks:** 2
- **Files modified:** 6

## Accomplishments
- Migrated store.js from Vuex 3 (new Vuex.Store) to Vuex 4 (createStore) with all 14 Vue.set() calls replaced by direct assignment
- Rewrote app.js and print.js entry points to use Vue 3 createApp() with plugin registration via app.use()
- Replaced Vue instance event bus with mitt library for cross-component communication
- Updated i18n plugin to use app.config.globalProperties instead of Vue.prototype
- Removed Vue.set() from mixins.js and updated Field import to use .vue extension

## Task Commits

Each task was committed atomically:

1. **Task 1: Migrate store.js to Vuex 4 and remove all Vue.set() calls** - `36b2733` (feat)
2. **Task 2: Migrate entry points, i18n plugin, mixins, and event bus to Vue 3 API** - `df3a517` (feat)

## Files Created/Modified
- `js/store.js` - Vuex 4 createStore, removed all Vue.set() calls, updated event bus to mitt API
- `js/app.js` - Vue 3 createApp with mitt event bus, plugin registration, filter replacement
- `js/print.js` - Vue 3 createApp with store and filter globalProperty
- `js/i18n.js` - Plugin install uses app.config.globalProperties instead of Vue.prototype
- `js/mixins.js` - Removed Vue import, replaced Vue.set() with direct array assignment, .vue extension on import
- `package.json` - Added mitt dependency

## Decisions Made
- Registered signedNumString as `app.config.globalProperties.$signedNumString` so templates can call `$signedNumString(value)` directly -- this is the standard Vue 3 replacement for global filters
- mitt event bus uses `.emit()/.on()/.off()` without `$` prefix -- component event bus consumers (Sheet.vue, QuillEditor.vue, List.vue, SpellList.vue) will need `$emit`/`$on`/`$off` updated to `emit`/`on`/`off` in Plan 04

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered
None

## User Setup Required
None - no external service configuration required.

## Next Phase Readiness
- All core JS modules now use Vue 3 / Vuex 4 API
- Components still need lifecycle hook updates (beforeDestroy -> beforeUnmount) and template filter syntax removal (Plan 04)
- Event bus consumers in components need `$emit`/`$on`/`$off` updated to mitt API (Plan 04)

## Self-Check: PASSED

All 7 files verified present. Both task commits (36b2733, df3a517) verified in git log.

---
*Phase: 01-build-tool-migration*
*Completed: 2026-03-21*
