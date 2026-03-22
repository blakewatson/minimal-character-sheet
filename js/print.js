import { createApp } from 'vue';
import Print from './components/Print.vue';
import store from './store';
import { signedNumString } from './utils';
import { i18nPlugin } from './i18n';

const app = createApp(Print);
app.use(store);
app.use(i18nPlugin);

// Register signedNumString as a global property (replaces Vue.filter)
app.config.globalProperties.$signedNumString = signedNumString;

app.mount('#print');
