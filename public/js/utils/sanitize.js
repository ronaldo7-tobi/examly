/**
 * @file /utils/sanitize.js
 * @module Sanitize
 * @description
 * Moduł dostarcza zbiór funkcji pomocniczych do zabezpieczania (sanityzacji)
 * danych przed ich renderowaniem w interfejsie użytkownika. Jego głównym celem
 * jest zapobieganie atakom typu Cross-Site Scripting (XSS).
 *
 * ## Dlaczego to Działa? (Technika)
 *
 * Funkcja `escapeHTML` wykorzystuje wbudowany w przeglądarkę, bezpieczny parser
 * DOM. Kiedy przypisujemy ciąg znaków do właściwości `textContent` elementu,
 * przeglądarka automatycznie traktuje go jako czysty tekst i zamienia
 * wszystkie znaki specjalne HTML (np. `<` na `&lt;`). Następnie odczytując
 * właściwość `innerHTML`, otrzymujemy gotowy, bezpieczny do użycia ciąg znaków
 * z poprawnie "uescape'owanymi" encjami HTML.
 *
 * ## Przykład Użycia
 *
 * ```javascript
 * import { escapeHTML } from './sanitize.js';
 *
 * const userInput = '<script>alert("XSS Attack!")</script>';
 * const safeHtml = escapeHTML(userInput);
 *
 * console.log(safeHtml);
 * // Wynik: "&lt;script&gt;alert(&quot;XSS Attack!&quot;)&lt;/script&gt;"
 *
 * // Teraz można bezpiecznie wstawić `safeHtml` do elementu
 * const container = document.getElementById('container');
 * container.innerHTML = safeHtml; // Nie wykona skryptu, tylko go wyświetli
 * ```
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */

/**
 * Zabezpiecza ciąg znaków przed interpretacją jako HTML, neutralizując
 * potencjalne ataki XSS.
 *
 * @param {string} str - Ciąg znaków do zabezpieczenia.
 * @returns {string} Bezpieczny do wstawienia w HTML ciąg znaków.
 */
export function escapeHTML(str) {
  // Krok 1: Zabezpieczenie na wypadek, gdyby przekazana wartość nie była stringiem.
  if (typeof str !== 'string') return '';

  // Krok 2: Stwórz tymczasowy, niewidoczny element `div` w pamięci.
  // Nie jest on dodawany do faktycznego drzewa DOM, więc operacja jest bardzo szybka.
  const div = document.createElement('div');

  // Krok 3: Wstaw niebezpieczny ciąg jako CZYSTY TEKST do elementu.
  // To jest kluczowy moment - przeglądarka sama zamienia znaki specjalne
  // na ich bezpieczne odpowiedniki (encje HTML), np. '<' -> '&lt;'.
  div.textContent = str;

  // Krok 4: Odczytaj wewnętrzny HTML elementu, który zawiera już
  // bezpieczną, "uescape'owaną" wersję oryginalnego ciągu.
  return div.innerHTML;
}