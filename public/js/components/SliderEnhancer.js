/**
 * @file SliderEnhancer.js
 * @module slider-enhancer
 * @description
 * Lekki, reużywalny komponent (`SliderEnhancer`) do dynamicznej aktualizacji
 * wartości liczbowej powiązanej z suwakiem HTML (`<input type="range">`).
 * Poprawia doświadczenie użytkownika, dając mu natychmiastową informację
 * zwrotną o wybranej wartości.
 *
 * ## Przykład Użycia (HTML)
 *
 * Aby komponent zadziałał, struktura HTML powinna wyglądać następująco:
 *
 * ```html
 * * <input type="range" id="question-count" min="10" max="40" value="20">
 *
 * * <output id="question-count-value">20</output>
 * ```
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */

class SliderEnhancer {
  /**
   * Inicjalizuje komponent, łącząc suwak z elementem wyjściowym.
   *
   * @constructs SliderEnhancer
   *
   * @param {string} sliderId - Atrybut `id` elementu suwaka (`<input type="range">`).
   * @param {string} outputId - Atrybut `id` elementu, w którym wyświetlana jest wartość (np. `<output>`).
   */
  constructor(sliderId, outputId) {
    /**
     * Element DOM suwaka.
     * @type {HTMLInputElement|null}
     */
    this.slider = document.getElementById(sliderId);

    /**
     * Element DOM do wyświetlania wartości.
     * @type {HTMLElement|null}
     */
    this.output = document.getElementById(outputId);

    if (this.slider && this.output) {
      this.init();
    } else {
      console.warn(`SliderEnhancer: Nie znaleziono elementu o ID "${sliderId}" lub "${outputId}".`);
    }
  }

  /**
   * Ustawia wartość początkową i dodaje nasłuchiwacz zdarzeń.
   *
   * Logika działania:
   * 1. Odczytuje początkową wartość z atrybutu `value` suwaka i wstawia
   *    ją do elementu `output`.
   * 2. Dodaje nasłuchiwacz na zdarzenie `input`, które jest wywoływane
   *    w czasie rzeczywistym przy każdym, nawet najmniejszym, ruchu suwaka.
   *
   * @private
   */
  init() {
    // Krok 1: Ustawienie wartości początkowej przy załadowaniu strony.
    this.output.textContent = this.slider.value;

    // Krok 2: Aktualizacja wartości podczas przesuwania suwaka.
    // Używamy zdarzenia 'input' zamiast 'change', aby aktualizacja
    // była płynna, a nie dopiero po puszczeniu myszki.
    this.slider.addEventListener('input', () => {
      this.output.textContent = this.slider.value;
    });
  }
}

/**
 * --- Punkt Startowy Aplikacji ---
 *
 * Po pełnym załadowaniu struktury DOM strony, tworzy nową instancję
 * `SliderEnhancer`, aby aktywować komponent dla suwaka wyboru liczby pytań.
 */
document.addEventListener('DOMContentLoaded', () => {
  new SliderEnhancer('question-count', 'question-count-value');
});
