---
phase: quick
plan: 260321-ry1
subsystem: frontend/i18n
tags: [bugfix, i18n, print-view]
dependency_graph:
  requires: []
  provides: [explicit-spell-level-i18n-keys]
  affects: [print-view]
tech_stack:
  added: []
  patterns: [explicit-i18n-keys-over-interpolation]
key_files:
  created: []
  modified:
    - js/languages/en.json
    - js/languages/de.json
    - js/components/Print.vue
decisions:
  - Used explicit keys (Level 1 spells...Level 9 spells) rather than adding interpolation to $t() -- simpler, no i18n library changes needed
metrics:
  duration: "<1min"
  completed: "2026-03-22"
  tasks_completed: 1
  tasks_total: 1
---

# Quick Task 260321-ry1: Fix Print View Spell Level Headings

Explicit i18n keys for spell level 1-9 headings, replacing broken interpolation param that $t() silently ignores.

## What Changed

The print view was displaying literal "Level {level} spells" instead of "Level 1 spells", "Level 2 spells", etc. The `$t()` function only does key lookup and does not support interpolation parameters. Print.vue was passing `{ level: idx + 1 }` as a second argument which was silently ignored.

**Fix:** Added 9 explicit translation entries ("Level 1 spells" through "Level 9 spells") to both en.json and de.json. Updated Print.vue to construct the key dynamically: `$t('Level ' + (idx + 1) + ' spells')`.

## Tasks Completed

| # | Task | Commit | Files |
|---|------|--------|-------|
| 1 | Add explicit spell level entries and fix Print.vue | 866ccb0 | en.json, de.json, Print.vue |
| - | Rebuild dist | 1d9b80d | dist/ assets |

## Deviations from Plan

None - plan executed exactly as written.

## Known Stubs

None.

## Self-Check: PASSED
