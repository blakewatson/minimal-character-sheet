import { createApp } from 'vue';
import mitt from 'mitt';
import Sheet from './components/Sheet.vue';
import { signedNumString } from './utils';
import { i18nPlugin } from './i18n';

/* -- Event bus (replaces new Vue() instance) -- */
window.sheetEvent = mitt();

window.md = window.markdownit({
  html: true,
  linkify: true,
  typographer: true,
});

const app = createApp(Sheet);
app.use(i18nPlugin);

// Register signedNumString as a global property (replaces Vue.filter)
// Components access via this.$signedNumString() or template: $signedNumString()
app.config.globalProperties.$signedNumString = signedNumString;

app.mount('#sheet');
