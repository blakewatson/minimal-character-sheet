// Initialize baguettebox for screenshot gallery
function initBaguettebox() {
  if (typeof baguetteBox !== "undefined") {
    console.log('baguetteBox is defined');
    baguetteBox.run('.screenshot-gallery');
  }
}


document.addEventListener('DOMContentLoaded', initBaguettebox);