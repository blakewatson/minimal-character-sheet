# Phase 3: Store Migration - Context

**Gathered:** 2026-03-21
**Status:** Ready for planning

<domain>
## Phase Boundary

Vuex is fully removed and replaced with a `reactive()` composable. All 18 components that access the store are updated to import state directly. Autosave is rewired to use `watch()`. The app must work identically after migration — no feature changes.

</domain>

<decisions>
## Implementation Decisions

### Store file API shape
- **D-01:** New `store.js` uses flat named exports — `export { state, modifiers, proficiencyBonus, initializeState, updateState, getJSON }`. Components import directly: `import { state, modifiers } from './store'`. No `useStore()` composable wrapper.
- **D-02:** `getJSON` becomes a plain synchronous function — `return JSON.stringify(state)`. No Promise wrapper.
- **D-03:** `initializeState` and `updateState` stay in `store.js` (not extracted to a separate file).
- **D-04:** `replaceState` mutation concept is dropped entirely. Both functions mutate the reactive state object directly via `Object.assign(state, newData)`.
- **D-05:** `modifiers` and `proficiencyBonus` are exported as Vue 3 `computed()` refs from `store.js`.

### Autosave redesign
- **D-06:** `watch(state, () => throttledSave(), { deep: true })` is inlined directly in Sheet.vue's `mounted()` hook. No `autosaveLoop()` method.
- **D-07:** Manual `window.sheetEvent.emit('autosave', ...)` calls in SpellList.vue (and anywhere else) are removed — the deep state watcher covers all mutations.
- **D-08:** `manualSave()` method in Sheet.vue is unchanged — it calls `saveSheetState()` directly, bypassing the watcher.
- **D-09:** Sheet.vue still reads `state.characterName` directly for the POST body (no change to save logic).

### SpellList ID fix
- **D-10:** `initializeState` assigns stable fallback IDs (via `Date.now() + index` or similar) to any spell missing an `id` field, across all 9 spell levels (`lvl1Spells` through `lvl9Spells`) and `cantripsList`.
- **D-11:** `initializeState` also applies `collapsed: false` normalization to all 9 spell levels (not just lvl1 as currently). Existing code only normalizes lvl1 — fix all levels in this phase.
- **D-12:** `SpellList.vue`'s `spellItems` computed becomes a plain pass-through — no more `Math.random()` ID assignment. Returns `state[this.listField].spells` directly.

### Dynamic state access (SpellGroup, SpellList, List)
- **D-13:** Components that use `state[this.listField]` (dynamic key access) wrap the access in a `computed()` property. Since `state` is a reactive object, `state[computedKey]` is reactive as long as the computed is re-evaluated when `listField` changes. No special handling needed beyond normal computed usage.

### Plan structure
- **Claude's Discretion:** Plan breakdown, ordering of component migrations, and which plan removes Vuex from package.json. User deferred this entirely.

</decisions>

<specifics>
## Specific Ideas

- No specific UI or behavioral references — this is a pure internal refactor. The app must look and work identically before and after.

</specifics>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Current store implementation
- `js/store.js` — Full Vuex store: all state fields, getters (modifiers, proficiencyBonus), mutations, and actions. The new reactive() store must expose the same interface.

### Components with store access (18 files, 86 occurrences)
- `js/components/Sheet.vue` — `mapState(['is_2024', 'readOnly'])`, `$store.dispatch('getJSON')`, `$store.dispatch('initializeState')`, `$store.subscribe`, `$store.state.characterName`
- `js/components/SpellList.vue` — `$store.state[this.listField].spells` (dynamic key), `$store.commit(...)` for all spell mutations; **has the random ID bug in `spellItems` computed**
- `js/components/SpellGroup.vue` — `mapState(['readOnly'])`, `$store.state[this.listField]` (dynamic key), `$store.commit(...)` for spell slot/expended/collapsed mutations
- `js/components/List.vue` — dynamic `$store.state[field]` access pattern
- `js/components/Skills.vue` — `mapState` + `$store.commit` (8 occurrences)
- `js/components/Attacks.vue` — `mapState` + `$store.commit` (7 occurrences)
- `js/components/Abilities.vue`, `Ability.vue`, `Bio.vue`, `Vitals.vue`, `Proficiency.vue`, `Equipment.vue`, `TextSection.vue`, `TrackableFields.vue`, `SavingThrow.vue`, `Spells.vue`, `Tabs.vue`, `Print.vue` — varying combinations of `mapState`, `mapGetters`, `$store.commit`, `$store.dispatch`

### Entry points (bootstrap changes)
- `js/app.js` — Currently does `app.use(store)`. Must remove after store migration.
- `js/print.js` — Check for any store usage; Print.vue uses `mapState`.

### Mixin
- `js/mixins.js` — `listMixin` uses direct array assignment (no Vue.set). Already Vue 3 compatible; verify no store access needed.

### Requirements
- `REQUIREMENTS.md` §Store Migration — STORE-01 through STORE-10, all pending

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `js/store.js` state object: already flat (~50 properties) — maps directly to `reactive({...})` with no restructuring needed
- `js/store.js` getters: `modifiers` and `proficiencyBonus` become `computed(() => ...)` exports
- `js/store.js` mutations: all become plain functions that mutate `state` directly — most are simple `state.field = val` assignments
- `js/store.js` `initializeState` / `updateState` actions: logic stays identical, just mutate state directly instead of calling `commit('replaceState')`

### Established Patterns
- Components use `this.$store.state.X` or `mapState(['X'])` for reads — both replaced with `import { state } from '../store'` + `computed: { x() { return state.x } }` or direct `state.x` in templates via a computed
- Components use `this.$store.commit('mutationName', payload)` for writes — replaced with `import { mutationName } from '../store'` + direct call
- `$store.dispatch('getJSON')` in Sheet.vue — replaced with synchronous `import { getJSON } from '../store'`

### Integration Points
- `js/app.js`: remove `import store from './store'` and `app.use(store)` after migration
- `Sheet.vue` `saveSheetState()`: reads `this.$store.state.characterName` for POST — becomes `state.characterName` after import
- `Sheet.vue` `saveSheetState()`: calls `await this.$store.dispatch('getJSON')` — becomes synchronous `getJSON()`
- `window.sheetEvent.emit('quill-refresh')` in `updateState` — stays unchanged (mitt event bus remains for Quill refresh)

### Known Bug to Fix
- `SpellList.vue` line 110: `spell.id = Math.random().toString()` inside `spellItems` computed — generates new IDs on every render, breaking Vue's keyed list reconciliation and `sortSpells` (which finds by ID). Fix: remove this line; ensure all spells have stable IDs from `initializeState`.

</code_context>

<deferred>
## Deferred Ideas

- None — discussion stayed within phase scope

</deferred>

---

*Phase: 03-store-migration*
*Context gathered: 2026-03-21*
