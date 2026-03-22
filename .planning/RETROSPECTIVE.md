# Project Retrospective

*A living document updated after each milestone. Lessons feed forward into future planning.*

## Milestone: v1.0 — Vue 3 Migration

**Shipped:** 2026-03-22
**Phases:** 3 | **Plans:** 8 | **Tasks:** 15

### What Was Built
- Vite 8 build system replacing Laravel Mix (4 entry points, Tailwind CSS v4 plugin, manifest-based asset loading)
- Vue 3.5 upgrade from Vue 2.6 with all 26 components updated (lifecycle hooks, markRaw, mitt event bus, filter-to-function)
- reactive() composable store replacing Vuex with 40 named exports
- Deep watch autosave replacing $store.subscribe
- Full cleanup of migration artifacts and documentation update

### What Worked
- Absorbing Phase 2 into Phase 1 (D-01) was the right call — avoided fighting incompatible plugin versions
- Phased migration (build tool → store → cleanup) kept the app working at every step
- reactive() composable was a natural fit — simpler than Vuex with no loss of functionality
- Quick task workflow handled 5 hotfixes efficiently without derailing phase execution

### What Was Inefficient
- ROADMAP progress table wasn't updated during execution (showed 0/4 for Phase 1 even after completion)
- Phase 2 absorbed into Phase 1 left a gap in phase numbering (1, 3, 4) which caused minor confusion
- Some bugs found post-execution (cantrip collapsing, attack add methods, Quill reactivity) that could have been caught with more structured verification

### Patterns Established
- Options API + reactive() composable works well for this codebase — no need for Composition API rewrite
- Vendor CSS served from `css/vendor/` rather than through build pipeline — simpler for static assets
- `.vue` extensions required in imports for Vite ESM resolution

### Key Lessons
1. When a plugin is incompatible with the target version, absorb the dependent phase rather than downgrading the target — cleaner than maintaining compatibility shims
2. reactive() composable with named exports is a natural replacement for flat Vuex stores — components import exactly what they need
3. Build tool migration and framework upgrade are inherently coupled when plugins don't support the old framework on the new tool

### Cost Observations
- Model mix: Quality profile (opus for subagents)
- Sessions: ~4 sessions over 2 days
- Notable: 8 plans executed in ~16 minutes total execution time — fast for a full framework migration

---

## Cross-Milestone Trends

### Process Evolution

| Milestone | Sessions | Phases | Key Change |
|-----------|----------|--------|------------|
| v1.0 | ~4 | 3 | Initial migration — established GSD workflow patterns |

### Cumulative Quality

| Milestone | Tests | Coverage | Zero-Dep Additions |
|-----------|-------|----------|-------------------|
| v1.0 | N/A (manual verification) | N/A | 1 (mitt) |

### Top Lessons (Verified Across Milestones)

1. Absorb dependent phases when tooling forces coupling — don't fight version incompatibilities
2. Flat reactive stores are simpler than state management libraries for small apps
