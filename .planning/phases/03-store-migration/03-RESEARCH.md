# Phase 3: Store Migration - Research

**Researched:** 2026-03-21
**Domain:** Vue 3 reactive() store migration from Vuex 4
**Confidence:** HIGH

## Summary

This phase replaces Vuex 4 with a plain Vue 3 `reactive()` object as the store. The current store (`js/store.js`) is a flat Vuex store with ~50 state properties, 2 getters, ~25 mutations, and 2 actions. Eighteen components access the store through `mapState`, `mapGetters`, `$store.commit`, and `$store.dispatch` patterns -- all must be rewritten to use direct imports.

The migration is mechanically straightforward because the store is flat (no modules, no plugins) and mutations are simple property assignments. The primary risks are: (1) the autosave watcher triggering infinite loops or missing changes, (2) the SpellList random ID bug causing DOM reconciliation issues during migration, and (3) the `TextSection.vue` dynamic field access pattern that uses `mapState` to pull all text fields into the component then accesses them via `this[this.field]`.

**Primary recommendation:** Rewrite `store.js` first as a reactive() module with all exports, then migrate components in waves grouped by complexity -- simple components first (no dynamic access), then dynamic-access components (SpellList, SpellGroup, List, TextSection), then Sheet.vue (autosave rewiring) last.

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions
- **D-01:** New `store.js` uses flat named exports -- `export { state, modifiers, proficiencyBonus, initializeState, updateState, getJSON }`. Components import directly: `import { state, modifiers } from './store'`. No `useStore()` composable wrapper.
- **D-02:** `getJSON` becomes a plain synchronous function -- `return JSON.stringify(state)`. No Promise wrapper.
- **D-03:** `initializeState` and `updateState` stay in `store.js` (not extracted to a separate file).
- **D-04:** `replaceState` mutation concept is dropped entirely. Both functions mutate the reactive state object directly via `Object.assign(state, newData)`.
- **D-05:** `modifiers` and `proficiencyBonus` are exported as Vue 3 `computed()` refs from `store.js`.
- **D-06:** `watch(state, () => throttledSave(), { deep: true })` is inlined directly in Sheet.vue's `mounted()` hook. No `autosaveLoop()` method.
- **D-07:** Manual `window.sheetEvent.emit('autosave', ...)` calls in SpellList.vue (and anywhere else) are removed -- the deep state watcher covers all mutations.
- **D-08:** `manualSave()` method in Sheet.vue is unchanged -- it calls `saveSheetState()` directly, bypassing the watcher.
- **D-09:** Sheet.vue still reads `state.characterName` directly for the POST body (no change to save logic).
- **D-10:** `initializeState` assigns stable fallback IDs (via `Date.now() + index` or similar) to any spell missing an `id` field, across all 9 spell levels (`lvl1Spells` through `lvl9Spells`) and `cantripsList`.
- **D-11:** `initializeState` also applies `collapsed: false` normalization to all 9 spell levels (not just lvl1 as currently). Existing code only normalizes lvl1 -- fix all levels in this phase.
- **D-12:** `SpellList.vue`'s `spellItems` computed becomes a plain pass-through -- no more `Math.random()` ID assignment. Returns `state[this.listField].spells` directly.
- **D-13:** Components that use `state[this.listField]` (dynamic key access) wrap the access in a `computed()` property. Since `state` is a reactive object, `state[computedKey]` is reactive as long as the computed is re-evaluated when `listField` changes. No special handling needed beyond normal computed usage.

### Claude's Discretion
- Plan breakdown, ordering of component migrations, and which plan removes Vuex from package.json.

### Deferred Ideas (OUT OF SCOPE)
- None -- discussion stayed within phase scope.
</user_constraints>

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| STORE-01 | Rewrite Vuex store as reactive() composable with exported state, computed refs, and plain functions | Store analysis below: flat state, 2 getters become computed(), mutations become exported functions, actions become plain functions |
| STORE-02 | Replace mapState in all components with computed properties referencing imported state | Component inventory below identifies all 15 components using mapState with exact field lists |
| STORE-03 | Replace mapGetters with imported computed refs (modifiers, proficiencyBonus) | 7 components use mapGetters -- replace with direct import of computed refs |
| STORE-04 | Replace $store.commit calls with direct state assignments across all components | 14 components use $store.commit -- mutations become exported functions, calls become direct invocations |
| STORE-05 | Replace $store.dispatch calls with direct function imports (getJSON, initializeState) | Sheet.vue and Print.vue use dispatch -- getJSON becomes sync, initializeState/updateState become plain functions |
| STORE-06 | Replace $store.subscribe autosave with watch(state, ..., { deep: true }) | Autosave pattern documented below with pitfall analysis for infinite loops |
| STORE-07 | Fix SpellList.vue computed property that generates random IDs (move to creation time) | SpellList line 110 bug documented; fix in initializeState per D-10 |
| STORE-08 | Update listMixin to use direct array assignment instead of Vue.set | listMixin already uses direct assignment (no Vue.set); no changes needed -- STORE-08 is already satisfied |
| STORE-09 | Handle dynamic state access patterns in SpellGroup, SpellList, List components | 4 components use dynamic access -- pattern documented below with computed() wrapping per D-13 |
| STORE-10 | Remove Vuex dependency entirely (npm uninstall, remove app.use(store)) | Entry points app.js and print.js both do app.use(store) -- remove after all components migrated |
</phase_requirements>

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| vue | ^3.5.30 | reactive(), computed(), watch() for store | Already installed; provides all primitives needed |

### Supporting
No additional libraries needed. The reactive store pattern uses only Vue 3 built-in APIs.

### What Gets Removed
| Package | Current Version | Why Removed |
|---------|----------------|-------------|
| vuex | ^4.1.0 | Replaced entirely by reactive() pattern |

**Uninstall command:**
```bash
npm uninstall vuex
```

## Architecture Patterns

### New Store Structure (`js/store.js`)

```javascript
import { reactive, computed } from 'vue';
import levelData from './level-data';

// Reactive state -- flat object, same shape as current Vuex state
export const state = reactive({
  id: '',
  slug: '',
  // ... all ~50 properties identical to current state
});

// Computed refs (replace Vuex getters)
export const modifiers = computed(() => {
  return state.abilities.map((a) => ({
    ability: a.name,
    val: Math.floor(parseInt(a.score) / 2 - 5),
  }));
});

export const proficiencyBonus = computed(() => {
  if (state.proficiencyOverride !== null && state.proficiencyOverride !== undefined) {
    return state.proficiencyOverride;
  }
  var level = state.level;
  var row = state.levelData.find((data) => level === data.lvl);
  if (!row) return 2;
  return row.proficiency;
});

// Plain functions (replace Vuex mutations)
export function updateBio(payload) {
  // validation logic stays identical
  state[payload.field] = payload.val;
}

// Synchronous (replaces Vuex action that returned Promise)
export function getJSON() {
  return JSON.stringify(state);
}

// Plain function (replaces Vuex action)
export function initializeState(payload) {
  // ... same logic, but uses Object.assign(state, newState)
}

export function updateState(payload) {
  // ... same logic, but uses Object.assign(state, newState)
  window.sheetEvent.emit('quill-refresh');
}
```

### Component Migration Pattern: mapState Replacement

**Before (Vuex):**
```javascript
import { mapState } from 'vuex';
export default {
  computed: {
    ...mapState(['readOnly', 'abilities']),
  },
  methods: {
    doSomething() {
      this.$store.commit('updateBio', { field, val });
    }
  }
}
```

**After (reactive store):**
```javascript
import { state, updateBio } from '../store';
export default {
  computed: {
    readOnly() { return state.readOnly; },
    abilities() { return state.abilities; },
  },
  methods: {
    doSomething() {
      updateBio({ field, val });
    }
  }
}
```

### Component Migration Pattern: mapGetters Replacement

**Before:**
```javascript
import { mapGetters } from 'vuex';
computed: {
  ...mapGetters(['modifiers', 'proficiencyBonus']),
}
```

**After:**
```javascript
import { modifiers, proficiencyBonus } from '../store';
computed: {
  modifiers() { return modifiers.value; },
  proficiencyBonus() { return proficiencyBonus.value; },
}
```

Note: `computed()` refs from the store must be accessed with `.value` inside Options API computed properties.

### Component Migration Pattern: Dynamic State Access

**Before (SpellGroup.vue):**
```javascript
computed: {
  totalSlots() {
    return this.$store.state[this.listField].slots;
  },
}
```

**After:**
```javascript
import { state } from '../store';
computed: {
  totalSlots() {
    return state[this.listField].slots;
  },
}
```

This works because `state` is a reactive proxy. When `this.listField` (a prop) changes, the computed re-evaluates and accesses the new key on the reactive object.

### Component Migration Pattern: TextSection Dynamic Field

TextSection.vue currently maps ALL 8 text fields via mapState and then accesses them via `this[this.field]`. After migration:

```javascript
import { state } from '../store';
computed: {
  textField() {
    return state[this.field] || '';
  },
}
```

This is simpler -- no need to map all 8 fields. Just access `state[this.field]` directly.

### Autosave Pattern (Sheet.vue)

**Before:**
```javascript
autosaveLoop() {
  this.$store.subscribe((mutation, state) => {
    window.sheetEvent.emit('autosave');
  });
  window.sheetEvent.on('autosave', () => {
    this.resetRetryState();
    this.hasUnsavedChanges = true;
    this.throttledSave();
  });
}
```

**After (in mounted()):**
```javascript
import { watch } from 'vue';
import { state } from '../store';

mounted() {
  // ... existing code ...
  if (!this.isPublic) {
    watch(state, () => {
      this.resetRetryState();
      this.hasUnsavedChanges = true;
      this.throttledSave();
    }, { deep: true });
  }
}
```

### Anti-Patterns to Avoid
- **Accessing computed refs without .value in Options API:** `modifiers` is a `computed()` ref. In Options API, wrap in a local computed: `computed: { modifiers() { return modifiers.value; } }`. Do NOT use `modifiers` directly in templates without unwrapping.
- **Using Object.assign for initial state without preserving reactivity:** `Object.assign(state, newData)` works correctly because it mutates properties on the existing reactive proxy. Do NOT do `state = reactive(newData)` -- that replaces the reference and breaks all existing imports.
- **Triggering autosave during initializeState:** The deep watcher will fire when `initializeState` runs `Object.assign`. The watcher callback must either (a) not be registered until after initialization, or (b) have a guard flag. Since the watcher is set up in `mounted()` and `initializeState` is called in `created()`, the ordering naturally prevents this in the main app. But in Print.vue, `initializeState` is also called in `created()` -- verify Print.vue does NOT set up a watcher (it should not, as it is read-only).

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Deep watching reactive state | Custom mutation tracking | `watch(state, cb, { deep: true })` | Vue 3's deep watch handles nested reactive objects correctly |
| Computed derived values | Manual memoization | `computed(() => ...)` from Vue | Automatic dependency tracking, caching |
| State serialization exclusion | Custom toJSON | Vue's reactive proxy ignores internal Vue properties in JSON.stringify | `JSON.stringify(reactive({...}))` just works -- Vue proxy's internal properties are not enumerable |

## Common Pitfalls

### Pitfall 1: Autosave Infinite Loop
**What goes wrong:** `watch(state, ..., { deep: true })` fires on ANY state mutation. If `saveSheetState` or its result handler modifies state (e.g., updating a `lastSaved` timestamp in state), it creates an infinite loop.
**Why it happens:** Deep watchers detect all nested changes including those made by the save handler itself.
**How to avoid:** Ensure `saveSheetState` only modifies component-local `data()` properties (`isSaving`, `hasUnsavedChanges`, `isError`, etc.) -- never state store properties. Current code already does this correctly.
**Warning signs:** Browser tab freezes, rapid network requests, console flooded with save attempts.

### Pitfall 2: Autosave Fires During Initialization
**What goes wrong:** `Object.assign(state, sheetData)` in `initializeState` triggers the deep watcher, causing an immediate save of the just-loaded data.
**Why it happens:** The watcher does not distinguish between user edits and programmatic state changes.
**How to avoid:** Lifecycle ordering protects against this: `created()` calls `initializeState`, `mounted()` sets up the watcher. In Vue, `created()` runs before `mounted()`, so by the time the watcher is active, initialization is complete. For public sheet polling (`updateState`), the watcher IS active -- but public sheets skip the watcher entirely (guard: `if (!this.isPublic)`). For owned sheets that call `updateState` via `refreshLoop` -- wait, owned sheets don't call refreshLoop. Only public sheets do. So this is safe.
**Warning signs:** A save request fires immediately on page load.

### Pitfall 3: computed() Ref .value Gotcha in Options API
**What goes wrong:** Importing `modifiers` (a computed ref) and using it directly in a template or method as `modifiers` instead of `modifiers.value` yields a Ref object, not the computed result.
**Why it happens:** In Composition API, refs auto-unwrap in templates. In Options API, there is no auto-unwrap for imported refs.
**How to avoid:** Always wrap imported computed refs in a local Options API computed property: `computed: { modifiers() { return modifiers.value; } }`.
**Warning signs:** Template renders `[object Object]` or `undefined` instead of the expected array/number.

### Pitfall 4: Object.assign Shallow Merge Loses Nested Defaults
**What goes wrong:** `Object.assign(state, sheetData)` replaces nested objects entirely. If the default state has `deathSaves: { successes: [...], failures: [...] }` and the sheet data has a `deathSaves` object, the default is replaced -- not merged. This is actually desired behavior (sheet data IS the truth), but if a nested object is missing a key that the default has, it will be lost.
**Why it happens:** Object.assign is a shallow merge.
**How to avoid:** This is the same behavior as the current Vuex `replaceState` mutation, which iterates over payload properties and assigns them. The current normalization code in `initializeState` handles missing fields (weaponNotes on attacks, collapsed on spells, etc.). Keep this normalization logic intact.
**Warning signs:** Missing properties on loaded sheets that existed in defaults.

### Pitfall 5: Template $store References in Templates
**What goes wrong:** Some components use `$store.commit(...)` directly in template expressions (not in methods). Example: `Attacks.vue` line 224: `@click="$store.commit('addAttack')"` and `TrackableFields.vue` line 253: `@click="$store.commit('addTrackableField')"`.
**Why it happens:** These are inline template expressions that bypass component methods.
**How to avoid:** Replace with method calls that invoke the imported store function. Must not miss these during migration.
**Warning signs:** Runtime error "$store is not defined" or "commit is not a function".

### Pitfall 6: Tabs.vue Uses $store.state Directly
**What goes wrong:** `Tabs.vue` accesses `this.$store.state.slug` in a computed property (line 147) without using mapState.
**Why it happens:** Direct $store access alongside mapState usage.
**How to avoid:** Replace with `state.slug` after importing state.
**Warning signs:** Runtime error when accessing slug property.

### Pitfall 7: addToListField Uses Math.random() for IDs
**What goes wrong:** The `addToListField` mutation (store.js line 487) uses `Math.random().toString()` for new list item IDs. While not as bad as the SpellList bug (this runs once at creation, not on every render), it produces non-deterministic IDs.
**Why it happens:** Legacy pattern.
**How to avoid:** Change to `Date.now().toString()` for consistency with other ID generation (attacks, trackable fields). Not strictly required but improves consistency.
**Warning signs:** None immediately -- this works but is inconsistent.

## Code Examples

### Complete store.js Skeleton

```javascript
import { reactive, computed } from 'vue';
import levelData from './level-data';

const defaultState = {
  id: '',
  slug: '',
  is_2024: false,
  readOnly: false,
  levelData: levelData,
  level: 1,
  characterName: '',
  // ... all other fields from current state
};

export const state = reactive({ ...defaultState });

// Getters -> computed refs
export const modifiers = computed(() =>
  state.abilities.map((a) => ({
    ability: a.name,
    val: Math.floor(parseInt(a.score) / 2 - 5),
  }))
);

export const proficiencyBonus = computed(() => {
  if (state.proficiencyOverride !== null && state.proficiencyOverride !== undefined) {
    return state.proficiencyOverride;
  }
  const row = state.levelData.find((data) => state.level === data.lvl);
  if (!row) return 2;
  return row.proficiency;
});

// Mutations -> plain exported functions
export function updateAbilityScore(payload) {
  state.abilities.forEach((ability, i) => {
    if (ability.name === payload.name) {
      state.abilities[i].score = payload.score;
    }
  });
}

// ... all other mutations become exported functions with same logic

// Actions -> plain functions
export function getJSON() {
  return JSON.stringify(state);
}

export function initializeState(payload) {
  var sheet = JSON.parse(payload.sheet);
  var newState = JSON.parse(JSON.stringify(defaultState));
  if (sheet.data) {
    newState = Object.assign({}, newState, sheet.data);
  }
  // ... all normalization logic (initiative, weaponNotes, collapsed, IDs)

  // NEW: Assign stable IDs to all spells missing them (D-10)
  const spellLevels = ['lvl1Spells', 'lvl2Spells', 'lvl3Spells', 'lvl4Spells',
    'lvl5Spells', 'lvl6Spells', 'lvl7Spells', 'lvl8Spells', 'lvl9Spells'];
  spellLevels.forEach((level) => {
    if (newState[level] && newState[level].spells) {
      newState[level].spells.forEach((spell, idx) => {
        if (!spell.id) {
          spell.id = (Date.now() + idx).toString();
        }
        // D-11: normalize collapsed for ALL levels
        if (!spell.hasOwnProperty('collapsed')) {
          spell.collapsed = false;
        }
      });
    }
  });

  // Also normalize cantripsList IDs
  if (newState.cantripsList) {
    newState.cantripsList.forEach((cantrip, idx) => {
      if (!cantrip.id) {
        cantrip.id = (Date.now() + idx + 100).toString();
      }
      if (!cantrip.hasOwnProperty('collapsed')) {
        cantrip.collapsed = false;
      }
    });
  }

  Object.assign(state, newState);
}

export function updateState(payload) {
  // ... same logic as current, ending with:
  Object.assign(state, newState);
  window.sheetEvent.emit('quill-refresh');
}
```

### Sheet.vue Autosave with watch()

```javascript
import { watch } from 'vue';
import { state, initializeState, updateState, getJSON } from '../store';

// In created():
initializeState({ sheet: window.sheet });

// In mounted():
if (!this.isPublic) {
  watch(state, () => {
    this.resetRetryState();
    this.hasUnsavedChanges = true;
    this.throttledSave();
  }, { deep: true });
}

// In saveSheetState():
var json = getJSON(); // synchronous, no await needed
formBody.set('name', state.characterName); // direct access
```

## Component Store Access Inventory

Complete inventory of all store access patterns that must be migrated:

### Components Using mapState (15 components)

| Component | mapState Fields |
|-----------|----------------|
| Sheet.vue | `is_2024`, `readOnly` |
| Bio.vue | `is_2024`, `level`, `characterName`, `className`, `race`, `background`, `alignment`, `xp`, `readOnly` |
| Vitals.vue | `hp`, `maxHp`, `tempHp`, `hitDie`, `totalHitDie`, `ac`, `speed`, `deathSaves`, `conditions`, `concentration`, `readOnly` |
| Abilities.vue | `abilities` |
| Ability.vue | `readOnly`, `savingThrows` |
| Skills.vue | `skills`, `readOnly`, `passivePerceptionOverride` |
| Attacks.vue | `attacks`, `readOnly` |
| Equipment.vue | `coins`, `equipmentText`, `readOnly` |
| TextSection.vue | `equipmentText`, `proficienciesText`, `featuresText`, `personalityText`, `backstoryText`, `treasureText`, `notesText`, `organizationsText` |
| TrackableFields.vue | `trackableFields`, `readOnly` |
| Spells.vue | `abilities`, `className`, `spClass`, `spAbility`, `spSave`, `spAttack`, `cantripsList`, `readOnly` |
| SpellGroup.vue | `readOnly` |
| Proficiency.vue | `inspiration`, `readOnly`, `initiative`, `shortRests`, `proficiencyOverride` |
| SavingThrow.vue | `readOnly` |
| Tabs.vue | `readOnly` |
| Print.vue | 33 fields (all state) |

### Components Using mapGetters (7 components)

| Component | Getters Used |
|-----------|-------------|
| Abilities.vue | `modifiers` |
| Ability.vue | `proficiencyBonus` |
| Skills.vue | `modifiers`, `proficiencyBonus` |
| Attacks.vue | `modifiers` |
| Spells.vue | `modifiers` |
| Proficiency.vue | `proficiencyBonus` |
| SavingThrow.vue | `proficiencyBonus` |
| Print.vue | `modifiers`, `proficiencyBonus` |

### Components Using $store.commit (14 components)

| Component | Mutations Called |
|-----------|----------------|
| Bio.vue | `updateLevel`, `updateBio` |
| Vitals.vue | `updateVitals`, `updateDeathSaves` |
| Ability.vue | `updateAbilityScore`, `updateSavingThrow` |
| Skills.vue | `updateSkillProficiency`, `updateSkillModifierOverride`, `updatePassivePerceptionOverride` |
| Attacks.vue | `updateAttacks`, `deleteAttack`, `sortAttacks`, `addAttack` (inline template) |
| Equipment.vue | `updateCoins`, `updateEquipment` |
| TextSection.vue | `updateTextField` |
| TrackableFields.vue | `updateTrackableField`, `deleteTrackableField`, `sortTrackableField`, `addTrackableField` (inline template) |
| Spells.vue | `updateSpellInfo`, `updateListField` |
| SpellGroup.vue | `updateSpellSlots`, `updateExpendedSlots`, `updateSpellCollapsed` |
| SpellList.vue | `updateSpellName`, `updateSpellPrepared`, `updateSpellCollapsed`, `addSpell`, `deleteSpell`, `sortSpells` |
| List.vue | `updateListField`, `addToListField`, `deleteFromListField`, `sortListField` |
| Proficiency.vue | `updateInitiative`, `updateInspiration`, `updateShortRests`, `updateProficiencyOverride` |
| SavingThrow.vue | `updateSavingThrow` |

### Components Using $store.dispatch (2 components)

| Component | Actions Called |
|-----------|--------------|
| Sheet.vue | `getJSON`, `initializeState`, `updateState` |
| Print.vue | `initializeState` |

### Components Using $store.state Directly (3 components)

| Component | Direct Access Pattern |
|-----------|----------------------|
| Sheet.vue | `this.$store.state.characterName` |
| Tabs.vue | `this.$store.state.slug` |
| SpellGroup.vue | `this.$store.state[this.listField]` (dynamic) |

### Components Using $store.subscribe (1 component)

| Component | Pattern |
|-----------|---------|
| Sheet.vue | `this.$store.subscribe((mutation, state) => {...})` |

### Dynamic State Access Components (4 components)

| Component | Pattern | Key Source |
|-----------|---------|------------|
| SpellList.vue | `this.$store.state[this.listField].spells` | prop `listField` (e.g., `lvl1Spells`) |
| SpellGroup.vue | `this.$store.state[this.listField].slots/expended/spells` | computed `listField` from prop `level` |
| List.vue | `this.$store.state[this.listField]` | prop `listField` (e.g., `cantripsList`) |
| TextSection.vue | `this[this.field]` (via mapState of all text fields) | prop `field` |

### Inline Template Store Access (2 components)

| Component | Template Expression |
|-----------|--------------------|
| Attacks.vue | `@click="$store.commit('addAttack')"` |
| TrackableFields.vue | `@click="$store.commit('addTrackableField')"` |

### Manual Autosave Emit Sites (3 locations -- all to be removed)

| Component | Location | Code |
|-----------|----------|------|
| SpellList.vue | `updateSpellPrepared` | `window.sheetEvent.emit('autosave', 1)` |
| SpellList.vue | `deleteSpell` | `window.sheetEvent.emit('autosave', 1)` |
| List.vue | `deleteItem` | `window.sheetEvent.emit('autosave', 1)` |

### Entry Points to Update (2 files)

| File | Current Code | Action |
|------|-------------|--------|
| `js/app.js` | `import store from './store'; app.use(store);` | Remove both lines |
| `js/print.js` | `import store from './store'; app.use(store);` | Remove both lines |

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Vuex 3 (Vue 2) | reactive() composable or Pinia | Vue 3 release (2020) | Vuex is in maintenance mode; reactive() is simpler for flat stores |
| mapState/mapGetters | Direct imports from composable | Vue 3 Composition API | No injection/provide needed for flat module-level state |
| $store.subscribe | watch(state, ..., { deep: true }) | Vue 3 | Native Vue reactivity replaces Vuex subscription |

**Deprecated/outdated:**
- Vuex: In maintenance mode. Official recommendation is Pinia for new projects, but for a flat store like this, plain reactive() is simpler than either.

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | None detected -- no test framework configured |
| Config file | None |
| Quick run command | N/A |
| Full suite command | N/A |

### Phase Requirements -> Test Map
| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| STORE-01 | Store exports state, computed refs, functions | manual | Build + load app | N/A |
| STORE-02 | Components read state via imports | manual | Build + verify each view | N/A |
| STORE-03 | Components use imported computed refs | manual | Build + verify calculations | N/A |
| STORE-04 | Components write state via imported functions | manual | Build + edit fields | N/A |
| STORE-05 | getJSON/initializeState work as plain functions | manual | Build + load/save cycle | N/A |
| STORE-06 | Autosave fires on edits, no infinite loops | manual | Edit field, verify single POST | N/A |
| STORE-07 | SpellList uses stable IDs | manual | Add spells, verify no flickering | N/A |
| STORE-08 | listMixin uses direct assignment | manual | Already satisfied (no Vue.set) | N/A |
| STORE-09 | Dynamic state access works | manual | Switch spell levels, verify data | N/A |
| STORE-10 | Vuex fully removed | manual | `npm ls vuex` returns empty | N/A |

### Sampling Rate
- **Per task commit:** `npm run prod` (build must succeed)
- **Per wave merge:** Manual smoke test: load sheet, edit fields, verify autosave, check print view
- **Phase gate:** Full manual verification per CLEAN-04/CLEAN-05 requirements

### Wave 0 Gaps
None -- no test infrastructure exists and adding one is out of scope for this migration phase.

## Open Questions

1. **JSON.stringify behavior with reactive proxy**
   - What we know: Vue 3's reactive proxy does not interfere with JSON.stringify -- internal Vue properties are non-enumerable and excluded from serialization. The `levelData` property (imported module reference) will be serialized but this is the same as current behavior.
   - What's unclear: Whether any edge cases exist with deeply nested reactive objects and JSON.stringify.
   - Recommendation: Verify by comparing JSON output before and after migration on a real sheet. The current store already serializes via `JSON.stringify(state)` in the Vuex action, so behavior should be identical.

2. **Object.assign and reactive proxy interaction**
   - What we know: `Object.assign(target, source)` where target is a reactive proxy works correctly -- it triggers reactivity for all assigned properties. This is documented Vue 3 behavior.
   - What's unclear: No concerns.
   - Recommendation: Use as planned per D-04.

## Sources

### Primary (HIGH confidence)
- Direct code analysis of all 18 components, store.js, app.js, print.js, mixins.js
- Vue 3 documentation: reactive(), computed(), watch() are stable core APIs since Vue 3.0

### Secondary (MEDIUM confidence)
- Vue 3 reactivity guide for Object.assign behavior with reactive proxies

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Vue 3 reactive/computed/watch are stable, well-documented APIs
- Architecture: HIGH - Pattern is a direct mechanical translation from Vuex to reactive()
- Pitfalls: HIGH - All identified through direct code analysis of the actual codebase
- Component inventory: HIGH - Every component read and cataloged

**Research date:** 2026-03-21
**Valid until:** 2026-04-21 (stable -- Vue 3 APIs are not changing)
