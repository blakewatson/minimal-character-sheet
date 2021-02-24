initDashboard();

export function initDashboard() {
  const sheets = Array.from(document.querySelectorAll('[data-sheet]'));
  
  if (sheets.length === 0) {
    return;
  }
  
  bindCheckboxes(sheets);
}

function bindCheckboxes(sheets) {
  sheets.forEach(sheet => {
    sheet.addEventListener('input', event => {
      var isPublic = event.target.checked;
      var sheetId = sheet.getAttribute('data-sheet');
      var csrf = document.querySelector('#csrf').value;
      var formBody = new URLSearchParams();
      
      formBody.set('is_public', isPublic);
      formBody = formBody.toString();

      fetch(`/make-public/${sheetId}`, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-AJAX-CSRF': csrf
          },
          body: formBody
      })
      .then(r => r.json())
      .then(data => {
          if(data.success) {
              document.querySelector('#csrf').value = data.csrf;
          }
      });
    });
  })
}