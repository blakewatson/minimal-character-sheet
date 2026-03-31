---
phase: quick
plan: 260321-pi3
subsystem: build
tags: [css, vendor, vite-prep, dist-cleanup]

requires:
  - phase: 01-build-tool-migration
    provides: "Vite build config (not yet created)"
provides:
  - "Vendor CSS organized in css/vendor/ for direct serving"
  - "Hand-written CSS (print, spacing) in css/ as source files"
  - "Clean dist/ with no manually-managed CSS"
affects: [02-vue3-core-migration, build-tool-migration]

tech-stack:
  added: []
  patterns: ["Vendor CSS served from css/vendor/ not dist/"]

key-files:
  created:
    - css/vendor/quill.bubble.css
    - css/vendor/baguetteBox.min.css
    - css/vendor/gutenberg.min.css
    - css/print.css
    - css/spacing.css
  modified:
    - templates/header.html
    - templates/print.html

key-decisions:
  - "Skipped Vite config update -- vite.config.js does not exist yet (Phase 2 prerequisite)"
  - "Kept print.css and spacing.css dist/ references in print.html -- future Vite build will output them there"

patterns-established:
  - "Vendor CSS location: css/vendor/ served directly by PHP, not through build pipeline"

requirements-completed: []

duration: 1min
completed: 2026-03-22
---

# Quick Task 260321-pi3: Move CSS Files Out of dist/ Summary

**Reorganized static CSS into css/vendor/ for vendor files and css/ for hand-written stylesheets, removing 7 misplaced files from public/ and 2 unreferenced files from dist/**

## Performance

- **Duration:** 1 min
- **Started:** 2026-03-22T01:24:04Z
- **Completed:** 2026-03-22T01:25:00Z
- **Tasks:** 2
- **Files modified:** 16

## Accomplishments
- Moved 3 vendor CSS files (quill, baguetteBox, gutenberg) from dist/ to css/vendor/
- Moved 2 hand-written CSS files (print, spacing) from dist/ to css/
- Deleted all 7 CSS files from public/ directory
- Deleted 2 unreferenced CSS files (flex.css, reset.min.css) from dist/
- Updated template references in header.html and print.html to point to css/vendor/

## Task Commits

Each task was committed atomically:

1. **Task 1: Move CSS files to proper source locations** - `2b92f82` (chore)
2. **Task 2: Update template CSS references** - `a38491f` (fix)

## Files Created/Modified
- `css/vendor/quill.bubble.css` - Quill editor bubble theme styles (moved from dist/)
- `css/vendor/baguetteBox.min.css` - Lightbox gallery styles (moved from dist/)
- `css/vendor/gutenberg.min.css` - Print typography styles (moved from dist/)
- `css/print.css` - Custom print stylesheet (moved from dist/)
- `css/spacing.css` - Custom spacing utilities (moved from dist/)
- `templates/header.html` - Updated quill and baguetteBox CSS references to css/vendor/
- `templates/print.html` - Updated gutenberg CSS reference to css/vendor/
- `dist/flex.css` - Deleted (unreferenced)
- `dist/reset.min.css` - Deleted (unreferenced)
- `public/*.css` - All 7 CSS files deleted

## Decisions Made
- Skipped Vite config update since vite.config.js does not exist yet -- will be handled when Vite build config is created in a future phase
- Kept print.html references to /dist/print.css and /dist/spacing.css unchanged -- these will become Vite build outputs

## Deviations from Plan

**1. [Rule 3 - Blocking] Skipped Vite config task**
- **Found during:** Task 2
- **Issue:** Plan calls for adding rollup inputs to vite.config.js, but the file does not exist (Phase 2 prerequisite)
- **Fix:** Skipped the Vite config portion of Task 2 per executor constraints
- **Impact:** print.css and spacing.css will need to be added as Vite inputs when vite.config.js is created

---

**Total deviations:** 1 (skipped non-existent config file update)
**Impact on plan:** Minimal -- the Vite config update will naturally happen when the build tool migration phase creates vite.config.js.

## Issues Encountered
None

## User Setup Required
None - no external service configuration required.

## Next Phase Readiness
- css/vendor/ directory established for direct-serve vendor CSS
- print.css and spacing.css source files ready to be added as Vite rollup inputs when vite.config.js is created
- Template references already correct for the new file locations

---
*Quick task: 260321-pi3*
*Completed: 2026-03-22*
