# Technology Stack

**Project:** Minimal Character Sheet -- Vue 3 Migration
**Researched:** 2026-03-21

## Current Stack (What We're Migrating From)

| Technology | Version | Role |
|------------|---------|------|
| Vue | ^2.6.7 | UI framework |
| Vuex | ^3.1.0 | State management |
| Laravel Mix | ^6 | Build tool (webpack wrapper) |
| vue-template-compiler | ^2.7.14 | SFC compilation (Vue 2) |
| vue-loader | ^15.10.2 | Webpack Vue SFC loader |
| sass / sass-loader | ^1.66 / ^12.6 | Not actively used (CSS is Tailwind/PostCSS) |
| resolve-url-loader | ^5.0.0 | Webpack URL resolution |

## Recommended Stack

### Build Tool

| Technology | Version | Purpose | Why | Confidence |
|------------|---------|---------|-----|------------|
| Vite | ^8.0.0 | Build tool (build-only, no dev server) | Current stable. Ships Rolldown (Rust bundler) for 10-30x faster builds. Full Rollup plugin compatibility. Node 22.17.1 on this machine exceeds the 22.12+ requirement. | HIGH |
| @vitejs/plugin-vue | ^6.0.0 | Vue 3 SFC compilation in Vite | Official Vue plugin for Vite. v6 is the current release paired with Vite 8. Works without modification on Rolldown. | HIGH |

**Why Vite 8 and not Vite 6/7:** Vite 8 is the current stable as of March 2026. The migration plan was drafted when Vite 5/6 was current, but Vite 8 has a compatibility layer that auto-converts `rollupOptions` to `rolldownOptions`, so the existing config in the migration plan works as-is. There is no reason to target an older version.

**Build-only mode:** This project uses PHP (Fat-Free Framework) as the backend. Vite's dev server with HMR is not used. Instead, `vite build` and `vite build --watch` produce assets to `dist/`, and the PHP backend reads `dist/.vite/manifest.json` for cache-busted paths. This is a well-documented pattern (Vite's "Backend Integration" guide covers it explicitly).

**Vite config notes:**
- `build.manifest: true` -- generates the manifest file the PHP helper reads
- `build.outDir: 'dist'` -- matches current output location
- `build.emptyOutDir: true` -- clean builds
- `build.rollupOptions.input` (auto-converted to `rolldownOptions` by Vite 8) -- three JS entries + CSS entry

### UI Framework

| Technology | Version | Purpose | Why | Confidence |
|------------|---------|---------|-----|------------|
| Vue | ^3.5.0 | UI framework | v3.5.30 is the latest stable (2026-03-09). v3.5 brought major reactivity performance improvements and -56% memory usage. v3.6 is in beta -- do not use it for a migration. | HIGH |

**Why ^3.5 and not pinned 3.5.30:** Patch versions are safe. The `^3.5.0` range picks up fixes but won't jump to 3.6.

**Options API is fully supported in Vue 3.** The migration plan correctly keeps Options API. No Composition API conversion is needed.

### State Management

| Technology | Version | Purpose | Why | Confidence |
|------------|---------|---------|-----|------------|
| Vuex | ^4.0.0 | Temporary bridge (Phase 2 only) | Vuex 4 is the Vue 3 compatible version. Used as an intermediate step to avoid changing the store and all components simultaneously. Removed in Phase 3. | HIGH |
| Vue `reactive()` + `computed()` | (built into Vue 3) | Final state management | The store is a flat bag of ~50 properties with trivial mutations. Pinia would add unnecessary abstraction. A single `reactive()` object with exported `computed()` getters is the right fit. | HIGH |

**Why not Pinia:** Pinia is the official successor to Vuex, but it adds concepts (defineStore, actions, getters) that are overkill for a single flat store. The reactive composable pattern is simpler, has zero dependencies, and matches the store's actual complexity. The migration plan's approach is correct.

### Event Bus

| Technology | Version | Purpose | Why | Confidence |
|------------|---------|---------|-----|------------|
| mitt | ^3.0.1 | Event emitter (replaces Vue 2 instance-as-event-bus) | 200 bytes, zero dependencies, widely used as the Vue 3 event bus replacement. v3.0.1 is the latest (stable for 3+ years). The API is `emit()`/`on()`/`off()` -- nearly identical to the Vue 2 pattern. | HIGH |

### CSS / Styling

| Technology | Version | Purpose | Why | Confidence |
|------------|---------|---------|-----|------------|
| @tailwindcss/vite | ^4.2.0 | Tailwind CSS v4 integration via Vite plugin | Replaces `@tailwindcss/postcss` for Vite projects. The Vite plugin is faster and simpler than the PostCSS plugin because it hooks directly into Vite's pipeline. Since we are moving to Vite, use the Vite plugin instead. | HIGH |

**Migration note:** Currently using `@tailwindcss/postcss` with Laravel Mix's PostCSS pipeline. When switching to Vite, replace `@tailwindcss/postcss` with `@tailwindcss/vite` and add it to `vite.config.js` plugins array. Remove the PostCSS-based approach entirely.

```js
// vite.config.js
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [tailwindcss(), vue()],
  // ...
})
```

### Rich Text Editor

| Technology | Version | Purpose | Why | Confidence |
|------------|---------|---------|-----|------------|
| Quill | ^2.0.3 | Rich text editing | Already installed and working. Quill 2 is fully compatible with Vue 3. The existing `QuillEditor.vue` component manually instantiates Quill on a DOM element -- this pattern works in Vue 3 (use `mounted()` lifecycle, same as before). No wrapper library needed. | HIGH |

**Do NOT add vue-quill or @vueup/vue-quill.** The existing custom component is simpler and already handles the app's specific needs (Quill bubble theme, bidirectional state sync). A wrapper library would add unnecessary abstraction.

**Gotcha:** Do not store the Quill instance in Vue reactive state. Use a plain variable or `markRaw()`. The existing code already does this correctly (stores on `this.quill`, which is not in `data()`).

### Existing Libraries (Keep As-Is)

| Technology | Version | Purpose | Notes |
|------------|---------|---------|-------|
| notyf | ^3.10.0 | Toast notifications | Framework-agnostic, no changes needed |
| quill | ^2.0.3 | Rich text editor | See above |
| prettier | 3.6.2 | Code formatting | Dev tool, unaffected by migration |
| prettier-plugin-tailwindcss | ^0.7.2 | Tailwind class sorting | Dev tool, unaffected |

## Packages to Remove

| Package | Why Remove |
|---------|-----------|
| laravel-mix | Replaced by Vite |
| vue-template-compiler | Vue 2 only; Vue 3 uses @vitejs/plugin-vue for SFC compilation |
| vue-loader | Webpack-specific; Vite handles this via plugin |
| resolve-url-loader | Webpack-specific |
| sass | Not actively used (all CSS is Tailwind/PostCSS) |
| sass-loader | Webpack-specific |
| vuex | Removed after Phase 3 (replaced by reactive composable) |
| @tailwindcss/postcss | Replaced by @tailwindcss/vite |

## Packages to Add

| Package | Version | Dev/Prod | Phase |
|---------|---------|----------|-------|
| vite | ^8.0.0 | dev | Phase 1 |
| @vitejs/plugin-vue | ^6.0.0 | dev | Phase 1 |
| @tailwindcss/vite | ^4.2.0 | dev | Phase 1 |
| mitt | ^3.0.1 | prod | Phase 2 |
| vue@3 | ^3.5.0 | prod | Phase 2 |
| vuex@4 | ^4.0.0 | prod (temporary) | Phase 2 |

## Alternatives Considered

| Category | Recommended | Alternative | Why Not |
|----------|-------------|-------------|---------|
| Build tool | Vite 8 | Vite 6 | Vite 8 is current stable. No reason to target an older version; the compat layer handles config migration automatically. |
| Build tool | Vite 8 | Webpack 5 (keep Mix) | Laravel Mix is effectively unmaintained. Vite is faster, simpler config, and the standard for Vue projects. |
| State | reactive() composable | Pinia | Store is a flat 50-property bag. Pinia's defineStore/actions/getters add ceremony without benefit. |
| State | reactive() composable | Vuex 4 (keep) | Vuex is in maintenance mode. The reactive() approach is simpler and has no dependency. |
| Event bus | mitt | tiny-emitter | mitt is smaller, more popular, and the de facto Vue 3 community standard. |
| CSS integration | @tailwindcss/vite | @tailwindcss/postcss | The Vite plugin is the recommended approach for Vite projects. Faster, no PostCSS config needed. |
| Quill wrapper | None (custom component) | @vueup/vue-quill | Custom component is already built and tailored. A wrapper adds a dependency for no benefit. |

## Installation Commands

### Phase 1 (Vite migration)

```bash
# Add Vite and plugins
npm install -D vite@^8 @vitejs/plugin-vue@^6 @tailwindcss/vite@^4

# Remove webpack/Mix toolchain
npm uninstall laravel-mix resolve-url-loader sass sass-loader vue-loader vue-template-compiler @tailwindcss/postcss
```

### Phase 2 (Vue 3 upgrade)

```bash
# Upgrade Vue, add Vuex 4 as bridge, add mitt
npm install vue@^3.5 vuex@^4 mitt@^3
```

### Phase 3 (Remove Vuex)

```bash
npm uninstall vuex
```

## Node.js Requirement

Vite 8 requires Node.js 20.19+ or 22.12+. This machine runs Node 22.17.1, which satisfies the requirement. No Node upgrade needed.

## Sources

- [Vite 8.0 announcement](https://vite.dev/blog/announcing-vite8) -- Rolldown integration, breaking changes, compat layer
- [Vite releases page](https://vite.dev/releases) -- version history
- [Vite migration from v7](https://vite.dev/guide/migration) -- Node requirements, rollupOptions rename
- [Vite backend integration guide](https://vite.dev/guide/backend-integration) -- manifest.json usage with PHP
- [Vue releases page](https://vuejs.org/about/releases) -- v3.5.30 current stable
- [Vue 3.5 announcement](https://blog.vuejs.org/posts/vue-3-5) -- reactivity improvements
- [@vitejs/plugin-vue on npm](https://www.npmjs.com/package/@vitejs/plugin-vue) -- v6.0.5 current
- [mitt on npm](https://www.npmjs.com/package/mitt) -- v3.0.1 current
- [@tailwindcss/vite on npm](https://www.npmjs.com/package/@tailwindcss/vite) -- v4.2.2 current
- [Tailwind CSS v4 Vite installation](https://tailwindcss.com/docs) -- @tailwindcss/vite plugin approach
- [VueQuill docs](https://vueup.github.io/vue-quill/) -- Quill 2 + Vue 3 compatibility confirmed
