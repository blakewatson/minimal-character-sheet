# Phase 1: Import & Export Characters - Research

**Researched:** 2026-03-25
**Domain:** Client-side file I/O (download/upload), JSON validation, Markdown generation
**Confidence:** HIGH

## Summary

This phase adds two features to the dashboard: exporting a character sheet as JSON + Markdown files, and importing a JSON file to create a new sheet. Both operations are single-sheet, dashboard-level actions.

The implementation is straightforward because the app already has all the building blocks: `getJSON()` serializes the store, `create_sheet_with_data()` creates sheets from JSON, and `dashboard.js` has the established pattern for fetch + CSRF interactions. No new libraries are needed. The export is entirely client-side (Blob + download), while import requires a new PHP endpoint that accepts a JSON file upload and delegates to the existing `create_sheet_with_data()` method.

**Primary recommendation:** Keep export fully client-side (fetch sheet data from existing `/sheet-data/@slug` endpoint, then Blob-download). Keep import as a POST with JSON body to a new `/import-sheet` endpoint. No new dependencies required.

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions
- **D-01:** Export as JSON + Markdown -- two separate file downloads per export
- **D-02:** JSON export contains the full JSON blob from the store (same data as autosave)
- **D-03:** Markdown export is a full detail dump -- all stats, abilities, skills, spells, equipment, AND all rich text fields (backstory, notes, features)
- **D-04:** Filename based on character name (sanitized) -- e.g., `Gandalf.json`, `Gandalf.md`
- **D-05:** File upload only -- user picks a `.json` file from their device
- **D-06:** Own format only -- only accept JSON files exported from this app (no D&D Beyond or other tool formats)
- **D-07:** Import always creates a new sheet -- no replacing or merging with existing sheets
- **D-08:** Reject invalid files with error message -- validate JSON structure (expected keys like `characterName`, `abilities`, `skills` must exist), show error toast if invalid, don't create anything
- **D-09:** Export uses `JSON.stringify()` on the store's reactive state -- same serialization path as autosave
- **D-10:** Import uses `JSON.parse()` then structural validation (expected top-level keys exist), then passes to existing `create_sheet_with_data()` PHP method
- **D-11:** Rich text fields (Quill HTML) are stored as strings within the JSON blob and are safely escaped by the JSON serializer -- no special handling needed
- **D-12:** Export button on dashboard per-sheet -- each sheet row gets an export action
- **D-13:** Import button at dashboard top-level -- near the existing "Add Sheet" button
- **D-14:** Single sheet only -- no bulk export feature

### Claude's Discretion
- Exact Markdown template layout and formatting
- Error message wording for invalid imports
- Sanitization rules for character name to filename conversion

### Deferred Ideas (OUT OF SCOPE)
- Bulk export (all sheets as ZIP)
- D&D Beyond import
- Export from within sheet view
- Import via paste
</user_constraints>

## Standard Stack

### Core
No new libraries needed. Everything uses built-in browser APIs and existing PHP.

| Technology | Version | Purpose | Why Standard |
|-----------|---------|---------|--------------|
| Blob API | Browser built-in | Create downloadable files client-side | Standard browser API, no polyfill needed |
| URL.createObjectURL | Browser built-in | Generate download links from Blobs | Standard approach for client-side downloads |
| FileReader API | Browser built-in | Read uploaded JSON files | Standard browser API for file input |
| `<input type="file">` | HTML | File picker for import | Native file input, no library needed |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|-----------|-----------|----------|
| Client-side Blob download | Server-side file generation endpoint | Unnecessary server round-trip; all data is already available client-side on the dashboard |
| FileReader for import | Send file to server directly via FormData | Either works; reading client-side first allows validation before hitting the server |

## Architecture Patterns

### Data Flow: Export

```
Dashboard page
  -> User clicks Export on a sheet row
  -> Fetch sheet data: GET /sheet-data/{slug}
  -> Receive full sheet JSON (same format as autosave)
  -> JSON export: JSON.stringify(data) -> Blob -> download as {name}.json
  -> Markdown export: Convert data to markdown string -> Blob -> download as {name}.md
  -> Two files downloaded sequentially
```

**Key insight:** The dashboard already has each sheet's `slug` and `name` in the DOM (via F3 template variables). The existing `/sheet-data/@slug` endpoint returns the full sheet data including the JSON blob. No new backend endpoint is needed for export.

### Data Flow: Import

```
Dashboard page
  -> User clicks Import button
  -> Hidden <input type="file" accept=".json"> triggered
  -> FileReader reads the file
  -> JSON.parse() the content
  -> Client-side validation: check required top-level keys exist
  -> If invalid: show Notyf error toast, stop
  -> If valid: POST /import-sheet with JSON body + CSRF token
  -> Server: validate again, call create_sheet_with_data()
  -> Server: return { success: true, slug: newSlug }
  -> Client: redirect to /dashboard (or show success toast)
```

### File Locations for New Code

```
js/dashboard.js           # Add export/import button handlers
js/export.js              # NEW: Export logic (generateJSON, generateMarkdown, downloadFile, sanitizeFilename)
templates/dashboard.html  # Add export buttons per row, import button at top
classes/controllers/Dashboard.php  # Add import_sheet() method
index.php                 # Add POST /import-sheet route
```

### Pattern: Client-Side File Download (Blob)

```javascript
// Source: Standard browser API pattern
function downloadFile(content, filename, mimeType) {
  const blob = new Blob([content], { type: mimeType });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
```

### Pattern: File Upload via Hidden Input

```javascript
// Source: Standard browser API pattern
function triggerFileUpload(onFileSelected) {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.json';
  input.addEventListener('change', (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => onFileSelected(e.target.result);
    reader.readAsText(file);
  });
  input.click();
}
```

### Pattern: Filename Sanitization

```javascript
function sanitizeFilename(name) {
  if (!name || !name.trim()) return 'character';
  return name
    .trim()
    .replace(/[<>:"/\\|?*\x00-\x1f]/g, '') // Remove illegal filename chars
    .replace(/\s+/g, '-')                    // Spaces to hyphens
    .substring(0, 100);                      // Limit length
}
```

### Anti-Patterns to Avoid
- **Generating files server-side for export:** All sheet data is available on the dashboard page or via existing GET endpoints. A server-side export endpoint adds unnecessary complexity.
- **Using FormData for import:** Since we want to validate the JSON client-side first, reading the file client-side via FileReader and then POSTing the parsed JSON is cleaner than sending raw file bytes.
- **Skipping server-side validation on import:** Even though the client validates, the server MUST also validate the JSON structure before calling `create_sheet_with_data()`. Never trust client-side validation alone.

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Sheet data retrieval for export | Custom data-fetching endpoint | Existing `GET /sheet-data/@slug` endpoint | Already returns the complete sheet data including JSON blob |
| Sheet creation from JSON | Custom insert logic | `Sheet::create_sheet_with_data()` | Already handles slug generation, field remapping (id, slug, characterName), JSON encoding |
| Toast notifications | Custom notification UI | Notyf (already in the app) | Already imported and used throughout dashboard interactions |
| CSRF token handling | Custom token logic | Existing `X-AJAX-CSRF` header + `#csrf` hidden input pattern | Used by all dashboard JS interactions already |

## Common Pitfalls

### Pitfall 1: Double-encoding JSON on import
**What goes wrong:** Import receives JSON string, calls `JSON.stringify()` again before sending to server, server receives double-encoded string.
**Why it happens:** Confusion about whether to send raw JSON or URL-encoded form data.
**How to avoid:** Send the import data as a JSON body with `Content-Type: application/json`, or send the parsed-and-revalidated data as a URL-encoded form field. The existing `create_sheet_with_data()` already handles both string and decoded data.
**Warning signs:** Character names showing with escaped quotes, or `decode_sheet_data()` log messages about double-encoding.

### Pitfall 2: Export missing sheet data (empty data field)
**What goes wrong:** Dashboard template has sheet name/slug but NOT the full JSON data blob. Trying to export from template variables alone gives incomplete data.
**Why it happens:** `get_all_sheets()` returns decoded data but the dashboard template only displays `name` and `slug`.
**How to avoid:** Use the existing `GET /sheet-data/@slug` endpoint to fetch full sheet data for export. This returns the complete JSON blob.
**Warning signs:** Exported JSON file contains only `name` and `slug` but no abilities, skills, etc.

### Pitfall 3: Sanitized filename produces empty string
**What goes wrong:** Character with a name like `???` or only special characters gets sanitized to an empty string, producing a file named `.json`.
**Why it happens:** All characters in the name are illegal filename characters.
**How to avoid:** Always fall back to a default name like `character` if sanitization produces an empty string.
**Warning signs:** Files downloaded with names like `.json` or `.md`.

### Pitfall 4: is_2024 flag lost during import
**What goes wrong:** Imported sheet loses its 2024/2014 rules mode because `is_2024` is a column on the sheet table, not just in the JSON blob.
**Why it happens:** `create_sheet_with_data()` accepts `is_2024` as a separate parameter, not from within the JSON data. If import doesn't extract and pass it, the default (true) is used.
**How to avoid:** When importing, extract `is_2024` from the JSON data (it is stored in `store.js` state as `state.is_2024`) and pass it as the fourth argument to `create_sheet_with_data()`. Also consider storing it in the exported JSON metadata.
**Warning signs:** All imported characters default to 2024 rules regardless of their original setting.

### Pitfall 5: CSRF token not refreshed after import
**What goes wrong:** After a successful import, subsequent dashboard actions (delete, toggle public) fail with CSRF errors.
**Why it happens:** The import POST consumes the current CSRF token but the response token is not written back to the `#csrf` hidden input.
**How to avoid:** Follow the same pattern as `bindCheckboxes` and `bindDeleteButtons` -- update `document.querySelector('#csrf').value` from the response JSON.
**Warning signs:** 400 errors on dashboard actions after importing a sheet.

### Pitfall 6: Rich text (Quill HTML) breaks Markdown export
**What goes wrong:** Quill stores rich text as HTML delta objects. Dumping raw HTML or delta JSON into Markdown produces unreadable output.
**Why it happens:** Fields like `equipmentText`, `featuresText`, `backstoryText` etc. contain Quill delta objects (with `ops` array), not plain strings.
**How to avoid:** For Markdown export, either (a) strip HTML tags and output plain text, or (b) convert the Quill delta `ops` to a readable text representation. Since these are `{}` objects with an `ops` property containing insert operations, extract the text from each `insert` op.
**Warning signs:** Markdown file contains `[object Object]` or raw JSON for text fields.

## Code Examples

### Existing: Sheet data structure (from store.js defaultState)
The `defaultState` object in `store.js` defines all fields. Key fields for validation:
- `characterName` (string)
- `abilities` (array of 6 objects with `name` and `score`)
- `skills` (array of 18 objects)
- `attacks` (array)
- `coins` (array of 5 objects)
- Rich text fields: `equipmentText`, `featuresText`, `backstoryText`, `personalityText`, `proficienciesText`, `treasureText`, `organizationsText`, `notesText` (Quill delta objects)
- Spell fields: `cantripsList`, `lvl1Spells` through `lvl9Spells`

### Existing: create_sheet_with_data() (from Sheet.php)
```php
// Source: classes/models/Sheet.php line 28
public function create_sheet_with_data( $name, $email, $data, $is_2024 = true ) {
    // Generates random slug, sets fields, saves
    // Handles both JSON strings and decoded arrays
    // Overwrites id, slug, characterName in the data with new values
}
```

### Existing: Dashboard fetch + CSRF pattern (from dashboard.js)
```javascript
// Source: js/dashboard.js
var csrf = document.querySelector('#csrf').value;
fetch(`/make-public/${sheetSlug}`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-AJAX-CSRF': csrf,
  },
  body: formBody,
})
  .then((r) => r.json())
  .then((data) => {
    if ('csrf' in data) {
      document.querySelector('#csrf').value = data.csrf;
    }
  });
```

### Existing: Dashboard template per-sheet row (from dashboard.html)
```html
<!-- Source: templates/dashboard.html line 42-100 -->
<repeat group="{{ @sheets }}" value="{{ @sheet }}">
  <li class="mb-4">
    <p class="mb-1.5">
      <a href="/sheet/{{ @sheet.slug }}">{{ @sheet.name }}</a>
    </p>
    <div class="flex items-center gap-2 ...">
      <!-- Public checkbox, Print link, Delete button -->
      <!-- Export button will go here, same pattern as Print/Delete -->
    </div>
  </li>
</repeat>
```

### Recommended: Import validation (client-side)
```javascript
// Validate that the imported JSON has the expected structure
function validateSheetJSON(data) {
  const requiredKeys = ['characterName', 'abilities', 'skills'];
  for (const key of requiredKeys) {
    if (!(key in data)) {
      return { valid: false, reason: `Missing required field: ${key}` };
    }
  }
  if (!Array.isArray(data.abilities) || data.abilities.length !== 6) {
    return { valid: false, reason: 'Invalid abilities data' };
  }
  if (!Array.isArray(data.skills)) {
    return { valid: false, reason: 'Invalid skills data' };
  }
  return { valid: true };
}
```

### Recommended: Markdown template structure
```markdown
# {characterName}

**Race:** {race} | **Class:** {className} | **Level:** {level}
**Background:** {background} | **Alignment:** {alignment} | **XP:** {xp}

## Vitals
- **HP:** {hp}/{maxHp} (Temp: {tempHp})
- **AC:** {ac} | **Speed:** {speed} | **Initiative:** {initiative}
- **Hit Die:** {hitDie} (Total: {totalHitDie})

## Ability Scores
| STR | DEX | CON | INT | WIS | CHA |
|-----|-----|-----|-----|-----|-----|
| {scores...} |

## Skills
- [x] Acrobatics (DEX): +N
- [ ] Animal Handling (WIS): +N
...

## Attacks
| Name | Attack Bonus | Damage |
|------|-------------|--------|
...

## Spells
**Class:** {spClass} | **Ability:** {spAbility} | **Save DC:** {spSave} | **Attack:** {spAttack}

### Cantrips
...

### Level 1 (Slots: N, Expended: N)
...

## Equipment
{equipmentText as plain text}

## Features & Traits
{featuresText as plain text}

## Backstory
{backstoryText as plain text}

## Notes
{notesText as plain text}
```

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | None detected (no test config files found) |
| Config file | None |
| Quick run command | N/A |
| Full suite command | N/A |

### Phase Requirements to Test Map

Since there is no existing test framework, validation will rely on manual testing:

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| D-01 | Two files downloaded on export | manual | N/A | N/A |
| D-02 | JSON contains full store data | manual | N/A | N/A |
| D-03 | Markdown contains all sheet sections | manual | N/A | N/A |
| D-04 | Filename based on sanitized character name | manual | N/A | N/A |
| D-05 | File upload picker for .json files | manual | N/A | N/A |
| D-07 | Import creates new sheet | manual | N/A | N/A |
| D-08 | Invalid JSON rejected with error toast | manual | N/A | N/A |
| D-09 | Export JSON matches autosave format | manual | N/A | N/A |
| D-10 | Import round-trips through create_sheet_with_data | manual | N/A | N/A |

### Wave 0 Gaps
No automated test infrastructure exists in the project. All validation is manual. Setting up a test framework is out of scope for this phase.

## Sources

### Primary (HIGH confidence)
- `classes/models/Sheet.php` -- `create_sheet_with_data()` method reviewed, handles JSON strings and arrays
- `js/store.js` -- `defaultState` defines all 50+ fields, `getJSON()` serializes state
- `js/dashboard.js` -- Existing CSRF + fetch pattern for dashboard interactions
- `templates/dashboard.html` -- Current dashboard layout with per-sheet action buttons
- `classes/controllers/Dashboard.php` -- All existing sheet CRUD methods reviewed
- `index.php` -- All current routes reviewed for naming conventions
- `js/components/Sheet.vue` -- `saveSheetState()` serialization pattern reviewed

### Secondary (MEDIUM confidence)
- Blob/FileReader API usage -- standard browser APIs, well-documented on MDN

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH -- No new dependencies, all browser built-ins
- Architecture: HIGH -- Follows existing dashboard patterns exactly
- Pitfalls: HIGH -- Identified from direct code analysis of existing codebase

**Research date:** 2026-03-25
**Valid until:** 2026-04-25 (stable; no external dependencies to go stale)
