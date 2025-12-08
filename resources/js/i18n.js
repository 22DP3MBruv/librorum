import { createI18n } from 'vue-i18n';
import en from './locales/en';
import lv from './locales/lv';

// Get saved locale from localStorage or default to 'lv'
const savedLocale = localStorage.getItem('locale') || 'lv';

const i18n = createI18n({
  legacy: false, // Use Composition API mode
  locale: savedLocale,
  fallbackLocale: 'lv',
  messages: {
    en,
    lv
  }
});

export default i18n;
