# Vue 3 + Vite Migration Plan

Migrate the character sheet app from Vue 2 + Vuex + Laravel Mix (webpack) to Vue 3 + `reactive()` composable + Vite.

## Current Stack

- **Vue 2.6** with Options API (29 SFCs in `js/components/`)
- **Vuex 3** — single store, no modules (`js/store.js`, ~750 lines)
- **Laravel Mix 6** (webpack wrapper) — config in `webpack.mix.js`
- **Tailwind CSS v4** via `@tailwindcss/postcss`
- **Three JS entry points**: `js/app.js` (sheet), `js/dashboard.js` (vanilla JS, no Vue), `js/print.js` (print view)
- **Output**: all built assets go to `dist/`, versioned via `mix-manifest.json`
- **PHP backend** reads `dist/mix-manifest.json` for cache-busted paths (`mix()` helper in `index.php`)

## Migration Phases

### Phase 1: Replace Laravel Mix with Vite

**Goal**: Drop webpack entirely. Get Vite building the same three entry points and CSS.

**What to do**:

1. Install Vite and the Vue plugin:
   ```
   npm install --save-dev vite @vitejs/plugin-vue
   ```

2. Create `vite.config.js` at project root:
   ```js
   import { defineConfig } from 'vite'
   import vue from '@vitejs/plugin-vue'

   export default defineConfig({
     plugins: [vue()],
     build: {
       outDir: 'dist',
       emptyOutDir: true,
       manifest: true,
       rollupOptions: {
         input: {
           app: 'js/app.js',
           dashboard: 'js/dashboard.js',
           print: 'js/print.js',
           styles: 'css/app.css'
         }
       }
     }
   })
   ```

3. Update `package.json` scripts:
   ```json
   {
     "dev": "vite build --mode development",
     "watch": "vite build --watch --mode development",
     "prod": "vite build"
   }
   ```
   Note: We are NOT using Vite's dev server (which would require HMR integration with the PHP backend). We're just using Vite as a build tool, same as Mix was used. The PHP app serves the files from `dist/` as before.

4. Update the PHP `mix()` helper in `index.php` to read Vite's manifest format instead. Vite writes `dist/.vite/manifest.json` (not `dist/mix-manifest.json`). The format differs:
   - **Mix manifest** (old): `{ "/dist/app.js": "/dist/app.js?id=abc123" }`
   - **Vite manifest** (new): `{ "js/app.js": { "file": "assets/app-abc123.js", "css": [...] } }`

   Replace the `mix()` function with a `vite()` function:
   ```php
   function vite($entry) {
       static $manifest;
       if (!$manifest) {
           $manifestPath = __DIR__ . '/dist/.vite/manifest.json';
           if (file_exists($manifestPath)) {
               $manifest = json_decode(file_get_contents($manifestPath), true);
           } else {
               $manifest = [];
           }
       }
       $entry = ltrim($entry, '/');
       if (isset($manifest[$entry])) {
           return '/dist/' . $manifest[$entry]['file'];
       }
       return '/dist/' . $entry;
   }
   ```
   Also add a helper to extract CSS files from the manifest (Vite may bundle CSS references into the JS entry's manifest record):
   ```php
   function viteCss($entry) {
       // Same manifest loading as above
       // Return array of CSS file paths from $manifest[$entry]['css']
   }
   ```

5. Update all template files that reference `mix()` to use `vite()` instead. Search `templates/` for `mix(` calls.

6. Handle the Quill CSS copy. Currently Mix copies `node_modules/quill/dist/quill.bubble.css` to `dist/`. With Vite, either:
   - Import it in `css/app.css` via `@import 'quill/dist/quill.bubble.css'`
   - Or keep copying it manually and reference it directly

7. Remove old build dependencies:
   ```
   npm uninstall laravel-mix resolve-url-loader sass sass-loader vue-loader vue-template-compiler
   ```
   Delete `webpack.mix.js` and `mix-manifest.json`.

**Verify**: Run `npm run dev`, confirm `dist/` contains the built assets, load the app in browser, confirm it works identically.

---

### Phase 2: Upgrade Vue 2 to Vue 3

**Goal**: Get all components running on Vue 3 with minimal changes. Keep Vuex temporarily to reduce simultaneous breakage.

**What to do**:

1. Update packages:
   ```
   npm install vue@3 vuex@4
   npm uninstall vue-template-compiler
   ```
   (vue-template-compiler is Vue 2 only; Vue 3 compiles templates via the Vite plugin)

2. **Rewrite entry points** — `js/app.js` and `js/print.js`:

   Current (`js/app.js`):
   ```js
   import Vue from 'vue';
   import Sheet from './components/Sheet';
   import store from './store';
   import { signedNumString } from './utils';
   import { i18nPlugin } from './i18n';

   Vue.use(i18nPlugin);
   window.sheetEvent = new Vue();
   Vue.filter('signedNumString', signedNumString);

   new Vue({
     el: '#sheet',
     store,
     render: (h) => h(Sheet),
   });
   ```

   New:
   ```js
   import { createApp } from 'vue';
   import Sheet from './components/Sheet.vue';
   import store from './store';
   import { signedNumString } from './utils';
   import { i18nPlugin } from './i18n';
   import { emitter } from './emitter';

   window.sheetEvent = emitter;
   window.md = window.markdownit({ html: true, linkify: true, typographer: true });

   const app = createApp(Sheet);
   app.use(store);
   app.use(i18nPlugin);
   app.config.globalProperties.$filters = { signedNumString };
   app.mount('#sheet');
   ```

   Same pattern for `js/print.js`.

3. **Replace the event bus** — create `js/emitter.js`:

   ```js
   import mitt from 'mitt';
   export const emitter = mitt();
   ```

   Install mitt: `npm install mitt`

   Then find-and-replace across the codebase:
   - `window.sheetEvent.$emit('x')` → `window.sheetEvent.emit('x')`
   - `window.sheetEvent.$on('x', fn)` → `window.sheetEvent.on('x', fn)`
   - `window.sheetEvent.$off('x', fn)` → `window.sheetEvent.off('x', fn)`

   Files affected:
   - `js/store.js` line 736: `$emit('quill-refresh')` → `emit('quill-refresh')`
   - `js/components/Sheet.vue` lines 139, 143: `$emit`/`$on` → `emit`/`on`
   - `js/components/QuillEditor.vue` lines 192, 198: `$on`/`$off` → `on`/`off`
   - `js/components/SpellList.vue` lines 132, 156: `$emit` → `emit`
   - `js/components/List.vue` line 123: `$emit` → `emit`

4. **Rewrite the i18n plugin** — `js/i18n.js`:

   Current:
   ```js
   export const i18nPlugin = {
     install(Vue) {
       Vue.prototype.$t = t;
       Vue.prototype.$getLocale = getLocale;
       Vue.prototype.$setLocale = setLocale;
     }
   };
   ```

   New (Vue 3 uses `app.config.globalProperties` instead of `Vue.prototype`):
   ```js
   export const i18nPlugin = {
     install(app) {
       app.config.globalProperties.$t = t;
       app.config.globalProperties.$getLocale = getLocale;
       app.config.globalProperties.$setLocale = setLocale;
     }
   };
   ```

5. **Replace global filters** — Vue 3 removed `Vue.filter()`.

   The only filter is `signedNumString` (from `js/utils.js`). In templates it's used as `{{ value | signedNumString }}`.

   Two options:
   - **Option A** (simplest): Find all template usages of `| signedNumString` and replace with a method call: `{{ signedNumString(value) }}`. Import the function in each component that uses it, or make it a global property.
   - **Option B**: Add it as a global property (`app.config.globalProperties.$filters = { signedNumString }`) and use `{{ $filters.signedNumString(value) }}` in templates.

   Search templates for `| signedNumString` to find all usages.

6. **Lifecycle hook renames** — find and replace:
   - `beforeDestroy` → `beforeUnmount`
   - `destroyed` → `unmounted`

   Files with `beforeDestroy`: search `js/components/` for these hooks. Known: `QuillEditor.vue`.

7. **Upgrade Vuex 3 → 4** (temporary, removed in Phase 3):

   In `js/store.js`, change:
   ```js
   import Vue from 'vue';
   import Vuex from 'vuex';
   Vue.use(Vuex);
   export default new Vuex.Store({ ... });
   ```
   To:
   ```js
   import { createStore } from 'vuex';
   export default createStore({ ... });
   ```

   Remove all `Vue.set()` calls (14 instances in `store.js`, 1 in `mixins.js`). In Vue 3, direct property assignment is reactive:
   - `Vue.set(state.abilities[i], 'score', payload.score)` → `state.abilities[i].score = payload.score`
   - `Vue.set(state.skills[payload.i], 'proficient', payload.proficient)` → `state.skills[payload.i].proficient = payload.proficient`
   - Same pattern for all 14 instances — just assign directly.

   The `import Vue from 'vue'` in `store.js` and `mixins.js` can then be removed entirely.

**Verify**: Full app works on Vue 3. All views (sheet, print, dashboard) load. Autosave works. Quill editors load and update. Skills, spells, attacks all function.

---

### Phase 3: Replace Vuex with `reactive()` composable

**Goal**: Remove Vuex entirely. Replace with a plain reactive object + computed values.

**Context on the current Vuex store** (`js/store.js`):

The store has:
- **~50 state properties** — a flat bag of character sheet data (HP, abilities, skills, spells, etc.)
- **2 getters**: `modifiers` (derived from abilities) and `proficiencyBonus` (derived from level/proficiencyOverride)
- **~30 mutations** — most are trivial property assignments. Some do array manipulation (sort, add, delete items).
- **2 actions**: `getJSON` (serializes state) and `initializeState`/`updateState` (hydrates state from server data)

Components access the store via:
- `mapState([...])` in computed — ~15 components
- `mapGetters([...])` in computed — ~7 components
- `this.$store.commit('mutationName', payload)` in methods — ~40 call sites
- `this.$store.dispatch('actionName', payload)` — 3 call sites
- `this.$store.state.someField` — a few direct accesses
- `this.$store.subscribe(callback)` — 1 usage in Sheet.vue for autosave (fires callback on every mutation)

**What to do**:

1. **Create `js/store.js` as a reactive composable**:

   ```js
   import { reactive, computed, watch } from 'vue';
   import levelData from './level-data';

   // The reactive state — same shape as the old Vuex state
   export const state = reactive({
     id: '',
     slug: '',
     is_2024: false,
     readOnly: false,
     levelData: levelData,
     level: 1,
     characterName: '',
     // ... all other properties, identical to current state definition
   });

   // Getters become computed refs
   export const modifiers = computed(() =>
     state.abilities.map(a => ({
       ability: a.name,
       val: Math.floor(parseInt(a.score) / 2 - 5)
     }))
   );

   export const proficiencyBonus = computed(() => {
     if (state.proficiencyOverride !== null && state.proficiencyOverride !== undefined) {
       return state.proficiencyOverride;
     }
     const row = state.levelData.find(data => state.level === data.lvl);
     return row ? row.proficiency : 2;
   });

   // Actions become plain functions
   export function getJSON() {
     return JSON.stringify(state);
   }

   export function initializeState(payload) {
     const sheet = JSON.parse(payload.sheet);
     const defaults = { /* deep copy of default state */ };
     const merged = sheet.data ? { ...defaults, ...sheet.data } : defaults;

     // ... same migration/normalization logic as current initializeState action

     Object.assign(state, merged);
   }

   export function updateState(payload) {
     // ... same logic as current updateState action
     // At end: window.sheetEvent.emit('quill-refresh')
   }
   ```

   **Important**: The `replaceState` mutation currently does `state[prop] = payload.state[prop]` in a loop. With `reactive()`, use `Object.assign(state, newState)` instead — this preserves the reactive proxy.

2. **Update every component** that uses the store. The pattern for each:

   **Before (Vuex)**:
   ```js
   import { mapState, mapGetters } from 'vuex';
   export default {
     computed: {
       ...mapState(['hp', 'maxHp', 'readOnly']),
       ...mapGetters(['modifiers', 'proficiencyBonus']),
     },
     methods: {
       updateHp(val) {
         this.$store.commit('updateVitals', { field: 'hp', val });
       }
     }
   }
   ```

   **After (reactive)**:
   ```js
   import { state, modifiers, proficiencyBonus } from '../store';
   export default {
     computed: {
       hp() { return state.hp; },
       maxHp() { return state.maxHp; },
       readOnly() { return state.readOnly; },
       modifiers() { return modifiers.value; },
       proficiencyBonus() { return proficiencyBonus.value; },
     },
     methods: {
       updateHp(val) {
         state.hp = val;  // Direct assignment — no commit needed
       }
     }
   }
   ```

   Alternatively, components can reference `state.hp` directly in templates if you prefer not to proxy through computed. But computed keeps templates unchanged.

3. **Eliminate mutations entirely**. Most mutations are trivial setters that can become direct assignments:

   | Old mutation call | New direct assignment |
   |---|---|
   | `$store.commit('updateVitals', { field: 'hp', val })` | `state.hp = val` |
   | `$store.commit('updateAbilityScore', { name, score })` | `state.abilities.find(a => a.name === name).score = score` |
   | `$store.commit('updateLevel', { level })` | `state.level = level` |
   | `$store.commit('addAttack')` | `state.attacks.push({ id: Date.now(), name: '', ... })` |
   | `$store.commit('deleteAttack', { id })` | `state.attacks = state.attacks.filter(a => a.id !== id)` |
   | `$store.commit('updateCoins', { i, amount })` | `state.coins[i].amount = amount` |

   Some mutations do allow-list validation (e.g., `updateBio` checks against `allowedFields`). Decide whether to keep that validation as helper functions or drop it (the fields are controlled by the UI, not user-typed input).

4. **Replace `$store.subscribe`** for autosave. Currently `Sheet.vue` uses `this.$store.subscribe()` to trigger autosave on every Vuex mutation. With `reactive()`, use Vue's `watch` with `deep: true`:

   ```js
   import { watch } from 'vue';
   import { state } from '../store';

   // In Sheet.vue mounted():
   watch(state, () => {
     window.sheetEvent.emit('autosave');
   }, { deep: true });
   ```

   Or use `watchEffect` if preferred. This fires whenever any property of `state` changes, same as `$store.subscribe` did.

5. **Replace `$store.dispatch('getJSON')`** — just call `getJSON()` directly:
   ```js
   import { getJSON } from '../store';
   const json = getJSON();
   ```

6. **Handle `mixins.js`** — the `listMixin` uses `Vue.set(this.items, i, value)`. Change to `this.items[i] = value` (Vue 3 reactivity handles array index assignment).

7. **Remove Vuex**: `npm uninstall vuex`. Remove `app.use(store)` from entry points.

**Component-by-component changelist** (source files, not `dist/`):

| Component | mapState fields | mapGetters | $store.commit calls | Notes |
|---|---|---|---|---|
| Sheet.vue | is_2024, readOnly | — | — | Has $store.subscribe (→ watch), $store.dispatch('getJSON') |
| Abilities.vue | abilities | modifiers | — | Read-only display |
| Ability.vue | readOnly, savingThrows | proficiencyBonus | updateAbilityScore, updateSavingThrow | |
| Skills.vue | skills, readOnly, passivePerceptionOverride | modifiers, proficiencyBonus | updateSkillProficiency, updatePassivePerceptionOverride, updateSkillModifierOverride | |
| SavingThrow.vue | readOnly | proficiencyBonus | updateSavingThrow | |
| Bio.vue | is_2024, level, characterName, className, race, background, alignment, xp, readOnly | — | updateLevel, updateBio | |
| Vitals.vue | hp, maxHp, tempHp, hitDie, totalHitDie, ac, speed, deathSaves, conditions, concentration, readOnly | — | updateVitals, updateDeathSaves | |
| Proficiency.vue | inspiration, readOnly, initiative, shortRests, proficiencyOverride | proficiencyBonus | updateInitiative, updateInspiration, updateShortRests, updateProficiencyOverride | |
| Attacks.vue | attacks, readOnly | modifiers | updateAttacks, deleteAttack, sortAttacks | Also `$store.commit('addAttack')` in template |
| TrackableFields.vue | trackableFields, readOnly | — | updateTrackableField, deleteTrackableField, sortTrackableField | Also `$store.commit('addTrackableField')` in template |
| Equipment.vue | coins, equipmentText, readOnly | — | updateCoins, updateEquipment | |
| Spells.vue | abilities, className, spClass, spAbility, spSave, spAttack, cantripsList, readOnly | modifiers | updateSpellInfo, updateListField | |
| SpellGroup.vue | readOnly | — | updateSpellSlots, updateExpendedSlots, updateSpellCollapsed | Accesses `$store.state[this.listField]` directly |
| SpellList.vue | — | — | updateSpellName, updateSpellPrepared, updateSpellCollapsed, addSpell, deleteSpell, sortSpells | Accesses `$store.state[this.listField].spells` directly |
| List.vue | — | — | updateListField, addToListField, deleteFromListField, sortListField | Accesses `$store.state[this.listField]` directly |
| TextSection.vue | equipmentText, proficienciesText, featuresText, personalityText, backstoryText, treasureText, notesText, organizationsText | — | updateTextField | |
| Tabs.vue | readOnly | — | — | Accesses `$store.state.slug` directly |
| Print.vue | (many fields) | modifiers, proficiencyBonus | — | Dispatches initializeState |

---

### Phase 4: Cleanup

1. Delete `webpack.mix.js` and `mix-manifest.json` (if not already done in Phase 1)
2. Update `CLAUDE.md` with new build commands and architecture
3. Update `.gitignore` if needed (Vite may output to `dist/.vite/`)
4. Verify `npm run prod` output is correct and PHP templates load all assets
5. Test all views: sheet editing, print view, dashboard, public read-only sheets

## Files That Change

### New files
- `vite.config.js`
- `js/emitter.js` (tiny mitt wrapper)

### Heavily modified
- `js/store.js` — complete rewrite from Vuex to reactive composable
- `js/app.js` — new Vue 3 createApp bootstrap
- `js/print.js` — same
- `js/i18n.js` — plugin install signature change (1 line)
- `js/mixins.js` — remove Vue.set (1 line)
- `index.php` — replace mix() with vite() helper

### Every component in `js/components/`
Each needs: remove vuex imports, import from store directly, replace mapState/mapGetters with computed wrappers or direct state access, replace $store.commit with direct state mutations. Mechanical but touches every file.

### Deleted
- `webpack.mix.js`
- `mix-manifest.json`

### Unchanged
- `js/dashboard.js` — vanilla JS, no Vue
- `css/app.css` — Tailwind, no changes
- All PHP models/controllers/templates (except asset path references)
- `js/utils.js`, `js/settings.js`, `js/level-data.js`

## Dependencies

### Remove
- `laravel-mix`
- `resolve-url-loader`
- `sass`, `sass-loader` (not actively used — CSS is Tailwind/PostCSS)
- `vue-loader` (Vite handles this)
- `vue-template-compiler` (Vue 2 only)
- `vuex`

### Add
- `vite`
- `@vitejs/plugin-vue`
- `mitt`

### Keep
- `vue` (upgrade ^2.6 → ^3)
- `@tailwindcss/postcss`
- `notyf`
- `quill`
- `prettier`, `prettier-plugin-tailwindcss`

## Risks and Gotchas

1. **`$store.subscribe` → `watch(state, ..., { deep: true })`**: Deep watching a large reactive object can have performance implications. In practice this state object is small enough that it won't matter. If it does, use `watchEffect` with specific fields or debounce.

2. **Quill editor**: The QuillEditor component manually creates a Quill instance on `this.$el`. This should still work in Vue 3 but test carefully — Quill's DOM manipulation can conflict with Vue's reactivity.

3. **Template filter syntax**: `{{ value | filter }}` pipe syntax is removed in Vue 3. Every instance must be converted to a function call. Missing even one will cause a template compile error, so this will be caught immediately.

4. **`hasOwnProperty` checks in mutations**: Several mutations guard with `state.hasOwnProperty(field)`. With Vue 3's Proxy-based reactivity, `hasOwnProperty` still works on reactive objects, but consider switching to `field in state` for clarity.

5. **Dynamic `$store.state[this.listField]` access**: Components like SpellGroup, SpellList, and List access store state dynamically by field name (e.g., `this.$store.state[this.listField]`). With the reactive composable, this becomes `state[this.listField]` — still works fine with reactive objects.

6. **Vite manifest path**: Vite outputs manifest to `dist/.vite/manifest.json` by default (as of Vite 5). Make sure the PHP helper reads from the correct path.

7. **Asset paths in templates**: Vite hashes filenames by default (`app-abc123.js`). The PHP `vite()` helper handles this via the manifest lookup, but verify all template references go through the helper.
