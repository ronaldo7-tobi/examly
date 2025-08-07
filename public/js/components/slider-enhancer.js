/**
 * @module slider-enhancer
 * @description Dostarcza prosty, reużywalny komponent (`SliderEnhancer`) do dynamicznej
 * aktualizacji wartości liczbowej powiązanej z suwakiem HTML.
 * @version 1.0.0
 */

/**
 * @class SliderEnhancer
 * @classdesc Ten skrypt znajduje na stronie suwak oraz powiązany z nim element <output>
 * i dynamicznie aktualizuje jego zawartość w czasie rzeczywistym,
 * gdy użytkownik przesuwa suwak.
 *
 * @property {HTMLInputElement|null} slider - Element DOM suwaka.
 * @property {HTMLElement|null} output - Element DOM do wyświetlania aktualnej wartości suwaka.
 */
class SliderEnhancer {
    /**
     * @constructs SliderEnhancer
     * @description Wyszukuje suwak i element wyjściowy po ich ID, a następnie
     * inicjalizuje mechanizm synchronizacji ich wartości.
     * @param {string} sliderId - ID elementu suwaka (`<input type="range">`).
     * @param {string} outputId - ID elementu, w którym ma być wyświetlana wartość suwaka.
     */
    constructor(sliderId, outputId) {
        this.slider = document.getElementById(sliderId);
        this.output = document.getElementById(outputId);

        if (this.slider && this.output) {
            this.init();
        }
    }

    /**
     * @method init
     * @description Inicjalizuje logikę komponentu. Ustawia wartość początkową
     * przy załadowaniu strony i dodaje nasłuchiwacz zdarzenia `input`
     * do aktualizacji wartości w czasie rzeczywistym.
     * @private
     */
    init() {
        // Ustawienie wartości początkowej.
        this.output.textContent = this.slider.value;

        // Aktualizacja wartości podczas przesuwania suwaka.
        this.slider.addEventListener('input', () => {
            this.output.textContent = this.slider.value;
        });
    }
}

/**
 * @event DOMContentLoaded
 * @description Po załadowaniu strony, tworzy instancję `SliderEnhancer` dla suwaka
 * wyboru liczby pytań na stronie testu spersonalizowanego.
 */
document.addEventListener('DOMContentLoaded', () => {
    new SliderEnhancer('question-count', 'question-count-value');
});