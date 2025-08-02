import Vue from 'vue';
import Print from './components/Print';
import store from './store';
import { signedNumString } from './utils';

Vue.filter('signedNumString', signedNumString);

new Vue({
  el: '#print',
  store,
  render: (h) => h(Print),
});
