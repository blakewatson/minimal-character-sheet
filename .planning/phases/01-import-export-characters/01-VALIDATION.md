---
phase: 1
slug: import-export-characters
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-25
---

# Phase 1 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Browser manual + PHP integration (no automated test framework in project) |
| **Config file** | none — no test framework configured |
| **Quick run command** | `npm run prod` (build succeeds) |
| **Full suite command** | `npm run prod && php -r "require 'index.php';"` |
| **Estimated runtime** | ~5 seconds |

---

## Sampling Rate

- **After every task commit:** Run `npm run prod`
- **After every plan wave:** Run `npm run prod` + manual smoke test
- **Before `/gsd:verify-work`:** Full build must be green + manual UAT
- **Max feedback latency:** 5 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| TBD | 01 | 1 | D-01..D-04 | manual | `npm run prod` | N/A | ⬜ pending |
| TBD | 01 | 1 | D-05..D-08 | manual | `npm run prod` | N/A | ⬜ pending |
| TBD | 01 | 1 | D-09..D-11 | manual | `npm run prod` | N/A | ⬜ pending |
| TBD | 01 | 1 | D-12..D-14 | manual | `npm run prod` | N/A | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

*Existing infrastructure covers all phase requirements — no test framework install needed. This project uses manual verification with build-time checks.*

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| JSON export downloads correct file | D-01, D-02 | Browser download API | Click export, verify file contents |
| Markdown export has all fields | D-03 | Content formatting | Export, open .md, check all sections present |
| Filename uses character name | D-04 | File system interaction | Export character named "Test Hero", verify filename |
| Import creates new sheet | D-07 | End-to-end flow | Upload exported JSON, verify new sheet appears |
| Invalid JSON shows error | D-08 | Error handling UX | Upload malformed file, verify toast error |
| Export button per sheet row | D-12 | UI placement | Check dashboard, verify button on each row |
| Import button at top level | D-13 | UI placement | Check dashboard top bar |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 5s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
