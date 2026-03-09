/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
/*!*************************!*\
  !*** ./js/dashboard.js ***!
  \*************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initDashboard: () => (/* binding */ initDashboard)
/* harmony export */ });
initDashboard();
function initDashboard() {
  var isPublicCheckboxes = Array.from(document.querySelectorAll('[data-is-public]'));
  if (isPublicCheckboxes.length > 0) {
    bindCheckboxes(isPublicCheckboxes);
  }
  var deleteButtons = Array.from(document.querySelectorAll('[data-delete-sheet]'));
  if (deleteButtons.length > 0) {
    bindDeleteButtons(deleteButtons);
  }
}
function bindCheckboxes(isPublicCheckboxes) {
  isPublicCheckboxes.forEach(function (checkbox) {
    checkbox.addEventListener('input', function (event) {
      var isPublic = event.target.checked;
      var sheetSlug = checkbox.getAttribute('data-sheet');
      var csrf = document.querySelector('#csrf').value;
      var formBody = new URLSearchParams();
      formBody.set('is_public', isPublic);
      formBody = formBody.toString();
      fetch("/make-public/".concat(sheetSlug), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-AJAX-CSRF': csrf
        },
        body: formBody
      }).then(function (r) {
        return r.json();
      }).then(function (data) {
        if ('csrf' in data) {
          document.querySelector('#csrf').value = data.csrf;
        }
      })["catch"](function (resp) {
        if ('csrf' in resp) {
          document.querySelector('#csrf').value = resp.csrf;
        }
      });
    });
  });
}
function bindDeleteButtons(deleteButtons) {
  deleteButtons.forEach(function (deleteBtn) {
    deleteBtn.addEventListener('click', function (event) {
      event.preventDefault();
      var csrf = document.querySelector('#csrf').value;
      var sheetSlug = deleteBtn.getAttribute('data-sheet');
      if (confirm('Are you sure you want to delete this sheet?')) {
        fetch("/sheet/".concat(sheetSlug), {
          method: 'delete',
          headers: {
            'X-Ajax-Csrf': csrf
          }
        }).then(function (r) {
          return r.json();
        }).then(function (r) {
          window.location = '/dashboard';
        });
      }
    });
  });
}
/******/ })()
;