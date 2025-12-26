import Vue from 'vue';
import Sheet from './components/Sheet';
import store from './store';
import { signedNumString } from './utils';

/* -- Event bus -- */
window.sheetEvent = new Vue();

window.md = window.markdownit({
  html: true,
  linkify: true,
  typographer: true,
});

Vue.filter('signedNumString', signedNumString);

new Vue({
  el: '#sheet',
  store,
  render: (h) => h(Sheet),
});
