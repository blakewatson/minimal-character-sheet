# Phase 1: Import & Export Characters - Context

**Gathered:** 2026-03-25
**Status:** Ready for planning

<domain>
## Phase Boundary

Allow users to export individual character sheets from the app (as JSON + Markdown files) and import character sheet JSON files to create new sheets. Single-sheet operations only — no bulk export/import.

</domain>

<decisions>
## Implementation Decisions

### Export Format
- **D-01:** Export as **JSON + Markdown** — two separate file downloads per export
- **D-02:** JSON export contains the **full JSON blob** from the store (same data as autosave)
- **D-03:** Markdown export is a **full detail dump** — all stats, abilities, skills, spells, equipment, AND all rich text fields (backstory, notes, features)
- **D-04:** Filename based on **character name** (sanitized) — e.g., `Gandalf.json`, `Gandalf.md`

### Import Source
- **D-05:** **File upload only** — user picks a `.json` file from their device
- **D-06:** **Own format only** — only accept JSON files exported from this app (no D&D Beyond or other tool formats)

### Import Flow
- **D-07:** Import **always creates a new sheet** — no replacing or merging with existing sheets
- **D-08:** **Reject invalid files with error message** — validate JSON structure (expected keys like `characterName`, `abilities`, `skills` must exist), show error toast if invalid, don't create anything

### JSON Round-Trip Safety
- **D-09:** Export uses `JSON.stringify()` on the store's reactive state — same serialization path as autosave, so if autosave works, export works
- **D-10:** Import uses `JSON.parse()` then structural validation (expected top-level keys exist), then passes to existing `create_sheet_with_data()` PHP method
- **D-11:** Rich text fields (Quill HTML) are stored as strings within the JSON blob and are safely escaped by the JSON serializer — no special handling needed

### UI Placement
- **D-12:** Export button on **dashboard per-sheet** — each sheet row gets an export action
- **D-13:** Import button at **dashboard top-level** — near the existing "Add Sheet" button
- **D-14:** **Single sheet only** — no bulk export feature

### Claude's Discretion
- Exact Markdown template layout and formatting
- Error message wording for invalid imports
- Sanitization rules for character name → filename conversion

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Data Model & Storage
- `classes/models/Sheet.php` — `create_sheet_with_data()` method already handles creating sheets from JSON data; `decode_sheet_data()` handles legacy double-encoded JSON
- `js/store.js` — `defaultState` object defines all character sheet fields; `initializeState` handles hydration

### Routing & Controllers
- `index.php` — All route definitions; new import/export routes will be added here
- `classes/controllers/Dashboard.php` — Sheet CRUD operations; import/export handlers go here

### Frontend Entry Points
- `js/dashboard.js` — Vanilla JS for dashboard interactions; export/import button handlers go here
- `templates/dashboard.html` — Dashboard HTML template; UI elements for export/import buttons

### Existing Patterns
- `js/components/Sheet.vue` — `saveSheetState()` shows the existing JSON serialization pattern for character data

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `Sheet::create_sheet_with_data()` — Already handles creating a sheet from JSON data with proper slug generation and field remapping (id, slug, characterName). Can be used directly for import.
- `store.js::defaultState` — Defines the canonical field list. Can be used to validate imported JSON structure.
- Dashboard JS pattern — `dashboard.js` uses fetch + CSRF token for API calls. Export/import will follow the same pattern.
- Notyf toast library — Already used for success/error notifications throughout the app.

### Established Patterns
- **CSRF handling**: All POST requests include CSRF token via `X-AJAX-CSRF` header; new CSRF returned in response
- **JSON API responses**: Backend returns `{ success: true/false, reason: '...' }` format
- **File downloads**: No existing download pattern — this will be a new capability (client-side Blob + URL.createObjectURL)
- **File uploads**: No existing upload pattern — new capability (FormData + file input or drag-and-drop)

### Integration Points
- Dashboard sheet list — export button per row
- Dashboard top bar — import button near "Add Sheet"
- `index.php` routes — new GET/POST routes for export/import
- `Dashboard.php` controller — new methods for export/import handlers

</code_context>

<specifics>
## Specific Ideas

- User emphasized JSON round-trip safety: export must produce valid JSON that import can always ingest, even with user-generated content in rich text fields
- The same serialization path (JSON.stringify/JSON.parse) used by autosave should be used for export/import to guarantee consistency

</specifics>

<deferred>
## Deferred Ideas

- **Bulk export** (all sheets as ZIP) — could be its own phase if users need backup functionality
- **D&D Beyond import** — format detection and field mapping for external tools
- **Export from within sheet view** — currently scoped to dashboard only
- **Import via paste** — paste raw JSON instead of file upload

</deferred>

---

*Phase: 01-import-export-characters*
*Context gathered: 2026-03-25*
