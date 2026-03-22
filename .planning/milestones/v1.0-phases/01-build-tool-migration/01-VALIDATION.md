---
phase: 1
slug: build-tool-migration
status: active
nyquist_compliant: true
wave_0_complete: true
created: 2026-03-21
---

# Phase 1 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Shell commands (node -e, test, grep) |
| **Config file** | None required |
| **Quick run command** | `npm run dev` (Vite build succeeds) |
| **Full suite command** | `npm run dev && npm run prod` |
| **Estimated runtime** | ~30 seconds |

---

## Sampling Rate

- **After every task commit:** Run `npm run dev` (build succeeds)
- **After every plan wave:** Run `npm run dev && npm run prod`
- **Before `/gsd:verify-work`:** Full build must succeed, no console errors in browser
- **Max feedback latency:** 30 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | Status |
|---------|------|------|-------------|-----------|-------------------|--------|
| 01-01-T1 | 01 | 1 | BUILD-01, BUILD-06, BUILD-07 | shell | `node -e "const p=require('./package.json'); ..."` | ⬜ pending |
| 01-01-T2 | 01 | 1 | BUILD-01, BUILD-04, BUILD-05 | shell | `test -f vite.config.js && grep -q defineConfig vite.config.js && ...` | ⬜ pending |
| 01-02-T1 | 02 | 2 | BUILD-02, BUILD-03 | shell | `grep -q "function vite" index.php && ...` | ⬜ pending |
| 01-02-T2 | 02 | 2 | BUILD-06 | shell | `test ! -f webpack.mix.js && test ! -f dist/mix-manifest.json` | ⬜ pending |
| 01-03-T1 | 03 | 2 | VUE-02, VUE-08 | shell | `grep -q createStore js/store.js && ! grep -q Vue.set js/store.js` | ⬜ pending |
| 01-03-T2 | 03 | 2 | VUE-01, VUE-03, VUE-04, VUE-05, VUE-10 | shell | `grep -q createApp js/app.js && grep -q mitt js/app.js && ...` | ⬜ pending |
| 01-04-T1 | 04 | 3 | VUE-05, VUE-06, VUE-07, VUE-09 | shell | `! grep -r beforeDestroy js/components/ && grep -q markRaw js/components/QuillEditor.vue` | ⬜ pending |
| 01-04-T2 | 04 | 3 | All BUILD + VUE | build | `npm run dev && npm run prod` | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

Existing infrastructure covers all phase requirements. No test framework installation needed — all verification uses shell commands and build success checks.

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Character sheet loads in browser with no console errors | All | Requires running PHP server and browser | Start PHP server, load `/sheet/@slug`, open devtools, check for console errors |
| Dark mode applies before first paint (settings.js) | BUILD-03 | Visual timing check | Load page, observe no flash of unstyled content |
| Quill editor loads and accepts input | VUE-07 | Interactive UI test | Click a text field, type text, verify formatting toolbar appears |
| Autosave triggers after editing | VUE-03 | Requires server + network inspection | Edit a field, check network tab for POST to `/sheet/@slug` |

---

## Validation Sign-Off

- [x] All tasks have `<automated>` verify commands
- [x] Sampling continuity: no 3 consecutive tasks without automated verify
- [x] No Wave 0 gaps — all verification uses existing shell commands
- [x] No watch-mode flags
- [x] Feedback latency < 30s
- [x] `nyquist_compliant: true` set in frontmatter

**Approval:** ready
