---
phase: 1
slug: build-tool-migration
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-21
---

# Phase 1 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | vitest (to be installed) + manual browser check |
| **Config file** | `vitest.config.js` — Wave 0 installs |
| **Quick run command** | `npm run build` (Vite build succeeds) |
| **Full suite command** | `npm run build && npx vitest run` |
| **Estimated runtime** | ~30 seconds |

---

## Sampling Rate

- **After every task commit:** Run `npm run build`
- **After every plan wave:** Run `npm run build && npx vitest run`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 30 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| 1-01-01 | 01 | 1 | BUILD-01 | build | `npm run build` | ❌ W0 | ⬜ pending |
| 1-01-02 | 01 | 1 | BUILD-02 | build | `npm run build` | ❌ W0 | ⬜ pending |
| 1-01-03 | 01 | 1 | BUILD-03 | build | `npm run build` | ❌ W0 | ⬜ pending |
| 1-01-04 | 01 | 1 | BUILD-04 | build | `npm run build` | ❌ W0 | ⬜ pending |
| 1-01-05 | 01 | 2 | BUILD-05 | manual | Browser load check | n/a | ⬜ pending |
| 1-01-06 | 01 | 2 | BUILD-06 | build | `ls dist/.vite/manifest.json` | ❌ W0 | ⬜ pending |
| 1-01-07 | 01 | 2 | BUILD-07 | build | `npm run build` | ❌ W0 | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `vite.config.js` — Vite build config with entry points, plugins, outDir
- [ ] Vitest installed if used for any automated tests
- [ ] `dist/.vite/` directory structure verification script

*If none: "Existing infrastructure covers all phase requirements."*

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Character sheet loads in browser with no console errors | BUILD-05 | Requires running PHP server and browser | Start PHP server, load `/sheet/@slug`, open devtools, check for console errors |
| Dark mode applies before first paint (settings.js) | BUILD-07 | Visual timing check | Load page, observe flash of unstyled content — should be none |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 30s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
