import en from './languages/en.json';
import de from './languages/de.json';

const messages = { en, de };

/**
 * Get the current locale from localStorage, defaulting to 'en'
 */
export function getLocale() {
  return localStorage.getItem('locale') || 'en';
}

/**
 * Set the current locale in localStorage
 */
export function setLocale(locale) {
  localStorage.setItem('locale', locale);
}

/**
 * Translate a key to the current locale.
 * Falls back to English, then to the key itself if no translation exists.
 *
 * @param {string} key - The translation key (English text)
 * @returns {string} - The translated string
 */
export function t(key) {
  const locale = getLocale();
  return messages[locale]?.[key] || messages.en?.[key] || key;
}

/**
 * Vue plugin for i18n
 * Adds $t, $getLocale, and $setLocale to all Vue components
 */
export const i18nPlugin = {
  install(app) {
    app.config.globalProperties.$t = t;
    app.config.globalProperties.$getLocale = getLocale;
    app.config.globalProperties.$setLocale = setLocale;
  }
};
