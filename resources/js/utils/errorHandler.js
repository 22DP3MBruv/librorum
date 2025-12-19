import i18n from '../i18n.js';

/**
 * Get the appropriate error message based on current locale
 * @param {Object} errorData - Error response from API
 * @returns {string} - Localized error message
 */
export function getLocalizedErrorMessage(errorData) {
  const currentLocale = i18n.global.locale.value;
  
  if (currentLocale === 'lv' && errorData.message_lv) {
    return errorData.message_lv;
  }
  
  return errorData.message || 'An error occurred';
}
