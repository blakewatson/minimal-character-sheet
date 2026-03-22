---
phase: 01-build-tool-migration
plan: 02
subsystem: infra
tags: [vite, php, asset-pipeline, build-tool]

# Dependency graph
requires:
  - phase: 01-build-tool-migration/01
    provides: Vite config, npm scripts, Vue 3 + Tailwind v4 dependencies
provides:
  - vite() PHP helper function reading dist/.vite/manifest.json
  - Updated PHP templates with type="module" script tags
  - Removal of Laravel Mix artifacts (webpack.mix.js, mix-manifest.json)
affects: [01-build-tool-migration/03, 01-build-tool-migration/04]

# Tech tracking
tech-stack:
  added: []
  patterns: [vite-manifest-based-asset-loading, esm-script-tags]

key-files:
  created: []
  modified:
    - index.php
    - templates/header.html
    - templates/footer.html
    - templates/print.html
    - vite.config.js

key-decisions:
  - "Static vendor CSS (quill, baguetteBox) uses direct /dist/ paths instead of vite() since they are copied from public/ and not in the Vite manifest"
  - "Notyf CSS removed from header.html since it is now bundled into app.css via @import"
  - "Added .vue to Vite resolve.extensions to support extensionless SFC imports used throughout the codebase"

patterns-established:
  - "vite() helper: use vite('js/app.js') for compiled assets, direct /dist/path for static public files"
  - "type=module on compiled entry scripts only; plain scripts (settings.js, home.js) remain classic"

requirements-completed: [BUILD-02, BUILD-03, BUILD-06]

# Metrics
duration: 2min
completed: 2026-03-21
---

# Phase 01 Plan 02: PHP Asset Integration Summary

**vite() PHP helper replacing mix(), updated templates with type="module" scripts, Laravel Mix artifacts deleted**

## Performance

- **Duration:** 2 min
- **Started:** 2026-03-21T23:41:22Z
- **Completed:** 2026-03-21T23:43:22Z
- **Tasks:** 2
- **Files modified:** 5

## Accomplishments
- Replaced mix() helper with vite() in index.php, reading Vite's manifest format (dist/.vite/manifest.json)
- Updated all PHP templates (header, footer, print) to use vite() for compiled assets and type="module" for ES module scripts
- Removed Notyf CSS separate link (now bundled in app.css)
- Deleted webpack.mix.js and dist/mix-manifest.json
- Fixed Vite resolve.extensions to support extensionless .vue imports

## Task Commits

Each task was committed atomically:

1. **Task 1: Replace mix() with vite() helper and update templates** - `1a4dd5b` (feat)
2. **Task 2: Delete Laravel Mix artifacts and fix Vite resolve config** - `5a5c746` (chore)

## Files Created/Modified
- `index.php` - Replaced mix() function with vite() reading dist/.vite/manifest.json
- `templates/header.html` - vite() for app.css, direct paths for vendor CSS, notyf link removed
- `templates/footer.html` - vite() + type="module" for app.js and dashboard.js
- `templates/print.html` - vite() + type="module" for print.js
- `vite.config.js` - Added resolve.extensions with .vue support

## Decisions Made
- Static vendor CSS (quill.bubble.css, baguetteBox.min.css) uses direct /dist/ paths since Vite copies them from public/ without manifest entries
- Notyf CSS link removed from header.html because it is bundled into app.css via CSS @import (established in plan 01)
- Added .vue to resolve.extensions -- the codebase uses extensionless imports for Vue SFCs throughout

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Added .vue to Vite resolve.extensions**
- **Found during:** Task 2 (verification build)
- **Issue:** Vite build failed with "Could not resolve './components/Sheet'" because Vite's default extensions don't include .vue, but the codebase uses extensionless SFC imports everywhere
- **Fix:** Added resolve.extensions config to vite.config.js including .vue
- **Files modified:** vite.config.js
- **Verification:** Build progresses past module resolution (772 modules transformed)
- **Committed in:** 5a5c746 (Task 2 commit)

---

**Total deviations:** 1 auto-fixed (1 blocking)
**Impact on plan:** Essential for Vite to resolve Vue SFC imports. No scope creep.

## Issues Encountered
- Vite build does not produce dist/.vite/manifest.json because JS entry points fail on Vue 2 API (`import Vue from 'vue'` has no default export in Vue 3). This is expected -- the Vue 2 code has not been migrated yet (that is plans 03-04 work). The build infrastructure is correctly configured: Vite reads the config, resolves 772 modules, and copies static files from public/. Full manifest generation will work once Vue 3 API migration is complete.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness
- PHP asset pipeline fully wired to Vite manifest format
- Templates ready for Vite-built assets with proper type="module" attributes
- Next plans (03, 04) will migrate Vue 2 code to Vue 3 API, enabling full successful builds
- Once Vue 3 migration completes, the manifest will be generated and the full asset pipeline will work end-to-end

## Self-Check: PASSED

All files verified present, deleted files confirmed removed, both commit hashes found in git log.

---
*Phase: 01-build-tool-migration*
*Completed: 2026-03-21*
