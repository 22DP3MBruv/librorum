import i18n from '../i18n.js';

/**
 * Get the appropriate message based on current locale
 * @param {Object} responseData - Response data from API (error or success)
 * @returns {string} - Localized message
 */
export function getLocalizedMessage(responseData) {
  const currentLocale = i18n.global.locale.value;
  
  if (currentLocale === 'lv' && responseData.message_lv) {
    return responseData.message_lv;
  }
  
  return responseData.message || 'An error occurred';
}
