import Vue from 'vue';
import Sheet from './components/Sheet';
import store from './store';
import { signedNumString } from './utils';
import { i18nPlugin } from './i18n';

Vue.use(i18nPlugin);

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
