import mitt from 'mitt';
import { createApp } from 'vue';
import Button from './components/Button.vue';
import Sheet from './components/Sheet.vue';
import { global } from './global';
import { i18nPlugin } from './i18n';
import { signedNumString } from './utils';

/* -- Event bus (replaces new Vue() instance) -- */
global.sheetEvent = mitt();

global.md = global.markdownit({
  html: true,
  linkify: true,
  typographer: true,
  breaks: true,
});

const app = createApp(Sheet);
app.use(i18nPlugin);
app.component('app-button', Button);

// Register signedNumString as a global property
// Components access via this.$signedNumString() or template: $signedNumString()
app.config.globalProperties.$signedNumString = signedNumString;

app.mount('#sheet');
