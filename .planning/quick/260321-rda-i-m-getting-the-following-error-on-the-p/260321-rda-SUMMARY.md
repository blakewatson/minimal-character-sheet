---
phase: quick
plan: 260321-rda
subsystem: frontend-build
tags: [bugfix, print-view, i18n, vite]
key-files:
  modified:
    - dist/assets/print-ChMJ7o1l.js
    - dist/assets/app-CRoW_KJy.js
    - dist/assets/i18n-2qVbfbiA.js
    - dist/.vite/manifest.json
  removed:
    - dist/assets/print-w4aRwlnc.js
    - dist/assets/app-BfZpsK2K.js
    - dist/assets/store-C13MKE96.js
decisions: []
metrics:
  duration: 24s
  completed: 2026-03-22T02:48:34Z
  tasks: 1
  files: 6
---

# Quick Task 260321-rda: Fix print view TypeError ($t is not a function) Summary

Rebuilt dist files so print bundle includes both store and i18nPlugin registrations, fixing `TypeError: o.$t is not a function`.

## What Was Done

### Task 1: Rebuild dist files and verify i18n plugin is in print bundle

Ran `npx vite build` to rebuild all dist assets from the current source (which already had the i18n plugin registration in `js/print.js` since commit `95d8d6e`).

**Verification:**
- Print bundle (`print-ChMJ7o1l.js`) contains two `.use()` calls: `.use(te)` and `.use(ne)` -- store + i18nPlugin
- The i18n shared chunk (`i18n-2qVbfbiA.js`) exports the install function that sets `globalProperties.$t`
- Build completed without errors

**Commit:** 87a20c1

## Root Cause

The dist files committed in `206bbca` were built from a stale version of `js/print.js` that predated the i18n plugin fix in `95d8d6e`. The print bundle only had one `.use()` call (store) and was missing the i18n plugin registration, so `$t` was never set on component instances.

## Deviations from Plan

None - plan executed exactly as written.

## Known Stubs

None.

## Self-Check: PASSED

- FOUND: dist/assets/print-ChMJ7o1l.js
- FOUND: dist/assets/i18n-2qVbfbiA.js
- FOUND: commit 87a20c1
