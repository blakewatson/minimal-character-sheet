# Architecture Research

**Domain:** Vue 2 to Vue 3 migration with Vite build tooling and PHP backend
**Researched:** 2026-03-21
**Confidence:** HIGH

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     PHP Backend (Fat-Free)                       │
│  index.php routes ──> templates/ ──> vite() helper ──> dist/    │
│  Serves HTML, reads build manifest, provides REST endpoints     │
└──────────────┬──────────────────────────────────────┬───────────┘
               │ (HTML with <script type="module">)   │ (POST/GET)
               ▼                                      ▲
┌──────────────────────────────────────────────────────────────────┐
│                     Browser / Vue 3 App                           │
│                                                                  │
│  ┌──────────┐   ┌──────────┐   ┌──────────┐                     │
│  │ app.js   │   │ print.js │   │dashboard │  Entry points       │
│  │ (Sheet)  │   │ (Print)  │   │.js (no   │  (each a separate   │
│  │          │   │          │   │  Vue)     │   Vite input)       │
│  └────┬─────┘   └────┬─────┘   └──────────┘                     │
│       │              │                                           │
│       ▼              ▼                                           │
│  ┌──────────────────────────────────────────┐                    │
│  │          Reactive Store (store.js)        │                    │
│  │  reactive() state + computed getters      │                    │
│  │  Imported directly by every component     │                    │
│  └──────────────────────┬───────────────────┘                    │
│                         │                                        │
│       ┌─────────────────┼─────────────────┐                      │
│       ▼                 ▼                 ▼                       │
│  ┌─────────┐   ┌──────────────┐   ┌──────────────┐              │
│  │ Section │   │ List-based   │   │ Utility      │              │
│  │ Comps   │   │ Components   │   │ Components   │              │
│  │ (Bio,   │   │ (Attacks,    │   │ (Tabs,Field, │              │
│  │ Vitals, │   │ SpellList,   │   │ QuillEditor) │              │
│  │ Skills) │   │ List)        │   │              │              │
│  └─────────┘   └──────────────┘   └──────────────┘              │
│                                                                  │
│  ┌──────────────────────────────────────────┐                    │
│  │          Cross-cutting concerns           │                    │
│  │  emitter (mitt) | i18n plugin | settings  │                    │
│  └──────────────────────────────────────────┘                    │
└──────────────────────────────────────────────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────────────────────────────┐
│                     Vite (build tool only)                        │
│  vite.config.js ──> dist/ with .vite/manifest.json               │
│  No dev server. Build + watch mode only.                         │
└──────────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Component | Responsibility | Communicates With |
|-----------|----------------|-------------------|
| **PHP Backend** | Routing, auth, serving HTML templates, reading build manifest, REST API for sheet CRUD | Browser (HTML + JSON responses) |
| **vite() PHP helper** | Reads `dist/.vite/manifest.json`, resolves hashed asset paths for templates | Templates (called inline) |
| **app.js entry** | Creates Vue app, mounts Sheet, registers plugins (i18n), sets up emitter | Store, Sheet component |
| **print.js entry** | Creates Vue app, mounts Print component (read-only sheet view) | Store, Print component |
| **dashboard.js** | Vanilla JS, no Vue -- manages sheet list UI | PHP backend directly |
| **Reactive Store** | Holds all character sheet state (~50 properties), exposes computed getters, serialization | Every Vue component imports it directly |
| **Sheet.vue** | Root component: autosave loop, public sheet polling, retry logic, view routing | Store (watch for autosave), emitter (autosave events), PHP backend (fetch) |
| **Section components** | Render/edit specific parts of the character sheet (Bio, Vitals, Skills, etc.) | Store (read/write state directly) |
| **List components** | Handle dynamic lists (attacks, spells, spell groups, trackable fields) | Store (read/write arrays by field name) |
| **QuillEditor** | Rich text editing with manual DOM management | Store (reads/writes text fields), emitter (quill-refresh events) |
| **emitter (mitt)** | Cross-component event bus for autosave triggers and quill refresh | Sheet.vue, QuillEditor, SpellList, List |
| **i18n plugin** | Provides `$t()` translation method on all components | All components via globalProperties |

## Vite + PHP Integration Architecture

### The Build-Only Pattern

This app uses Vite strictly as a build tool. The PHP backend serves all HTML. There is no Vite dev server, no HMR, no middleware proxy. This is the simplest integration pattern.

**How it works:**

1. `vite build` compiles JS/CSS entry points into `dist/` with content-hashed filenames
2. Vite writes `dist/.vite/manifest.json` mapping source paths to output paths
3. PHP's `vite()` helper reads this manifest at runtime to resolve asset URLs
4. Templates use `{{ vite('js/app.js') }}` instead of `{{ mix('/app.js') }}`

**Vite manifest format** (differs significantly from Mix manifest):

```json
{
  "js/app.js": {
    "file": "assets/app-BRBmoGS9.js",
    "src": "js/app.js",
    "isEntry": true,
    "css": ["assets/app-5UjPuW-k.css"]
  },
  "css/app.css": {
    "file": "assets/app-ChJ_j-JJ.css",
    "src": "css/app.css",
    "isEntry": true
  }
}
```

**Critical change: `<script type="module">`**

Vite outputs ES modules. The template `<script>` tags must become `<script type="module">`. This is a hard requirement -- without it, `import` statements in the built code will fail. The `vite()` PHP helper or the templates themselves must handle this.

### PHP Helper Design

```php
function vite(string $entry): string {
    static $manifest = null;
    if ($manifest === null) {
        $path = __DIR__ . '/dist/.vite/manifest.json';
        $manifest = file_exists($path)
            ? json_decode(file_get_contents($path), true)
            : [];
    }
    $entry = ltrim($entry, '/');
    return isset($manifest[$entry])
        ? '/dist/' . $manifest[$entry]['file']
        : '/dist/' . $entry;
}

function viteCss(string $entry): array {
    // Same manifest loading pattern
    // Returns $manifest[$entry]['css'] array or empty array
    // Needed because Vite may bundle CSS references into JS entry metadata
}
```

**Template changes required:**

| Before (Mix) | After (Vite) |
|---|---|
| `<script src="{{ mix('/app.js') }}">` | `<script type="module" src="{{ vite('js/app.js') }}">` |
| `<link href="{{ mix('/app.css') }}">` | `<link href="{{ vite('css/app.css') }}">` |
| `<link href="{{ mix('/quill.bubble.css') }}">` | Import in CSS or `<link href="{{ vite('quill.bubble.css') }}">` |

### Static CSS Assets (quill, notyf, baguetteBox)

Currently, Mix copies `quill.bubble.css` to `dist/`, and other vendor CSS files (`notyf.min.css`, `baguetteBox.min.css`) already live in `dist/` as pre-built files. Two options:

**Option A (recommended): Import vendor CSS into app.css.** Add `@import 'quill/dist/quill.bubble.css'` to `css/app.css`. Vite resolves `node_modules` imports automatically. This eliminates the separate file and the copy step. For notyf, `@import 'notyf/notyf.min.css'`. This bundles everything into one CSS output.

**Option B: Use Vite's `vite-plugin-static-copy`.** Copies files to `dist/` during build, similar to Mix's `.copy()`. More moving parts for no benefit in this small app.

Option A is better because it reduces HTTP requests and simplifies the manifest -- one CSS file instead of several.

## Reactive Store Architecture

### Current Vuex Pattern

```
Component ──mapState──> Vuex state (read)
Component ──$store.commit('mutation', payload)──> Vuex mutations (write)
Component ──$store.dispatch('action')──> Vuex actions (async)
Sheet.vue ──$store.subscribe(cb)──> fires on every mutation (autosave trigger)
```

### New reactive() Pattern

```
Component ──import { state }──> reactive object (read)
Component ──state.field = value──> direct assignment (write)
Component ──import { getJSON }──> plain function call (actions)
Sheet.vue ──watch(state, cb, { deep: true })──> fires on any change (autosave trigger)
```

### Store Module Design

```js
// js/store.js -- the entire "store" is one module
import { reactive, computed, watch } from 'vue';

// 1. State: a single reactive object
export const state = reactive({ /* ~50 flat properties */ });

// 2. Getters: exported computed refs
export const modifiers = computed(() => /* derive from state.abilities */);
export const proficiencyBonus = computed(() => /* derive from state.level */);

// 3. Actions: exported plain functions
export function initializeState(payload) { /* Object.assign(state, ...) */ }
export function updateState(payload) { /* merge + emit quill-refresh */ }
export function getJSON() { return JSON.stringify(state); }
```

**Why this works for Options API components:**

Components stay in Options API. They import the store module and expose state via computed properties. No Composition API rewrite needed.

```js
// Any component
import { state, modifiers } from '../store';

export default {
  computed: {
    hp() { return state.hp; },
    modifiers() { return modifiers.value; },
  },
  methods: {
    updateHp(val) { state.hp = val; }
  }
}
```

### Key Design Decision: No Pinia

Pinia is the "official" Vue 3 state management library, but it adds unnecessary abstraction here. The store is:
- A flat bag of ~50 properties (no modules, no namespacing)
- Two computed getters
- Trivial mutations (direct property assignment)
- Two async actions (serialize and hydrate)

A `reactive()` singleton handles this with zero dependencies and zero boilerplate. Pinia would add `defineStore`, `storeToRefs`, and plugin registration for no practical benefit.

### Autosave Architecture Change

**Before:** `$store.subscribe()` fires a callback on every Vuex mutation. Sheet.vue uses this to trigger autosave.

**After:** `watch(state, callback, { deep: true })` fires on any reactive property change. Functionally identical.

```js
// In Sheet.vue mounted()
import { watch } from 'vue';
import { state } from '../store';

watch(state, () => {
  window.sheetEvent.emit('autosave');
}, { deep: true });
```

**Performance note:** The state object has ~50 top-level properties with some nested arrays (abilities, skills, spells). Deep watching this is fine -- Vue 3's proxy-based reactivity is efficient, and this object is small. No debouncing of the watch itself is needed because the autosave handler already throttles saves to every 5 seconds.

## Data Flow

### Edit Flow (Authenticated User)

```
User types in field
    |
    v
Component method fires (e.g., updateHp)
    |
    v
Direct state mutation: state.hp = newValue
    |
    v
watch(state, ...) fires in Sheet.vue
    |
    v
emitter.emit('autosave')
    |
    v
throttledSave() queues save (5s trailing throttle)
    |
    v
getJSON() serializes state
    |
    v
fetch POST /sheet/:slug with JSON body
    |
    v
PHP backend saves to SQLite
```

### Public Sheet Refresh Flow (Read-Only)

```
setInterval(refreshLoop, 30000)
    |
    v
fetch GET /sheet-data/:slug?updated_at=...
    |
    v
Server returns sheet data (if updated)
    |
    v
updateState(payload) -- Object.assign into reactive state
    |
    v
emitter.emit('quill-refresh')
    |
    v
QuillEditor listens, re-renders rich text content
```

### Print View Flow

```
print.js creates Vue app with Print component
    |
    v
initializeState(window.sheet) hydrates reactive state
    |
    v
Print component reads state via computed properties
    |
    v
Purely read-only rendering, no autosave, no event bus needed
```

## Migration Build Order (Dependencies)

The migration has strict ordering constraints based on what depends on what.

### Phase 1: Vite Build Tool (independent of Vue version)

```
1. Create vite.config.js
2. Write vite() PHP helper
3. Update template references (mix -> vite, add type="module")
4. Handle vendor CSS (import into app.css or copy)
5. Update package.json scripts
6. Remove Mix dependencies
7. Delete webpack.mix.js
```

**Why first:** This is orthogonal to the Vue upgrade. Vite can build Vue 2 code just fine with `@vitejs/plugin-vue2`. However, since the plan is to upgrade Vue immediately after, and `@vitejs/plugin-vue` (Vue 3) is the default, it makes sense to do this phase and the Vue 3 upgrade in close succession. The risk of doing Vite first with Vue 2 is needing `@vitejs/plugin-vue2` temporarily. The alternative is to do Vite + Vue 3 simultaneously in one phase, but that creates too many variables if something breaks.

**Recommendation:** Use `@vitejs/plugin-vue2` for this phase so you can verify the build pipeline works before changing Vue. Then swap to `@vitejs/plugin-vue` in Phase 2.

### Phase 2: Vue 3 Upgrade (depends on Phase 1)

```
1. npm install vue@3 vuex@4, remove vue-template-compiler
2. Swap @vitejs/plugin-vue2 -> @vitejs/plugin-vue in vite.config.js
3. Rewrite entry points (createApp pattern)
4. Create emitter.js (mitt), replace event bus calls
5. Update i18n plugin (Vue.prototype -> app.config.globalProperties)
6. Replace Vue.filter() with function calls or global properties
7. Rename lifecycle hooks (beforeDestroy -> beforeUnmount)
8. Upgrade Vuex 3 -> 4 (createStore pattern)
9. Remove all Vue.set() calls (direct assignment works in Vue 3)
```

**Why keep Vuex temporarily:** Changing the store pattern simultaneously with the Vue version creates too many failure points. With Vuex 4, all existing `mapState`/`mapGetters`/`$store.commit` patterns continue working. This lets you verify the Vue 3 upgrade in isolation.

### Phase 3: Vuex to reactive() (depends on Phase 2)

```
1. Rewrite store.js as reactive() composable
2. Update Sheet.vue (subscribe -> watch, dispatch -> function call)
3. Update each component (mapState -> computed, commit -> direct assignment)
4. Update mixins.js (Vue.set -> direct assignment)
5. Remove Vuex dependency
```

**Component update order within Phase 3:**

Update in dependency order -- leaf components first, container components last:

1. **Store itself** -- write the new store.js
2. **Utility components** (Field, QuillEditor, Tabs) -- fewest store dependencies
3. **Leaf section components** (Ability, SavingThrow, Proficiency) -- simple state reads
4. **List components** (List, SpellList, SpellGroup) -- dynamic `state[fieldName]` access
5. **Section containers** (Bio, Vitals, Skills, Attacks, Equipment, Spells, TextSection, TrackableFields) -- moderate store usage
6. **Sheet.vue** -- autosave watcher, the most complex migration
7. **Print.vue** -- read-only, straightforward but touches many state fields

This order means you can test incrementally. Each updated component works alongside not-yet-updated ones because the reactive store is a module-level singleton -- components that still import from Vuex would break, so in practice the store rewrite and component updates must happen together within a single phase. The ordering above is about which files to edit first for least cognitive load.

### Phase 4: Cleanup (depends on Phase 3)

```
1. Delete webpack.mix.js if not done
2. Update documentation (CLAUDE.md)
3. Update .gitignore (dist/.vite/)
4. Full regression test
```

## Anti-Patterns

### Anti-Pattern 1: Using Vite Dev Server with PHP Backend

**What people do:** Configure Vite's dev server as a proxy in front of PHP, enabling HMR
**Why it's wrong for this project:** Adds significant complexity (proxy config, CORS, two servers running). The app is a small character sheet, not a large SPA where HMR saves meaningful time. `vite build --watch` rebuilds in under a second.
**Do this instead:** Use `vite build --watch` for development. Page reload is fine.

### Anti-Pattern 2: Gradual Component-by-Component Store Migration

**What people do:** Try to migrate some components to the new store while leaving others on Vuex, running both stores in parallel
**Why it's wrong:** Two sources of truth for the same state. Autosave watches one but not the other. Components read stale data.
**Do this instead:** Migrate the store and all components in one atomic phase. The store rewrite is mechanical (search-and-replace patterns), not creative, so doing it all at once is realistic.

### Anti-Pattern 3: Wrapping reactive() in a Composable Function

**What people do:** Create `useStore()` composable that returns `{ state, modifiers, ... }` -- calling the function in each component's `setup()`
**Why it's wrong for Options API:** The app is staying with Options API. Composable functions are designed for `setup()`. With Options API, you'd need to add a `setup()` function to every component just to call `useStore()`, which defeats the purpose of staying on Options API.
**Do this instead:** Export `state`, `modifiers`, `proficiencyBonus`, and action functions directly from the module. Import them in the `<script>` section and reference in computed/methods. No `setup()` needed.

### Anti-Pattern 4: Overcomplicating the vite() PHP Helper

**What people do:** Build elaborate PHP packages or middleware to handle Vite integration, with dev server detection, HMR injection, preload tag generation
**Why it's wrong for this project:** The helper needs to do exactly one thing: look up a source path in manifest.json and return the hashed output path. That's 15 lines of PHP.
**Do this instead:** Write a minimal `vite()` function. Add `viteCss()` only if entry points have associated CSS in the manifest. Skip preload tags -- the app has 3 JS files and 1 CSS file; preloading gains nothing.

## Integration Points

### External Services

| Service | Integration Pattern | Migration Impact |
|---------|---------------------|------------------|
| PHP Backend (F3) | REST API via fetch, HTML templates with asset helpers | Replace `mix()` with `vite()`, add `type="module"` to script tags |
| SQLite | Accessed only by PHP, stores sheet JSON blobs | No impact |
| Quill Editor | Loaded as npm dependency, creates DOM elements | Test carefully after Vue 3 upgrade -- Quill manages its own DOM |
| markdown-it | Loaded via `<script>` tag as `window.markdownit` | No change -- external to Vue |

### Internal Boundaries

| Boundary | Communication | Migration Notes |
|----------|---------------|-----------------|
| Store <-> Components | Module import (reactive state + computed refs) | Every component file changes from Vuex imports to direct store imports |
| Sheet <-> Children | Props down for UI state (view, saving status); store for data | No structural change -- just the store access pattern changes |
| Components <-> Emitter | `window.sheetEvent.emit/on` for cross-cutting events | Method names change ($emit->emit, $on->on) but pattern stays |
| PHP Templates <-> JS | Global variables (`window.sheet`, `window.is_2024`) injected by PHP | No change -- Vite doesn't affect how PHP injects data |
| PHP Templates <-> Build Output | `vite()` helper resolves manifest paths | Manifest format and helper function change; template references update |

## Sources

- [Vite Backend Integration Guide](https://vite.dev/guide/backend-integration) -- official docs on manifest format and PHP integration
- [Vite Build Options](https://vite.dev/config/build-options) -- manifest configuration reference
- [Vue 3 Reactivity: reactive() and computed()](https://vuejs.org/guide/essentials/reactivity-fundamentals.html) -- official Vue 3 docs
- [Vue.js Developers: Composition API as Vuex Replacement](https://vuejsdevelopers.com/2020/10/05/composition-api-vuex/) -- pattern analysis for reactive() store
- Existing migration plan: `plans/vue3-migration.md` -- project-specific component-by-component changelist

---
*Architecture research for: Vue 2 to Vue 3 migration with Vite + PHP backend*
*Researched: 2026-03-21*
