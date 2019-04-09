import Vue from 'vue';
import Sheet from './components/Sheet';
import store from './store';

/* -- Event bus -- */
window.sheetEvent = new Vue();

Vue.filter('signedNumString', num => {
	num = parseInt(num);
	if(num > 0) return `+${num}`;
	return num.toString();
});

new Vue({
	el: '#sheet',
	store,
	render: h => h(Sheet)
});