# Phase 1: Import & Export Characters - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-03-25
**Phase:** 01-import-export-characters
**Areas discussed:** Export format & content, Import sources & compatibility, Import flow & conflict handling, UI placement & triggers

---

## Export Format & Content

### Q1: What format should characters export as?

| Option | Description | Selected |
|--------|-------------|----------|
| JSON only | Native format — matches internal JSON blob storage. Lightweight, re-importable. | |
| JSON + PDF | JSON for data portability, plus a styled PDF for printing/sharing. Significant complexity. | |
| JSON + Markdown | JSON for re-import, plus a human-readable Markdown summary. | ✓ |

**User's choice:** JSON + Markdown
**Notes:** Two separate file downloads per export operation.

### Q2: Should the export include ALL sheet data or a curated subset?

| Option | Description | Selected |
|--------|-------------|----------|
| Full JSON blob | Export everything in the store — abilities, skills, spells, equipment, bio, settings. Guarantees perfect re-import. | ✓ |
| Curated subset | Strip internal fields (id, slug, readOnly, levelData) and export only character data. | |

**User's choice:** Full JSON blob
**Notes:** None

### Q3: What should the exported filename look like?

| Option | Description | Selected |
|--------|-------------|----------|
| Character name based | e.g., "Gandalf.json" / "Gandalf.md" — sanitized character name. | ✓ |
| Name + date | e.g., "Gandalf-2026-03-25.json" — includes export date for versioning. | |
| You decide | Claude picks a sensible naming convention. | |

**User's choice:** Character name based
**Notes:** None

### Q4: How detailed should the Markdown export be?

| Option | Description | Selected |
|--------|-------------|----------|
| Summary sheet | Key stats, abilities, skills, spells, equipment — a readable character overview. | |
| Full detail dump | Everything including all rich text fields (backstory, notes, features). | ✓ |
| You decide | Claude picks the right level of detail. | |

**User's choice:** Full detail dump
**Notes:** None

---

## Import Sources & Compatibility

### Q1: How should users provide import data?

| Option | Description | Selected |
|--------|-------------|----------|
| File upload only | User picks a .json file from their device. Simple, standard. | ✓ |
| File upload + paste JSON | Upload a file OR paste raw JSON into a text area. | |
| File upload + URL fetch | Upload a file OR provide a URL to fetch JSON from. | |

**User's choice:** File upload only
**Notes:** None

### Q2: Support files from other D&D tools?

| Option | Description | Selected |
|--------|-------------|----------|
| Own format only | Only import JSON files exported from this app. Simple validation. | ✓ |
| Own format + D&D Beyond | Also support D&D Beyond JSON exports. Requires field mapper. | |
| Extensible format detection | Detect format automatically. Start with own format, architecture allows adding others. | |

**User's choice:** Own format only
**Notes:** None

---

## Import Flow & Conflict Handling

### Q1: What happens when a user imports a character?

| Option | Description | Selected |
|--------|-------------|----------|
| Always create new sheet | Import always creates a brand new sheet with a fresh slug. No overwriting risk. | ✓ |
| Ask: new or replace | Let user choose to create new or replace existing from their list. | |
| Smart merge | Detect if character exists by name, offer to update or create duplicate. | |

**User's choice:** Always create new sheet
**Notes:** None

### Q2: How should invalid or incomplete import files be handled?

| Option | Description | Selected |
|--------|-------------|----------|
| Reject with error message | Validate JSON structure, show error toast if invalid, don't create anything. | ✓ |
| Import what's valid, fill defaults | Accept partial data — missing fields get default values. | |
| You decide | Claude picks the right validation approach. | |

**User's choice:** Reject with error message
**Notes:** User emphasized JSON round-trip safety — export must always produce valid JSON that import can ingest. Same serialization path as autosave ensures consistency.

---

## UI Placement & Triggers

### Q1: Where should the Export button live?

| Option | Description | Selected |
|--------|-------------|----------|
| Dashboard per-sheet | Export button on each sheet row in the dashboard list. | ✓ |
| Inside sheet view | Export button within the character sheet editor. | |
| Both dashboard + sheet view | Available from either location. | |

**User's choice:** Dashboard per-sheet
**Notes:** None

### Q2: Where should the Import button live?

| Option | Description | Selected |
|--------|-------------|----------|
| Dashboard top-level | An "Import" button near the "Add Sheet" button. | ✓ |
| Inside Add Sheet flow | "Import from file" option within the Add Sheet page/form. | |
| You decide | Claude picks best placement based on existing layout. | |

**User's choice:** Dashboard top-level
**Notes:** None

### Q3: Should bulk export be supported?

| Option | Description | Selected |
|--------|-------------|----------|
| No, single sheet only | Export one sheet at a time. Bulk export is a separate feature if needed later. | ✓ |
| Yes, as a ZIP download | "Export All" button that bundles all sheets into a ZIP file. | |
| You decide | Claude decides based on complexity trade-offs. | |

**User's choice:** No, single sheet only
**Notes:** None

---

## Claude's Discretion

- Exact Markdown template layout and formatting
- Error message wording for invalid imports
- Sanitization rules for character name → filename conversion

## Deferred Ideas

- Bulk export (all sheets as ZIP) — potential backup feature
- D&D Beyond import — format detection and field mapping
- Export from within sheet view — currently dashboard only
- Import via paste — paste raw JSON instead of file upload
