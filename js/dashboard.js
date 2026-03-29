import { exportSheetJSON, exportSheetMarkdown } from './export.js';
import { Notyf } from 'notyf';

initDashboard();

export function initDashboard() {
  const isPublicCheckboxes = Array.from(
    document.querySelectorAll('[data-is-public]'),
  );

  if (isPublicCheckboxes.length > 0) {
    bindCheckboxes(isPublicCheckboxes);
  }

  const deleteButtons = Array.from(
    document.querySelectorAll('[data-delete-sheet]'),
  );

  if (deleteButtons.length > 0) {
    bindDeleteButtons(deleteButtons);
  }

  const exportJsonButtons = Array.from(
    document.querySelectorAll('[data-export-json]'),
  );

  if (exportJsonButtons.length > 0) {
    bindExportButtons(exportJsonButtons, exportSheetJSON);
  }

  const exportMdButtons = Array.from(
    document.querySelectorAll('[data-export-markdown]'),
  );

  if (exportMdButtons.length > 0) {
    bindExportButtons(exportMdButtons, exportSheetMarkdown);
  }

  const importButton = document.querySelector('[data-import-sheet]');

  if (importButton) {
    bindImportButton(importButton);
  }
}

function bindCheckboxes(isPublicCheckboxes) {
  isPublicCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('input', (event) => {
      var isPublic = event.target.checked;
      var sheetSlug = checkbox.getAttribute('data-sheet');
      var csrf = document.querySelector('#csrf').value;
      var formBody = new URLSearchParams();

      formBody.set('is_public', isPublic);
      formBody = formBody.toString();

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
        })
        .catch((resp) => {
          if ('csrf' in resp) {
            document.querySelector('#csrf').value = resp.csrf;
          }
        });
    });
  });
}

function bindExportButtons(buttons, exportFn) {
  buttons.forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      var slug = btn.getAttribute('data-sheet');
      var name = btn.getAttribute('data-name');
      exportFn(slug, name);
    });
  });
}

function validateSheetJSON(data) {
  if (!data || typeof data !== 'object') {
    return { valid: false, reason: 'File does not contain valid JSON data.' };
  }
  const requiredKeys = ['characterName', 'abilities', 'skills'];
  for (const key of requiredKeys) {
    if (!(key in data)) {
      return { valid: false, reason: 'Missing required field: ' + key };
    }
  }
  if (!Array.isArray(data.abilities) || data.abilities.length !== 6) {
    return {
      valid: false,
      reason: 'Invalid abilities data — expected 6 ability scores.',
    };
  }
  if (!Array.isArray(data.skills)) {
    return { valid: false, reason: 'Invalid skills data.' };
  }
  return { valid: true };
}

function bindImportButton(btn) {
  btn.addEventListener('click', (event) => {
    event.preventDefault();

    // Create hidden file input per D-05 (file upload only)
    var input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json'; // per D-06, only .json files

    input.addEventListener('change', (e) => {
      var file = e.target.files[0];
      if (!file) return;

      var reader = new FileReader();
      reader.onload = (loadEvent) => {
        var content = loadEvent.target.result;
        var data;
        var notyf = new Notyf();

        // Parse JSON
        try {
          data = JSON.parse(content);
        } catch (err) {
          notyf.error('Invalid file: not valid JSON.');
          return;
        }

        // Client-side validation per D-08
        var validation = validateSheetJSON(data);
        if (!validation.valid) {
          notyf.error('Invalid character sheet: ' + validation.reason);
          return;
        }

        // POST to import endpoint per D-10
        var csrf = document.querySelector('#csrf').value;
        fetch('/import-sheet', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-AJAX-CSRF': csrf,
          },
          body: JSON.stringify({ data: data }),
        })
          .then((r) => r.json())
          .then((resp) => {
            // Refresh CSRF per RESEARCH.md Pitfall 5
            if ('csrf' in resp) {
              document.querySelector('#csrf').value = resp.csrf;
            }
            if (resp.success) {
              // Reload dashboard to show the new sheet per D-07
              window.location = '/dashboard';
            } else {
              notyf.error('Import failed: ' + (resp.reason || 'Unknown error'));
            }
          })
          .catch((err) => {
            notyf.error('Import failed: network error.');
          });
      };
      reader.readAsText(file);
    });

    input.click();
  });
}

function bindDeleteButtons(deleteButtons) {
  deleteButtons.forEach((deleteBtn) => {
    deleteBtn.addEventListener('click', (event) => {
      event.preventDefault();

      var csrf = document.querySelector('#csrf').value;
      var sheetSlug = deleteBtn.getAttribute('data-sheet');

      if (confirm('Are you sure you want to delete this sheet?')) {
        fetch(`/sheet/${sheetSlug}`, {
          method: 'delete',
          headers: {
            'X-Ajax-Csrf': csrf,
          },
        })
          .then((r) => r.json())
          .then((r) => {
            window.location = '/dashboard';
          });
      }
    });
  });
}
