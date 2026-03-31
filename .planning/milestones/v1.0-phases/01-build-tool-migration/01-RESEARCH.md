# Phase 1: Build Tool Migration - Research

**Researched:** 2026-03-21
**Domain:** Build tooling (Laravel Mix to Vite), PHP asset integration
**Confidence:** HIGH

## Summary

This phase replaces Laravel Mix (webpack) with Vite as the build tool. The app currently compiles three JS entry points and one CSS entry point via `webpack.mix.js`, outputs to `dist/`, and uses a PHP `mix()` helper to read `dist/mix-manifest.json` for cache-busted asset paths.

**Critical finding:** `@vitejs/plugin-vue2` v2.3.4 (latest) declares `peerDependencies: { vite: "^3.0.0 || ^4.0.0 || ^5.0.0 || ^6.0.0 || ^7.0.0", vue: "^2.7.0-0" }`. It does NOT support Vite 8 (current latest: 8.0.1), and the project uses Vue 2.6.7 (below the required ^2.7.0-0). Per user decisions D-01 and D-02, this means Phase 1 cannot maintain Vue 2 with Vite -- the build tool migration must include upgrading to Vue 3 so that `@vitejs/plugin-vue` (which supports Vite 8) can be used. **This phase must be combined with Phase 2 (Vue 3 upgrade) at the build-tool level.** The planner should account for this.

**Primary recommendation:** Install Vite 8 + `@vitejs/plugin-vue` 6.x + `@tailwindcss/vite` 4.x. Upgrade Vue to 3.5.x and Vuex to 4.x simultaneously. The Vue 3 API migration (createApp, lifecycle hooks, filters, etc.) must happen in the same phase since the build cannot compile Vue 2 SFCs with the Vue 3 plugin.

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions
- **D-01:** If `@vitejs/plugin-vue2` is incompatible with the current Vite version, merge Phase 1 and Phase 2 into a single phase rather than pinning to an older Vite or using community/archived plugins
- **D-02:** Avoid temporary or community-maintained packages -- prefer official packages or skip the intermediate step
- **D-03:** Three JS entry points: `js/app.js`, `js/dashboard.js`, `js/print.js` -- same as current Mix setup
- **D-04:** CSS as an explicit Vite input entry point (`css/app.css`) -- outputs a separate CSS file, not injected via JS. Templates keep explicit `<link>` tags. Clean 1:1 replacement for current Mix behavior
- **D-05:** Use `@tailwindcss/vite` plugin (replaces `@tailwindcss/postcss`) -- Tailwind processing happens automatically as part of the CSS entry build
- **D-06:** Output directory: `dist/` -- unchanged from Mix
- **D-07:** Replace `mix()` function with `vite()` in `index.php`. Reads `dist/.vite/manifest.json` (Vite's manifest format: `{"app.js": {"file": "assets/app.abc123.js"}}`)
- **D-08:** If only one helper function is needed, keep it inline in `index.php`. If additional helpers are required, extract to a `helpers.php` file
- **D-09:** Notyf CSS: `@import` into `app.css` -- bundled into the main CSS output, no separate file or `mix()` call
- **D-10:** `quill.bubble.css` and `baguetteBox.min.css`: move source files to `css/vendor/`, copy to `dist/` via Vite's `publicDir` -- keeps `dist/` fully reproducible from source
- **D-11:** `dist/` stays committed to the repo, but must be fully reproducible: wiping `dist/` and running the build should produce identical output
- **D-12:** Compiled entry points (`app.js`, `dashboard.js`, `print.js`) get `type="module"` in PHP templates
- **D-13:** `settings.js` stays as a plain `<script>` (no `type="module"`) -- must run synchronously before compiled JS to apply dark mode before first paint
- **D-14:** `home.js` and `baguetteBox.min.js` stay as plain `<script>` tags -- not compiled by Vite, served raw
- **D-15:** Inline `<script>` blocks in templates (`window.sheet`, `window.is_2024`, etc.) unchanged -- they naturally execute before deferred module scripts

### Claude's Discretion
- Exact Vite config structure (`vite.config.js` contents beyond entry points and plugins)
- Whether `publicDir` points to a dedicated folder or uses Vite's `public/` convention
- Source location for vendor CSS files (`css/vendor/` or similar)
- Handling of `baguetteBox.min.css` source (currently has no source in repo -- may need to copy from `node_modules` or commit to `css/vendor/`)

### Deferred Ideas (OUT OF SCOPE)
- None -- discussion stayed within phase scope
</user_constraints>

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| BUILD-01 | Replace Laravel Mix with Vite as build tool, outputting to `dist/` | Vite 8.0.1 with `build.outDir: 'dist'` and `build.manifest: true`. Requires Vue 3 upgrade due to plugin-vue2 incompatibility |
| BUILD-02 | Create vite() PHP helper that reads `dist/.vite/manifest.json` for cache-busted asset paths | Manifest format: `{"js/app.js": {"file": "assets/app-abc123.js", "isEntry": true, "css": ["assets/app-xyz.css"]}}`. Keys are relative src paths from project root |
| BUILD-03 | Add `type="module"` to script tags in PHP templates | Required for Vite ES module output. Only compiled entry points get `type="module"` -- settings.js, home.js, baguetteBox.min.js stay as plain scripts |
| BUILD-04 | Consolidate vendor CSS (Quill) via CSS @import instead of file copying | D-09: Notyf CSS via `@import 'notyf/notyf.min.css'` in app.css. D-10: quill.bubble.css and baguetteBox.min.css via publicDir copy |
| BUILD-05 | Switch from @tailwindcss/postcss to @tailwindcss/vite plugin | `@tailwindcss/vite` 4.2.2, peer dep: vite ^5.2.0 or ^6 or ^7 or ^8. Drop-in replacement, no CSS config changes needed |
| BUILD-06 | Remove Laravel Mix, webpack.mix.js, mix-manifest.json, and unused build dependencies | Remove: laravel-mix, resolve-url-loader, sass, sass-loader, vue-loader, vue-template-compiler. Remove webpack.mix.js and dist/mix-manifest.json |
| BUILD-07 | Update package.json scripts (dev, watch, prod) for Vite | `dev: "vite build --mode development"`, `watch: "vite build --watch --mode development"`, `prod: "vite build"` (build-only, no dev server per project constraints) |
</phase_requirements>

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| vite | 8.0.1 | Build tool | Latest stable, ESM-native bundler replacing webpack/Mix |
| @vitejs/plugin-vue | 6.0.5 | Vue 3 SFC compilation | Official Vite plugin for Vue 3, supports Vite 8 |
| @tailwindcss/vite | 4.2.2 | Tailwind CSS processing | Official Vite plugin, replaces @tailwindcss/postcss |
| vue | 3.5.30 | UI framework | Required because plugin-vue2 is incompatible with Vite 8 (D-01) |
| vuex | 4.x | State management | Vue 3 compatible Vuex; bridge until Phase 3 reactive() migration |

### To Remove
| Library | Reason |
|---------|--------|
| laravel-mix | Replaced by Vite |
| resolve-url-loader | Webpack-specific, not needed with Vite |
| sass / sass-loader | Project uses PostCSS/Tailwind, not Sass for primary styles |
| vue-loader | Webpack-specific Vue SFC loader |
| vue-template-compiler | Vue 2 specific, Vue 3 uses @vue/compiler-sfc (bundled) |
| @tailwindcss/postcss | Replaced by @tailwindcss/vite |

### Installation
```bash
npm uninstall laravel-mix resolve-url-loader sass sass-loader vue-loader vue-template-compiler @tailwindcss/postcss
npm install vue@^3.5.30 vuex@^4
npm install -D vite@^8 @vitejs/plugin-vue@^6 @tailwindcss/vite@^4
```

## Architecture Patterns

### Vite Config Structure
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
  ],
  build: {
    outDir: 'dist',
    manifest: true,
    rollupOptions: {
      input: {
        app: 'js/app.js',
        dashboard: 'js/dashboard.js',
        print: 'js/print.js',
        styles: 'css/app.css',
      },
    },
    // Vite clears outDir by default -- need emptyOutDir: false
    // to preserve static files OR use publicDir to copy them in
    emptyOutDir: true,
  },
  publicDir: 'public',  // files here are copied to dist/ as-is
});
```

### Public Directory Structure for Static Assets
```
public/                          # Vite copies contents to dist/ on build
  quill.bubble.css               # Copied from node_modules/quill/dist/
  baguetteBox.min.css            # No npm source -- commit to public/
  gutenberg.min.css              # Currently only in dist/ -- move to public/
  spacing.css                    # Currently only in dist/ -- move to public/
  print.css                      # Currently only in dist/ -- move to public/
  flex.css                       # Currently only in dist/ -- move to public/
  reset.min.css                  # Currently only in dist/ -- move to public/
```

### Vite Manifest Format (dist/.vite/manifest.json)
```json
{
  "js/app.js": {
    "file": "assets/app-BxH3k2.js",
    "isEntry": true,
    "src": "js/app.js"
  },
  "js/dashboard.js": {
    "file": "assets/dashboard-a1b2c3.js",
    "isEntry": true,
    "src": "js/dashboard.js"
  },
  "js/print.js": {
    "file": "assets/print-d4e5f6.js",
    "isEntry": true,
    "src": "js/print.js"
  },
  "css/app.css": {
    "file": "assets/app-g7h8i9.css",
    "isEntry": true,
    "src": "css/app.css"
  }
}
```

### PHP vite() Helper
```php
/**
 * Returns a versioned asset path using Vite's manifest file.
 * Used in templates for cache busting: {{ vite('js/app.js') }}
 */
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
    if (isset($manifest[$entry])) {
        return '/dist/' . $manifest[$entry]['file'];
    }
    // Fallback: return the path as-is (for non-manifest assets)
    return '/dist/' . $entry;
}
```

**Key difference from mix():** The `mix()` helper received paths like `/app.js` and returned paths like `/dist/app.js?id=hash`. The `vite()` helper receives entry names like `js/app.js` (matching rollupOptions.input keys) and returns paths like `/dist/assets/app-BxH3k2.js` (content-hashed filenames, no query strings).

### Template Updates

**header.html** -- before:
```html
<link href="{{ @BASE }}{{ mix('/quill.bubble.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ @BASE }}{{ mix('/app.css') }}" />
<link rel="stylesheet" href="{{ @BASE }}{{ mix('/notyf.min.css') }}" />
```

**header.html** -- after:
```html
<link href="{{ @BASE }}/dist/quill.bubble.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ @BASE }}{{ vite('css/app.css') }}" />
<!-- notyf CSS is now bundled into app.css via @import -->
```

**footer.html** -- before:
```html
<script src="{{ @BASE }}{{ mix('/app.js') }}"></script>
```

**footer.html** -- after:
```html
<script type="module" src="{{ @BASE }}{{ vite('js/app.js') }}"></script>
```

### Anti-Patterns to Avoid
- **Using Vite dev server:** This project uses PHP to serve the app. Vite is build-only. Do NOT configure HMR or dev middleware.
- **CSS injected via JS:** Do NOT rely on Vite's default behavior of injecting CSS through JS entry points. CSS must be an explicit entry point with a separate `<link>` tag in templates (D-04).
- **Hashing static vendor CSS:** Files like `quill.bubble.css` and `baguetteBox.min.css` are referenced directly in templates, not through the manifest. They go through `publicDir` and are copied as-is (no hashing).

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Asset manifest reading | Custom manifest parser | Simple `vite()` helper (see pattern above) | Vite manifest format is stable and simple -- a JSON object with entry keys mapping to `{file, isEntry, css}` chunks |
| Tailwind processing | PostCSS plugin chain | `@tailwindcss/vite` | Handles all Tailwind processing automatically as a Vite plugin |
| Vue SFC compilation | Custom webpack/rollup config | `@vitejs/plugin-vue` | Handles template compilation, scoped CSS, script setup |
| File copying to dist | Custom build scripts | Vite `publicDir` | Built-in mechanism to copy static files to output directory |

## Common Pitfalls

### Pitfall 1: CSS Entry Point Produces Empty JS File
**What goes wrong:** When CSS is listed as a rollup input, Vite generates both the CSS file AND a tiny empty JS wrapper file in the manifest.
**Why it happens:** Rollup treats all inputs as JS entry points; CSS is handled as a side-effect.
**How to avoid:** This is normal behavior. The `vite()` helper should handle the CSS entry by looking up its `file` property in the manifest. The empty JS file can be ignored. The manifest key for CSS entries will point to the correct CSS output.
**Warning signs:** Seeing an extra `.js` file in `dist/assets/` for the CSS entry.

### Pitfall 2: Vite emptyOutDir Deletes Static Files
**What goes wrong:** Vite's default `build.emptyOutDir: true` wipes `dist/` before building, deleting static CSS files that aren't produced by the build.
**Why it happens:** Vite assumes it owns the entire output directory.
**How to avoid:** Use `publicDir` to hold static files -- Vite copies them into `dist/` after the build. All files that need to be in `dist/` must either be built by Vite OR placed in the `publicDir` source folder.

### Pitfall 3: Manifest Key Mismatch
**What goes wrong:** The `vite()` helper can't find entries because the manifest keys don't match what templates pass.
**Why it happens:** Vite manifest keys are the relative paths from project root as specified in `rollupOptions.input` (e.g., `js/app.js`), NOT the output filenames. The old `mix()` helper used paths like `/app.js`.
**How to avoid:** Update ALL template calls from `mix('/app.js')` to `vite('js/app.js')` -- the input path, not the output path.

### Pitfall 4: type="module" Script Execution Order
**What goes wrong:** Module scripts are deferred by default, so they execute after the DOM is parsed, which changes execution order relative to inline scripts.
**Why it happens:** `<script type="module">` is always deferred. Classic `<script>` blocks execute immediately.
**How to avoid:** This is actually fine for this project -- `settings.js` (plain script, runs first for dark mode), inline scripts (`window.sheet`, etc.) run before the deferred module scripts. The current footer.html structure already has inline scripts before the compiled script tags.

### Pitfall 5: Notyf CSS Import Path
**What goes wrong:** `@import 'notyf/notyf.min.css'` fails because Vite/Tailwind can't resolve node_modules paths in CSS.
**Why it happens:** CSS `@import` resolution differs between PostCSS and Vite.
**How to avoid:** Use the full path: `@import 'notyf/notyf.min.css'` should work with Vite's CSS resolution which handles bare module specifiers. If not, use `@import '../node_modules/notyf/notyf.min.css'` as fallback.

### Pitfall 6: Static CSS Files Have No Source
**What goes wrong:** Several CSS files in `dist/` have NO source files anywhere in the repo: `gutenberg.min.css`, `spacing.css`, `print.css`, `flex.css`, `reset.min.css`, `baguetteBox.min.css`, `notyf.min.css`.
**Why it happens:** These were manually placed in `dist/` and never managed by the build tool.
**How to avoid:** For D-11 (reproducible dist), move these files to `public/` (or `css/vendor/`) so `publicDir` copies them. `notyf.min.css` is handled differently -- it gets `@import`ed into `app.css` per D-09, so the standalone file is no longer needed.

## Code Examples

### Notyf CSS Import in app.css
```css
/* Add at the top of css/app.css */
@import 'notyf/notyf.min.css';
@import 'tailwindcss';

/* ... rest of existing styles ... */
```

### Vite Build Scripts in package.json
```json
{
  "scripts": {
    "dev": "vite build --mode development",
    "watch": "vite build --watch --mode development",
    "prod": "vite build"
  }
}
```

### Template Script Tag with type="module"
```html
<!-- Compiled entry points: type="module" -->
<script type="module" src="{{ @BASE }}{{ vite('js/app.js') }}"></script>

<!-- Plain scripts: no type="module" -->
<script src="{{ @BASE }}/js/settings.js"></script>
<script src="{{ @BASE }}/js/home.js"></script>
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Laravel Mix (webpack wrapper) | Vite (ESM-native bundler) | Laravel ecosystem migrated in 2022-2023 | Faster builds, simpler config, native ES modules |
| `@tailwindcss/postcss` plugin | `@tailwindcss/vite` plugin | Tailwind v4 (2025) | Purpose-built Vite integration, no PostCSS config needed |
| mix-manifest.json (query string hashing) | .vite/manifest.json (content-hashed filenames) | Vite default | Filenames contain hash instead of query params |
| `vue-loader` + `vue-template-compiler` | `@vitejs/plugin-vue` + `@vue/compiler-sfc` | Vue 3 + Vite | Single plugin handles all SFC compilation |

**Deprecated/outdated:**
- `laravel-mix`: No longer maintained for new projects; Laravel itself migrated to Vite
- `@vitejs/plugin-vue2`: Supports Vite up to v7 only; Vue 2 is EOL
- `vue-template-compiler`: Vue 2 only; Vue 3 bundles its own compiler

## Inventory of Affected Files

### Files to Create
| File | Purpose |
|------|---------|
| `vite.config.js` | Vite build configuration |
| `public/` directory | Source location for static files copied to `dist/` |

### Files to Modify
| File | Change |
|------|--------|
| `package.json` | Update deps and scripts |
| `index.php` | Replace `mix()` with `vite()` helper |
| `templates/header.html` | Update asset references from `mix()` to `vite()`, remove notyf CSS link |
| `templates/footer.html` | Update script tags with `type="module"` and `vite()` paths |
| `templates/print.html` | Update `mix()` call to `vite()` with `type="module"` |
| `css/app.css` | Add `@import 'notyf/notyf.min.css'` |

### Files to Delete
| File | Reason |
|------|--------|
| `webpack.mix.js` | Replaced by `vite.config.js` |
| `dist/mix-manifest.json` | Replaced by `dist/.vite/manifest.json` |

### Files to Move (for reproducibility)
| From | To | Reason |
|------|-----|--------|
| `dist/quill.bubble.css` | `public/quill.bubble.css` | Source: `node_modules/quill/dist/quill.bubble.css` -- copy to public/ |
| `dist/baguetteBox.min.css` | `public/baguetteBox.min.css` | No npm source; must commit to public/ |
| `dist/gutenberg.min.css` | `public/gutenberg.min.css` | No source outside dist/ |
| `dist/spacing.css` | `public/spacing.css` | No source outside dist/ |
| `dist/print.css` | `public/print.css` | No source outside dist/ |
| `dist/flex.css` | `public/flex.css` | No source outside dist/ |
| `dist/reset.min.css` | `public/reset.min.css` | No source outside dist/ |
| `dist/notyf.min.css` | (delete) | Now bundled into app.css via @import |

## Open Questions

1. **CSS entry point manifest behavior**
   - What we know: CSS as a rollup input produces both a CSS file and an empty JS wrapper. The manifest entry for `css/app.css` will have a `file` property pointing to the hashed CSS output.
   - What's unclear: Whether the CSS entry also generates a `.css` key under a different manifest structure, or if the JS wrapper is the primary entry.
   - Recommendation: Test during implementation. The `vite()` helper should check for the `file` property and handle CSS entries correctly.

2. **Notyf CSS @import with Tailwind v4**
   - What we know: Tailwind v4 uses `@import 'tailwindcss'` syntax in CSS. Notyf CSS needs to be imported alongside it.
   - What's unclear: Whether `@import 'notyf/notyf.min.css'` resolves correctly with `@tailwindcss/vite` processing the CSS.
   - Recommendation: Place the notyf import before `@import 'tailwindcss'` in `css/app.css`. If bare specifier resolution fails, use relative path to `node_modules`.

3. **Phase merge scope**
   - What we know: D-01 triggers -- plugin-vue2 is incompatible with Vite 8. Phase 1 must include Vue 3 upgrade.
   - What's unclear: How much of Phase 2's Vue 3 migration work should be pulled into Phase 1 vs. kept separate.
   - Recommendation: Phase 1 must include enough Vue 3 changes to make the build compile (createApp, Vue.use to app.use, filters to functions, lifecycle hook renames in components). The planner should pull in all Phase 2 VUE-* requirements.

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | Manual browser testing (no automated test framework detected) |
| Config file | None |
| Quick run command | `npm run dev` then load app in browser |
| Full suite command | `npm run prod` then verify all views in browser |

### Phase Requirements to Test Map
| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| BUILD-01 | Vite produces compiled assets in dist/ | smoke | `npm run prod && ls dist/assets/` | N/A |
| BUILD-02 | vite() helper resolves manifest paths | manual | Load any page, verify assets load (no 404s) | N/A |
| BUILD-03 | Script tags have type="module" | manual | View page source in browser | N/A |
| BUILD-04 | Vendor CSS loads correctly | manual | Check quill editor styling, notyf toasts | N/A |
| BUILD-05 | Tailwind styles render correctly | manual | Check page styling matches pre-migration | N/A |
| BUILD-06 | Mix artifacts removed | smoke | `test ! -f webpack.mix.js && test ! -f dist/mix-manifest.json` | N/A |
| BUILD-07 | npm scripts work | smoke | `npm run dev && npm run prod` | N/A |

### Sampling Rate
- **Per task commit:** `npm run dev` -- verify build succeeds
- **Per wave merge:** `npm run prod` then load character sheet, dashboard, print view in browser
- **Phase gate:** Full production build + manual verification of all views

### Wave 0 Gaps
- No automated test infrastructure exists for this project
- All validation is manual browser testing
- Consider adding a smoke test script that runs `npm run prod` and checks for expected output files

## Sources

### Primary (HIGH confidence)
- npm registry: `npm view vite version` -> 8.0.1, `npm view @vitejs/plugin-vue2 peerDependencies` -> vite ^3-^7 only, vue ^2.7.0-0 only
- npm registry: `npm view @vitejs/plugin-vue peerDependencies` -> vite ^5-^8, vue ^3.2.25
- npm registry: `npm view @tailwindcss/vite peerDependencies` -> vite ^5.2.0 or ^6-^8
- npm registry: `npm view @tailwindcss/vite version` -> 4.2.2
- npm registry: `npm view @vitejs/plugin-vue version` -> 6.0.5
- npm registry: `npm view vue@3 version` -> 3.5.30

### Secondary (MEDIUM confidence)
- [Vite Backend Integration docs](https://vite.dev/guide/backend-integration) -- manifest format, build.manifest config
- [Vite Build Options docs](https://vite.dev/config/build-options) -- outDir, manifest, emptyOutDir, publicDir
- [Vite CSS entry point issue #14271](https://github.com/vitejs/vite/issues/14271) -- CSS as rollup input behavior (closed)
- [Vite Shared Options docs](https://vite.dev/config/shared-options) -- publicDir configuration

### Tertiary (LOW confidence)
- WebSearch for CSS entry point handling in Vite -- multiple sources agree on behavior but exact manifest structure for CSS entries needs implementation verification

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH -- all versions verified against npm registry
- Architecture: HIGH -- Vite config patterns well-documented, manifest format stable
- Pitfalls: HIGH -- identified from official issues and verified documentation
- Phase merge (D-01 trigger): HIGH -- peer dependency incompatibility is a hard constraint verified against npm

**Research date:** 2026-03-21
**Valid until:** 2026-04-21 (stable ecosystem, unlikely to change rapidly)
