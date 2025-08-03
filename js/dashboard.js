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
