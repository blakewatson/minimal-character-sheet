# Phase 1: Build Tool Migration - Context

**Gathered:** 2026-03-21
**Status:** Ready for planning

<domain>
## Phase Boundary

Replace Laravel Mix with Vite as the build tool. PHP templates load assets via a new `vite()` helper reading Vite's manifest. The app must build and run identically — still Vue 2 at end of this phase (unless plugin compatibility forces a merge with Phase 2).

</domain>

<decisions>
## Implementation Decisions

### Phase scope
- **D-01:** If `@vitejs/plugin-vue2` is incompatible with the current Vite version, merge Phase 1 and Phase 2 into a single phase rather than pinning to an older Vite or using community/archived plugins
- **D-02:** Avoid temporary or community-maintained packages — prefer official packages or skip the intermediate step

### Vite configuration
- **D-03:** Three JS entry points: `js/app.js`, `js/dashboard.js`, `js/print.js` — same as current Mix setup
- **D-04:** CSS as an explicit Vite input entry point (`css/app.css`) — outputs a separate CSS file, not injected via JS. Templates keep explicit `<link>` tags. Clean 1:1 replacement for current Mix behavior
- **D-05:** Use `@tailwindcss/vite` plugin (replaces `@tailwindcss/postcss`) — Tailwind processing happens automatically as part of the CSS entry build
- **D-06:** Output directory: `dist/` — unchanged from Mix

### PHP asset helper
- **D-07:** Replace `mix()` function with `vite()` in `index.php`. Reads `dist/.vite/manifest.json` (Vite's manifest format: `{"app.js": {"file": "assets/app.abc123.js"}}`)
- **D-08:** If only one helper function is needed, keep it inline in `index.php`. If additional helpers are required, extract to a `helpers.php` file

### Vendor CSS
- **D-09:** Notyf CSS: `@import` into `app.css` — bundled into the main CSS output, no separate file or `mix()` call
- **D-10:** `quill.bubble.css` and `baguetteBox.min.css`: move source files to `css/vendor/`, copy to `dist/` via Vite's `publicDir` — keeps `dist/` fully reproducible from source
- **D-11:** `dist/` stays committed to the repo, but must be fully reproducible: wiping `dist/` and running the build should produce identical output

### Script tags
- **D-12:** Compiled entry points (`app.js`, `dashboard.js`, `print.js`) get `type="module"` in PHP templates
- **D-13:** `settings.js` stays as a plain `<script>` (no `type="module"`) — must run synchronously before compiled JS to apply dark mode before first paint
- **D-14:** `home.js` and `baguetteBox.min.js` stay as plain `<script>` tags — not compiled by Vite, served raw
- **D-15:** Inline `<script>` blocks in templates (`window.sheet`, `window.is_2024`, etc.) unchanged — they naturally execute before deferred module scripts

### Claude's Discretion
- Exact Vite config structure (`vite.config.js` contents beyond entry points and plugins)
- Whether `publicDir` points to a dedicated folder or uses Vite's `public/` convention
- Source location for vendor CSS files (`css/vendor/` or similar)
- Handling of `baguetteBox.min.css` source (currently has no source in repo — may need to copy from `node_modules` or commit to `css/vendor/`)

</decisions>

<specifics>
## Specific Ideas

- "If I needed to I could completely empty the dist folder and run the build to get a reproducible set of dist files" — reproducibility is the goal for `dist/`, not eliminating the commit
- Keep the build simple and explicit: separate CSS file, separate JS files, no magic CSS injection from JS entry points

</specifics>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Current build setup
- `webpack.mix.js` — Current Mix config; defines all entry points, CSS processing, file copies, and versioning. The Vite config must cover everything this does
- `package.json` — Current dependencies; shows what needs to be removed (laravel-mix, vue-loader, vue-template-compiler, sass-loader) and what stays

### PHP asset loading
- `index.php` lines 86-102 — Current `mix()` helper implementation; `vite()` replaces this
- `templates/header.html` — All `mix()` calls for CSS assets (`app.css`, `quill.bubble.css`, `notyf.min.css`, `baguetteBox.min.css`)
- `templates/footer.html` — All `mix()` calls for JS assets (`app.js`, `dashboard.js`)
- `templates/print.html` — `mix()` call for `print.js`

### CSS source
- `css/app.css` — Main CSS entry point; uses Tailwind v4 `@theme` and `@custom-variant` directives

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `css/app.css`: Already uses Tailwind v4 CSS-based config (`@theme`, `@custom-variant`) — compatible with `@tailwindcss/vite`, no config migration needed
- `js/settings.js`: Plain script, no imports — stays outside Vite entirely
- `js/baguetteBox.min.js`: Already a pre-built file served raw — no change needed

### Established Patterns
- All `mix('/asset.ext')` calls in templates follow the pattern `{{ @BASE }}{{ mix('/app.js') }}` — the `vite()` helper must return a path in the same format (`/dist/assets/app.abc123.js`)
- `dist/` is committed — any file Vite needs to serve must be produced by the build or explicitly copied

### Integration Points
- `index.php` `mix()` helper → replace with `vite()`, same call signature from templates
- `templates/header.html` and `templates/footer.html` → update `mix()` calls to `vite()`, add `type="module"` to compiled JS tags
- `templates/print.html` → same update for `print.js`

</code_context>

<deferred>
## Deferred Ideas

- None — discussion stayed within phase scope

</deferred>

---

*Phase: 01-build-tool-migration*
*Context gathered: 2026-03-21*
