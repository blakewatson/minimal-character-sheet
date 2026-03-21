# Project Research Summary

**Project:** Minimal Character Sheet -- Vue 3 Migration
**Domain:** Frontend framework migration (Vue 2 + Vuex + Laravel Mix to Vue 3 + reactive() + Vite)
**Researched:** 2026-03-21
**Confidence:** HIGH

## Executive Summary

This project is a Vue 2 to Vue 3 migration of a D&D 5e character sheet web app with a PHP (Fat-Free Framework) backend. The app has 26 Vue single-file components, a flat Vuex store with ~50 properties, and uses Laravel Mix (webpack) for builds. The migration also replaces the build tool (Laravel Mix to Vite 8) and the state management layer (Vuex to a plain `reactive()` composable). Research across all four areas converges on a clear, well-documented migration path with high confidence.

The recommended approach is a three-phase migration with strict ordering: build tool first, then Vue framework upgrade (with Vuex 4 as a temporary bridge), then store replacement. This ordering isolates variables so that when something breaks, the cause is obvious. The app is small enough that each phase can be completed in a single pass without incremental or partial migration strategies. The `@vue/compat` migration build is unnecessary -- the breaking changes are mechanical and well-understood for a codebase this size.

The primary risks are concentrated in Phase 3 (store migration): an autosave infinite loop caused by deep watching the reactive state, a SpellList computed property that generates random IDs on every access (causing infinite re-renders), and accidentally breaking the reactive proxy by reassigning the state object. Phase 2 has one critical risk: Quill editor DOM manipulation conflicting with Vue 3's Proxy-based reactivity. All of these have known, low-cost fixes documented in the pitfalls research.

## Key Findings

### Recommended Stack

The stack choices are straightforward with no contested decisions. Vite 8 (current stable) replaces Laravel Mix as the build tool in build-only mode -- no dev server, just `vite build` and `vite build --watch`. Vue 3.5.x is the target framework version. The Tailwind CSS integration switches from `@tailwindcss/postcss` to `@tailwindcss/vite` for tighter Vite integration.

**Core technologies:**
- **Vite 8** with `@vitejs/plugin-vue` v6: Build tool replacing Laravel Mix. Rolldown-based, 10-30x faster builds. Build-only mode with PHP backend integration via manifest.json.
- **Vue 3.5.x**: Target framework. Options API fully supported -- no Composition API conversion needed.
- **Vuex 4**: Temporary bridge during Phase 2 only. Removed in Phase 3.
- **reactive() composable**: Final state management. A single reactive object with computed getters. No Pinia -- the store is too simple to justify it.
- **mitt**: Event bus replacement (200 bytes). Drop-in for Vue 2's instance-as-event-bus pattern.
- **@tailwindcss/vite**: Replaces @tailwindcss/postcss for Vite projects.

**Packages to remove:** laravel-mix, vue-template-compiler, vue-loader, resolve-url-loader, sass, sass-loader, @tailwindcss/postcss, vuex (after Phase 3).

See [STACK.md](STACK.md) for version details, installation commands, and alternatives considered.

### Expected Features

This is a migration, not a feature build. All work items are table stakes for a correct, functional migration. There are no user-facing feature additions.

**Must have (migration correctness):**
- Replace `new Vue()` with `createApp()` and all related API changes
- Replace event bus (`new Vue()` as emitter) with mitt
- Remove all `Vue.set()` calls (14 in store.js, 1 in mixins.js)
- Remove template filter syntax (11 usages of `| signedNumString`)
- Rename lifecycle hooks (`beforeDestroy` to `beforeUnmount` in 4 components)
- Fix `v-model` prop/event changes (`value`/`input` to `modelValue`/`update:modelValue`)
- Replace Vuex with reactive() composable and update all 26 components
- Replace Laravel Mix with Vite and update PHP manifest helper
- Handle Quill CSS bundling (import into app.css instead of Mix's copy)

**Should have (code quality during migration):**
- Add `emits` option declarations to components
- Centralize vendor CSS imports (quill, notyf) into app.css

**Defer (post-migration):**
- Composition API / `<script setup>` conversion
- TypeScript adoption
- Pinia adoption (only if store complexity grows)
- Vite dev server with HMR (complexity not justified for this app size)

See [FEATURES.md](FEATURES.md) for the complete feature landscape, dependency graph, and prioritization matrix.

### Architecture Approach

The architecture stays fundamentally unchanged: PHP backend serves HTML templates, Vue handles client-side interactivity, and the build tool compiles assets to `dist/`. The key architectural change is the store access pattern -- moving from Vuex's `mapState`/`commit` indirection to direct module imports of a reactive singleton. Components import `state` and `modifiers` directly from `store.js` and expose them via computed properties in Options API.

**Major components:**
1. **Vite build pipeline** -- Compiles 3 JS entry points + 1 CSS entry to `dist/` with content-hashed filenames and a manifest file
2. **PHP vite() helper** -- Reads `dist/.vite/manifest.json` to resolve hashed asset paths in templates (replaces `mix()` helper)
3. **Reactive store (store.js)** -- Single `reactive()` object with ~50 properties, 2 `computed()` getters, and exported action functions
4. **Event emitter (mitt)** -- Cross-component communication for autosave triggers and Quill refresh events
5. **26 Vue components** -- All stay in Options API, all update from Vuex imports to direct store imports

See [ARCHITECTURE.md](ARCHITECTURE.md) for system diagrams, data flow, store design, and anti-patterns to avoid.

### Critical Pitfalls

Research identified 8 pitfalls. The top 5 that require active prevention:

1. **Autosave infinite loop from deep watch** -- When replacing `$store.subscribe` with `watch(state, ..., { deep: true })`, any state write from the save flow re-triggers the watcher. Prevention: keep save-status flags on the component (not in store state), use the existing 5s throttle, and test with a single edit to verify exactly one save fires.

2. **SpellList computed generates random IDs on every access** -- `SpellList.vue` assigns `Math.random()` IDs in a computed property, which mutates reactive state during render, causing infinite re-renders. Prevention: generate stable IDs in `initializeState` and `addSpell`, not in computed properties.

3. **Quill editor breaks under Vue 3 Proxy** -- Storing the Quill instance in reactive data wraps it in a Proxy, breaking Quill's internal `instanceof` checks. Prevention: wrap with `markRaw()` at creation time.

4. **Vite manifest path/structure differs from Mix** -- Different location (`dist/.vite/manifest.json`), different key format (input paths, not output paths), and CSS may be nested under JS entries. Prevention: write and test the PHP helper first, before any Vue changes.

5. **Template filter syntax produces garbage, not errors** -- `{{ value | filterName }}` becomes bitwise OR in Vue 3, producing NaN instead of a compile error. Prevention: search-and-replace all 11 instances before testing.

See [PITFALLS.md](PITFALLS.md) for all 8 pitfalls with warning signs, recovery strategies, and a verification checklist.

## Implications for Roadmap

Based on research, the migration has a natural 4-phase structure dictated by strict dependency ordering. Phases cannot be reordered or merged without increasing risk.

### Phase 1: Build Tool Migration (Vite)
**Rationale:** The build pipeline must work before any framework code changes. Vite is orthogonal to the Vue version -- it can build Vue 2 code. Doing this first means you have a known-good build when Vue 3 issues arise.
**Delivers:** Working Vite build producing identical output to Laravel Mix. PHP templates load assets via new `vite()` helper.
**Addresses:** Vite config, PHP manifest helper, template asset references, vendor CSS bundling, npm script updates, webpack/Mix dependency removal.
**Avoids:** Pitfall 5 (manifest path/structure) -- test immediately after writing the PHP helper.
**Note:** Use `@vitejs/plugin-vue2` temporarily so the existing Vue 2 code builds under Vite. Swap to `@vitejs/plugin-vue` in Phase 2.

### Phase 2: Vue 3 Framework Upgrade
**Rationale:** Depends on Phase 1 (working Vite build). Uses Vuex 4 as a bridge so all existing store access patterns continue working while Vue 3 API changes are made.
**Delivers:** App running on Vue 3 with all breaking changes addressed. All 26 components compile and render correctly. Quill editor works.
**Addresses:** createApp() bootstrap, event bus (mitt), i18n plugin, filter removal, lifecycle hook renames, Vue.set() removal, v-model changes, Vuex 3 to 4 upgrade.
**Avoids:** Pitfall 4 (Quill + Proxy -- use markRaw), Pitfall 6 (filter syntax -- search-and-replace all 11), Pitfall 7 (lifecycle hooks -- search-and-replace all 4), Pitfall 8 ($attrs includes class/style -- visual verification).

### Phase 3: Store Migration (Vuex to reactive)
**Rationale:** Depends on Phase 2 (Vue 3 installed, reactive()/computed()/watch() APIs available). This is the highest-risk phase and the most labor-intensive (touching all 26 components). Isolating it from the Vue 3 upgrade means store-related bugs are clearly attributable.
**Delivers:** Vuex fully removed. Store is a plain reactive() module. All components use direct imports. Autosave works via watch().
**Addresses:** Store rewrite, all 26 component updates, autosave watch replacement, listMixin update, Vuex uninstall.
**Avoids:** Pitfall 1 (autosave infinite loop -- test immediately), Pitfall 2 (reactive proxy breakage -- never reassign state const), Pitfall 3 (SpellList IDs -- fix during store rewrite).

### Phase 4: Cleanup and Verification
**Rationale:** Final phase to remove leftover artifacts and verify the complete migration.
**Delivers:** Clean codebase with updated documentation, no dead dependencies, and verified feature parity.
**Addresses:** Delete webpack.mix.js, update CLAUDE.md, update .gitignore, full regression test (edit, autosave, print, public sheets, dashboard).

### Phase Ordering Rationale

- **Build tool before framework:** Isolates build issues from framework issues. If assets do not load, it is a Vite config problem, not a Vue 3 problem.
- **Vue 3 before store rewrite:** The reactive() composable requires Vue 3 APIs. Keeping Vuex 4 as a bridge during the Vue 3 upgrade means existing store patterns work, reducing the number of simultaneous changes.
- **Store rewrite as atomic phase:** The architecture research explicitly warns against gradual component-by-component store migration (two sources of truth, stale data). All 26 components must update together. The work is mechanical (repeated pattern), not creative.
- **Cleanup last:** Documentation and verification only make sense when all code changes are complete.

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 1:** May need research on `@vitejs/plugin-vue2` configuration specifics if it behaves differently from `@vitejs/plugin-vue`. The Vite backend integration guide covers the PHP pattern well, but the temporary Vue 2 plugin is less documented.
- **Phase 3:** The SpellList ID generation fix requires understanding the existing spell data schema to design stable IDs. Research the current data shape in `initializeState` before writing the fix.

Phases with standard patterns (skip research-phase):
- **Phase 2:** Vue 2 to Vue 3 breaking changes are exhaustively documented in the official migration guide. Every change needed is mechanical and well-understood.
- **Phase 4:** Cleanup is straightforward deletion and documentation updates.

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | All recommendations verified against current npm registry versions and official docs. Vite 8, Vue 3.5.x, mitt -- no contested decisions. |
| Features | HIGH | Migration scope is well-defined. Every required change maps to a documented Vue 3 breaking change. Feature landscape is complete. |
| Architecture | HIGH | Build-only Vite + PHP pattern is explicitly documented by Vite. Reactive store pattern is endorsed by Vue docs for simple stores. |
| Pitfalls | HIGH | Pitfalls identified from official migration guides, community case studies, and direct codebase analysis. SpellList ID issue found via code inspection. |

**Overall confidence:** HIGH

### Gaps to Address

- **@vitejs/plugin-vue2 behavior:** The architecture research recommends using this temporarily in Phase 1, but the stack research does not list it. Verify it works with Vite 8 before committing to this approach. If it does not, the alternative is to do Phase 1 and Phase 2 simultaneously (higher risk but eliminates the temporary dependency).
- **Spell data schema:** The SpellList ID pitfall requires knowing the exact shape of spell objects in the store. This was identified via code analysis but the fix (where to generate IDs) needs validation against real sheet data in the database.
- **baguetteBox.min.css handling:** The architecture research mentions this file but it is unclear whether it is actively used. Verify during Phase 1 whether it needs to be bundled or can be removed.

## Sources

### Primary (HIGH confidence)
- [Vue 3 Migration Guide - Breaking Changes](https://v3-migration.vuejs.org/breaking-changes/)
- [Vite Backend Integration Guide](https://vite.dev/guide/backend-integration)
- [Vite 8.0 Announcement](https://vite.dev/blog/announcing-vite8)
- [Vue 3.5 Announcement](https://blog.vuejs.org/posts/vue-3-5)
- [Vue.js Official - State Management](https://vuejs.org/guide/scaling-up/state-management.html)

### Secondary (MEDIUM confidence)
- [HeroDevs - What It Really Takes to Migrate from Vue 2 to Vue 3](https://www.herodevs.com/blog-posts/what-it-really-takes-to-migrate-from-vue-2-to-vue-3)
- [Monterail - Vue 3 Migration Case Study](https://www.monterail.com/blog/vue-3-migration-case-study)
- [Avoiding Infinite Watch Loops in Vue Production Builds](https://iwasherefirst2.medium.com/avoiding-infinite-watch-loops-in-vue-production-builds-6d80ffc66029)
- [Migration Tips from Laravel Mix to Vite](https://iwasherefirst2.medium.com/migration-tips-from-laravel-mix-to-vite-aaade1fd9d67)

### Tertiary (LOW confidence)
- [reactive() considered harmful](https://dev.to/ycmjason/thought-on-vue-3-composition-api-reactive-considered-harmful-j8c) -- opinionated take, but the concerns do not apply to a module-level singleton pattern

---
*Research completed: 2026-03-21*
*Ready for roadmap: yes*
