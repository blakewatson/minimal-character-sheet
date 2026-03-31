# Pitfalls Research

**Domain:** Vue 2 to Vue 3 migration (with Vuex to reactive(), Laravel Mix to Vite, Quill editor)
**Researched:** 2026-03-21
**Confidence:** HIGH

## Critical Pitfalls

### Pitfall 1: Autosave infinite loop from deep watch on reactive state

**What goes wrong:**
Replacing `$store.subscribe` with `watch(state, callback, { deep: true })` creates a watcher that fires on every nested property change. If the autosave callback or any code in the save path touches the reactive state (even indirectly -- e.g., updating `isSaving`, resetting retry state, or parsing response data back into state), the watcher fires again, creating an infinite loop. In Vue 2, `$store.subscribe` only fired on explicit `commit()` calls, providing natural batching. Deep watch has no such gate.

**Why it happens:**
The `Sheet.vue` autosave loop currently uses `$store.subscribe` which fires once per mutation. With `watch(state, ..., { deep: true })`, any property write triggers the callback -- including writes from within the save flow itself. The current code sets `this.hasUnsavedChanges`, `this.isSaving`, `this.isError`, `this.retryCount` -- if any of these were on the reactive state object (they are on the component, so they are safe), or if `updateState` writes back into state after a public sheet poll, the watcher would re-trigger.

**How to avoid:**
1. Keep the autosave watcher's scope tight: only watch the state object from the store, not component-local data.
2. Use a dirty flag pattern: set a `_skipWatch` flag before programmatic state updates (like `initializeState` and `updateState`), and check it in the watcher.
3. Debounce or throttle the watcher callback (the existing `throttledSave` with 5s throttle helps, but the watcher itself still fires on every change).
4. Test the public sheet polling flow specifically -- `updateState` calls `Object.assign(state, merged)` which triggers the deep watcher for every property.

**Warning signs:**
- Browser freezes or high CPU on page load (initializeState writes ~50 properties, each triggering the watcher)
- Network tab shows rapid-fire POST requests to `/sheet/:slug`
- Console shows recursive call stack in the watcher

**Phase to address:**
Phase 3 (Vuex to reactive). Must be tested immediately when `$store.subscribe` is replaced with `watch`.

---

### Pitfall 2: Object.assign on reactive state loses reactivity for new properties

**What goes wrong:**
The `initializeState` and `updateState` actions use `Object.assign(state, mergedData)` to bulk-hydrate state. If the merged data contains properties that do not exist in the original `reactive()` declaration, those new properties will not be reactive. Unlike Vue 2's `Vue.set()`, Vue 3's `reactive()` proxy does track dynamically added properties -- but only if the object was created with `reactive()`. The real risk is the opposite: if you accidentally replace the state object reference instead of mutating it (e.g., `state = reactive(newData)` instead of `Object.assign(state, newData)`), all component references to the old proxy become stale.

**Why it happens:**
The current `replaceState` mutation iterates `payload.state` properties and assigns them individually. The migration plan suggests using `Object.assign(state, newState)`. Both work, but a careless refactor might do `state = { ...state, ...newState }` which creates a new plain object, breaking the reactive proxy.

**How to avoid:**
1. Never reassign the `state` const. Always use `Object.assign(state, newData)` or property-by-property assignment.
2. Export `state` as `const` and never re-export a new object.
3. Ensure all state properties are declared in the initial `reactive()` call with defaults -- the current Vuex state already does this, so copy the shape exactly.

**Warning signs:**
- Components stop updating after `initializeState` runs
- State values appear correct in console but template does not re-render
- Only the first load works; subsequent `updateState` calls (public sheet polling) show stale data

**Phase to address:**
Phase 3 (Vuex to reactive). The `initializeState` and `replaceState` logic is the most delicate part of the store rewrite.

---

### Pitfall 3: SpellList computed property generates new IDs on every access, causing infinite re-renders

**What goes wrong:**
`SpellList.vue` line 109-113 has a computed property `spellItems` that maps over spells and assigns `spell.id = Math.random().toString()` on every access. In Vue 2, this mutates the Vuex state object (technically a violation, but Vue 2 is lenient about it). In Vue 3 with a reactive store, this computed property mutates reactive state on every read, which triggers the deep watcher, which triggers autosave, which triggers re-render, which re-reads the computed, which generates new IDs again -- an infinite loop.

**Why it happens:**
This is a Vue 2 anti-pattern that happened to work because Vuex strict mode was not enabled. The computed property has a side effect (mutating state). Vue 3's reactivity system is more strict about detecting mutations during render.

**How to avoid:**
1. Generate stable IDs when spells are created (in `addSpell`), not on every computed access.
2. If spells from old data lack IDs, add IDs during `initializeState` migration/normalization, not in computed properties.
3. Use the spell's array index + a stable prefix as the key if no ID exists, rather than generating random ones.

**Warning signs:**
- Browser freezes when viewing spell lists
- Vue devtools shows infinite re-render warnings
- Console shows "Maximum recursive updates exceeded" error (Vue 3 specific warning)

**Phase to address:**
Phase 3 (Vuex to reactive). Must fix this before or during the computed property migration. This is a data migration issue -- add IDs to existing spells in `initializeState`.

---

### Pitfall 4: Quill editor DOM manipulation conflicts with Vue 3 reactivity proxy

**What goes wrong:**
Quill creates its own DOM structure inside the component's root element (`this.$el`). Vue 3's reactivity system uses Proxies more aggressively than Vue 2. If the Quill instance is accidentally stored in reactive data (e.g., `this.editor = new Quill(...)` where `this` is a reactive component), Vue 3 wraps the Quill instance in a Proxy, which breaks Quill's internal `instanceof` checks, event handling, and DOM operations. The editor appears to render but becomes uneditable or throws errors.

**Why it happens:**
In Vue 2, `data()` properties were made reactive via `Object.defineProperty`, which did not wrap complex objects like Quill instances. In Vue 3, the Composition API's `reactive()` uses `Proxy` which wraps everything. However, Options API `data()` in Vue 3 also uses Proxies internally. The `QuillEditor.vue` stores `editor: null` in `data()` and later sets `this.editor = new Quill(this.$el, ...)`.

**How to avoid:**
1. Use `markRaw()` on the Quill instance: `this.editor = markRaw(new Quill(this.$el, ...))`.
2. Alternatively, store the Quill instance outside reactive data -- use a component-level variable (e.g., a `let` in `<script setup>` or a property set in `mounted()` that is not declared in `data()`).
3. Since you are staying with Options API, the simplest fix is `markRaw()`.

**Warning signs:**
- Quill editor renders content but clicking/typing does nothing
- Console errors about `contenteditable` or Quill internals
- Toolbar appears but has no effect when clicked
- Works on first mount but breaks after `quill-refresh` event

**Phase to address:**
Phase 2 (Vue 3 upgrade). Test Quill editors immediately after the Vue 3 upgrade, before touching the store.

---

### Pitfall 5: Vite manifest path and structure differs from Mix manifest

**What goes wrong:**
The PHP backend reads a manifest file to resolve hashed asset filenames. Mix writes `dist/mix-manifest.json` with the format `{ "/dist/app.js": "/dist/app.js?id=abc123" }`. Vite writes `dist/.vite/manifest.json` (since Vite 5) with a different structure: `{ "js/app.js": { "file": "assets/app-abc123.js", "css": ["assets/app-def456.css"] } }`. Getting the path wrong, the key format wrong, or missing CSS extraction breaks the entire app.

**Why it happens:**
Vite changed the manifest location between versions (it used to be `dist/manifest.json`, now it is `dist/.vite/manifest.json`). The key format uses the input path relative to project root, not the output path. CSS may be bundled into JS entry manifest records rather than being separate entries.

**How to avoid:**
1. Write the `vite()` PHP helper first and test it with a manual build before changing anything else.
2. The manifest key is the input path (e.g., `js/app.js`), not the output path.
3. Handle CSS extraction: Vite may list CSS files under the `css` array in the JS entry's manifest record. The PHP helper needs a `viteCss()` function to extract these.
4. Verify the `.vite` directory is not blocked by `.gitignore` or web server config (the `data/` directory is blocked; ensure `dist/.vite/` is not).

**Warning signs:**
- Blank page after build (assets not loading)
- PHP errors about missing manifest file
- CSS not loading (HTML loads but is unstyled)
- 404 errors for asset paths in browser network tab

**Phase to address:**
Phase 1 (Vite migration). This is the first thing to get right and must work before any Vue changes.

---

### Pitfall 6: Template filter syntax removal breaks all `| signedNumString` usages

**What goes wrong:**
Vue 3 completely removed the pipe filter syntax (`{{ value | filterName }}`). The codebase has 11 usages across 6 components (Ability.vue, SavingThrow.vue, Skills.vue, Print.vue, Spells.vue, Proficiency.vue). Every single one is a template compile error in Vue 3 -- the build will fail silently per-component or loudly at compile time.

**Why it happens:**
Filters look like they might just work since they look like regular template expressions. But the `|` operator in Vue 3 templates is treated as JavaScript bitwise OR, not a filter pipe. This means `{{ value | signedNumString }}` tries to do `value | signedNumString` (bitwise OR of a number and a function reference), producing garbage output or NaN rather than a compile error in some cases.

**How to avoid:**
1. Global search for `| signedNumString` in all `.vue` files and replace with function calls.
2. Add `signedNumString` as a global property via `app.config.globalProperties.$filters = { signedNumString }` and replace template usages with `$filters.signedNumString(value)`.
3. Alternatively, import the function in each component and use it as a method.
4. The migration plan already identifies this -- just do not skip any instances.

**Warning signs:**
- NaN displayed where modifier bonuses should appear (e.g., ability scores show NaN instead of +2)
- Template compile errors mentioning unexpected token

**Phase to address:**
Phase 2 (Vue 3 upgrade). Must be done as part of the Vue 3 conversion -- the app will not render correctly without it.

---

### Pitfall 7: `beforeDestroy` lifecycle hook silently ignored in Vue 3

**What goes wrong:**
Vue 3 renamed `beforeDestroy` to `beforeUnmount` and `destroyed` to `unmounted`. The old names are silently ignored -- no error, no warning. The codebase has 4 components using `beforeDestroy`: `QuillEditor.vue`, `Sheet.vue`, `Attacks.vue`, and `TrackableFields.vue`. If not renamed, event listeners and cleanup code never runs, causing memory leaks and stale event handlers.

**Why it happens:**
Vue 3 does not throw errors for unknown lifecycle hooks in Options API -- they are treated as regular methods. The `beforeDestroy` hook in `QuillEditor.vue` removes the `quill-refresh` event listener from the event bus. If this cleanup does not run, destroyed QuillEditor instances keep receiving events and trying to update destroyed DOM.

**How to avoid:**
1. Global search for `beforeDestroy` and `destroyed` in all `.vue` files and rename.
2. This is a mechanical find-and-replace but the consequences of missing one are subtle (memory leaks, not crashes).

**Warning signs:**
- Memory usage grows over time as user navigates between views
- Console errors referencing destroyed component DOM elements
- Event handlers firing on components that should no longer exist

**Phase to address:**
Phase 2 (Vue 3 upgrade). Mechanical change but must not be skipped.

---

### Pitfall 8: $attrs now includes class and style in Vue 3, causing duplicate attributes

**What goes wrong:**
In Vue 2, `$attrs` excludes `class` and `style`. In Vue 3, `$attrs` includes them. Components that use `v-bind="$attrs"` (QuillEditor.vue, Field.vue, AppDialog.vue) will now pass `class` and `style` through, potentially duplicating classes or overriding styles on the inner element. Additionally, Vue 3 removes `$listeners` -- listeners are now part of `$attrs`.

**Why it happens:**
Vue 3 unified the attrs/listeners model. This is a subtle change that does not cause errors but can cause visual regressions (duplicate classes, unexpected styling).

**How to avoid:**
1. Check each component that uses `v-bind="$attrs"` and verify the parent is not passing `class` or `style` that would conflict.
2. If needed, set `inheritAttrs: false` and manually control which attrs are applied.
3. For this codebase, the risk is LOW -- QuillEditor uses `$attrs` on its root div which already has classes. Vue 3 merges classes intelligently (appends rather than replaces), so this likely works fine. But verify visually.

**Warning signs:**
- Subtle CSS differences (double-applied classes, unexpected margins)
- QuillEditor styling looks different (extra padding, wrong font)

**Phase to address:**
Phase 2 (Vue 3 upgrade). Low priority but verify during visual testing.

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Keep Options API (no Composition API conversion) | Smaller migration scope, fewer file changes | Components are verbose; future Vue ecosystem moves toward Composition API | Acceptable for this migration -- scope control is correct |
| reactive() store without Pinia | Simpler for flat state, fewer dependencies | No devtools integration, no time-travel debugging, no action tracking | Acceptable given the store's simplicity (~50 flat properties, 2 getters) |
| Deep watch for autosave | Simple replacement for $store.subscribe | Performance cost of traversing all nested properties on every change | Acceptable -- state object is small. Would not scale to hundreds of properties |
| Direct state mutation from components | No mutation boilerplate | Harder to trace where state changes originate; no centralized validation | Acceptable given the validation was already minimal (allowedFields checks) |

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| Quill editor + Vue 3 | Storing Quill instance in reactive data | Use `markRaw()` or store outside reactive data |
| PHP manifest reader | Hardcoding manifest path from old Vite version docs | Check actual output: Vite 5+ uses `dist/.vite/manifest.json` |
| mitt event bus | Using `$emit`/`$on` method names (Vue-style) | mitt uses `emit`/`on` (no `$` prefix). Search-replace must catch all instances |
| Quill bubble CSS | Expecting Mix's `.copy()` behavior | Import CSS in app.css or configure Vite to handle it. Vite does not have a `copy()` equivalent out of the box |

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Deep watch on entire state object | Autosave fires on every keystroke; watcher traverses all nested objects | Throttle the save callback (already done with 5s throttle). Consider `watchEffect` targeting specific fields if perf degrades | Unlikely to break at this scale (50 props), but would matter at 500+ |
| SpellList computed generates random IDs every access | Infinite re-renders; key changes cause full DOM teardown/rebuild on every update | Generate stable IDs at creation time | Breaks immediately in Vue 3 with reactive store |
| Quill editor initialization per spell | Each spell entry creates a Quill instance with full toolbar | Already mitigated with lazy init (collapsed state renders static HTML) | Would matter at 50+ spells per level -- unlikely in practice |

## "Looks Done But Isn't" Checklist

- [ ] **Filter syntax:** All 11 `| signedNumString` usages converted -- verify by searching templates for `|` followed by a word character (bitwise OR of a number produces wrong output, not an error)
- [ ] **Lifecycle hooks:** All 4 `beforeDestroy` hooks renamed to `beforeUnmount` -- verify by searching for `beforeDestroy` (zero results expected)
- [ ] **Event bus methods:** All `$emit`/`$on`/`$off` on `window.sheetEvent` changed to `emit`/`on`/`off` -- verify by searching for `sheetEvent.$` (zero results expected)
- [ ] **Vue.set() calls:** All 14 instances in store.js and 1 in mixins.js removed -- verify by searching for `Vue.set` (zero results expected)
- [ ] **Store imports:** No component still imports from `vuex` -- verify by searching for `from 'vuex'` (zero results expected)
- [ ] **Manifest helper:** PHP `vite()` function works for both JS and CSS assets -- verify by loading app and checking network tab for 200 status on all assets
- [ ] **Quill markRaw:** Quill instance wrapped with `markRaw()` -- verify by editing a rich text field and confirming typing works
- [ ] **Autosave on public sheets:** Public read-only sheets should NOT trigger autosave -- verify that `watch` is not set up when `isPublic` is true
- [ ] **Print view:** Print.vue dispatches `initializeState` -- verify this still works with the new store (it uses `$store.dispatch` which must be replaced with direct function call)
- [ ] **SpellList IDs:** Spells have stable IDs that persist across re-renders -- verify by editing a spell and confirming other spells do not re-mount

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Autosave infinite loop | LOW | Add `{ flush: 'post' }` to watcher, add throttle/debounce, or switch to `watchEffect` tracking specific fields |
| Reactive proxy broken (state reassignment) | MEDIUM | Find the reassignment, change to `Object.assign`. May need to re-test all components that read state |
| Quill uneditable | LOW | Add `markRaw()` to Quill instance creation. One-line fix |
| Missing filter conversions (NaN display) | LOW | Search for `|` in templates, replace remaining instances. Mechanical fix |
| Manifest path wrong | LOW | Check `dist/` output, update path in PHP helper. One-line fix |
| SpellList infinite re-render | MEDIUM | Refactor ID generation into `initializeState` and `addSpell`. Need to update data migration logic |
| Lifecycle hooks not renamed | LOW | Global search and replace. Mechanical fix |

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Vite manifest path/structure | Phase 1 (Vite) | Load app in browser, confirm all assets return 200 in network tab |
| Template filter syntax removal | Phase 2 (Vue 3) | Search for `\| signedNumString` returns zero results; ability scores display correctly |
| beforeDestroy silent ignore | Phase 2 (Vue 3) | Search for `beforeDestroy` returns zero results |
| $attrs includes class/style | Phase 2 (Vue 3) | Visual comparison of QuillEditor and Field components before/after |
| Quill + Vue 3 reactivity proxy | Phase 2 (Vue 3) | Create and edit rich text in a Quill field; verify content saves and loads |
| Autosave infinite loop | Phase 3 (reactive store) | Load a sheet, make one edit, verify exactly one save request fires (after throttle) |
| Object.assign on reactive state | Phase 3 (reactive store) | Load a sheet, verify all fields populate; poll a public sheet, verify it updates |
| SpellList random ID re-render | Phase 3 (reactive store) | Open spell list, add a spell, verify existing spells do not flash/re-mount |
| Event bus method names | Phase 2 (Vue 3) | Search for `sheetEvent.$` returns zero results; autosave and Quill refresh work |

## Sources

- [Vue 3 Migration Guide - Breaking Changes](https://v3-migration.vuejs.org/breaking-changes/)
- [Vue 3 Watchers Documentation](https://vuejs.org/guide/essentials/watchers.html)
- [Avoiding Infinite Watch Loops in Vue Production Builds](https://iwasherefirst2.medium.com/avoiding-infinite-watch-loops-in-vue-production-builds-6d80ffc66029)
- [reactive() considered harmful](https://dev.to/ycmjason/thought-on-vue-3-composition-api-reactive-considered-harmful-j8c)
- [Common Mistakes When Creating Composition Functions in Vue](https://www.telerik.com/blogs/common-mistakes-creating-composition-functions-vue)
- [Vue 2 to Vue 3 Migration: Real Challenges and Solutions](https://medium.com/@karangandhi.dev/vue-2-to-vue-3-migration-real-world-challenges-and-fixes-952546966aff)
- [How to Fix Infinite Loop in Vue Watchers](https://oneuptime.com/blog/post/2026-01-24-vue-watcher-infinite-loop/view)
- [VueQuill - Rich Text Editor for Vue 3](https://vueup.github.io/vue-quill/)
- [Migration Tips from Laravel Mix to Vite](https://iwasherefirst2.medium.com/migration-tips-from-laravel-mix-to-vite-aaade1fd9d67)
- Codebase analysis: `js/store.js`, `js/components/QuillEditor.vue`, `js/components/Sheet.vue`, `js/components/SpellList.vue`, `js/mixins.js`

---
*Pitfalls research for: Vue 2 to Vue 3 migration (Vuex to reactive, Mix to Vite, Quill editor)*
*Researched: 2026-03-21*
