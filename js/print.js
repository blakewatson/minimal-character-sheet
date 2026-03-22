import Vue from 'vue';
import Print from './components/Print';
import { i18nPlugin } from './i18n';
import store from './store';
import { signedNumString } from './utils';

Vue.use(i18nPlugin);

Vue.filter('signedNumString', signedNumString);

new Vue({
  el: '#print',
  store,
  render: (h) => h(Print),
});
