import Quill from 'quill';

// One helper instance, off-screen or in a detached element
const helperContainer = document.createElement('div');

const helperQuill = new Quill(helperContainer, {
  readOnly: true,
  modules: { toolbar: false },
});

export function deltaToHtml(delta) {
  helperQuill.setContents(delta);
  return helperContainer.querySelector('.ql-editor').innerHTML;
}

// When you need to display a read-only block:
export function renderReadOnlyQuill(targetElement, delta) {
  const html = deltaToHtml(delta);
  targetElement.innerHTML = html;
  // optionally add the .ql-editor class so your existing CSS works:
  targetElement.classList.add('ql-editor');
}
