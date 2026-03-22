---
phase: 3
slug: store-migration
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-21
---

# Phase 3 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | None — no test framework configured; build + manual smoke tests only |
| **Config file** | none |
| **Quick run command** | `npm run prod` |
| **Full suite command** | Manual smoke test: load sheet, edit fields, verify autosave, check print view |
| **Estimated runtime** | ~30 seconds build + 5 minutes manual |

---

## Sampling Rate

- **After every task commit:** Run `npm run prod` (build must succeed)
- **After every plan wave:** Manual smoke test: load sheet, edit fields, verify autosave, check print view
- **Before `/gsd:verify-work`:** Full manual verification per success criteria
- **Max feedback latency:** ~30 seconds (build)

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| 3-01-01 | 01 | 1 | STORE-01 | build | `npm run prod` | ✅ | ⬜ pending |
| 3-01-02 | 01 | 1 | STORE-02 | build | `npm run prod` | ✅ | ⬜ pending |
| 3-01-03 | 01 | 1 | STORE-03 | build | `npm run prod` | ✅ | ⬜ pending |
| 3-01-04 | 01 | 1 | STORE-04 | build | `npm run prod` | ✅ | ⬜ pending |
| 3-01-05 | 01 | 1 | STORE-05 | build | `npm run prod` | ✅ | ⬜ pending |
| 3-01-06 | 01 | 1 | STORE-06 | manual | Edit field, verify single POST | N/A | ⬜ pending |
| 3-01-07 | 01 | 1 | STORE-07 | manual | Add spells, verify no flickering | N/A | ⬜ pending |
| 3-01-08 | 01 | 1 | STORE-08 | build | `npm run prod` | ✅ | ⬜ pending |
| 3-01-09 | 01 | 1 | STORE-09 | manual | Switch spell levels, verify data | N/A | ⬜ pending |
| 3-01-10 | 01 | 1 | STORE-10 | automated | `npm ls vuex` returns empty | ✅ | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

None — no test infrastructure exists and adding one is out of scope for this migration phase. Existing build pipeline (`npm run prod`) covers automated verification.

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Autosave fires on edits, no infinite loops | STORE-06 | Network inspection required | Edit a field, open DevTools Network tab, verify exactly one POST to `/sheet/@slug` |
| SpellList uses stable IDs (no flickering) | STORE-07 | DOM observation required | Add 3 spells to a level, edit middle one, verify others don't re-render |
| Dynamic state access works for spell levels | STORE-09 | Runtime behavior | Switch spell level tabs, verify data persists and displays correctly |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 60s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
