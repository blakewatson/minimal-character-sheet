import mitt from 'mitt';
import { createApp } from 'vue';
import Sheet from './components/Sheet.vue';
import { i18nPlugin } from './i18n';
import { signedNumString } from './utils';

/* -- Event bus (replaces new Vue() instance) -- */
window.sheetEvent = mitt();

window.md = window.markdownit({
  html: true,
  linkify: true,
  typographer: true,
  breaks: true,
});

const app = createApp(Sheet);
app.use(i18nPlugin);

// Register signedNumString as a global property
// Components access via this.$signedNumString() or template: $signedNumString()
app.config.globalProperties.$signedNumString = signedNumString;

app.mount('#sheet');
