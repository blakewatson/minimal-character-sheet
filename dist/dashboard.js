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
  var sheets = Array.from(document.querySelectorAll('[data-sheet]'));
  if (sheets.length === 0) {
    return;
  }
  bindCheckboxes(sheets);
}
function bindCheckboxes(sheets) {
  sheets.forEach(function (sheet) {
    sheet.addEventListener('input', function (event) {
      var isPublic = event.target.checked;
      var sheetSlug = sheet.getAttribute('data-sheet');
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
      })["finally"](function (data) {
        if ('csrf' in data) {
          document.querySelector('#csrf').value = data.csrf;
        }
      });
    });
  });
}
/******/ })()
;