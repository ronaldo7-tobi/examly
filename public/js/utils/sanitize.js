/**
 * @module Sanitize
 * @description Moduł dostarcza funkcje pomocnicze do zabezpieczania danych przed wstrzyknięciem do HTML.
 */

/**
 * Zabezpiecza ciąg znaków przed interpretacją jako HTML (zapobieganie XSS).
 * @param {string} str - Ciąg znaków do "uescape'owania".
 * @returns {string} Bezpieczny do wstawienia w HTML ciąg znaków.
 */
export function escapeHTML(str) {
    if (typeof str !== 'string') return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}