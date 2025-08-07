/**
 * @module verification-countdown
 * @description Dostarcza reużywalny komponent UI (`VerificationCountdown`) do zarządzania
 * przyciskiem z funkcją odliczania (cooldown). Idealny do akcji, które
 * użytkownik może powtórzyć dopiero po upływie określonego czasu,
 * jak np. ponowne wysłanie e-maila weryfikacyjnego.
 */
class VerificationCountdown {
    /**
     * @class VerificationCountdown
     * @classdesc Zarządza logiką przycisku z odliczaniem, blokując go
     * na określony czas i odblokowując po upływie tego czasu.
     *
     * @property {HTMLButtonElement|null} button - Element przycisku, którym zarządza klasa.
     * @property {number} remaining - Czas w sekundach pozostały do odblokowania przycisku.
     * @property {HTMLElement|null} countdownSpan - Element <span> wewnątrz przycisku, wyświetlający licznik.
     */

    /**
     * @constructs VerificationCountdown
     * @description Wyszukuje elementy DOM na podstawie podanego ID, odczytuje
     * początkowy czas z atrybutu `data-remaining` i inicjalizuje komponent.
     * @param {string} buttonId - ID przycisku, który ma być kontrolowany.
     */
    constructor(buttonId) {
        this.button = document.getElementById(buttonId);
        if (!this.button) return;

        this.remaining = parseInt(this.button.dataset.remaining || '0', 10);
        this.countdownSpan = this.button.querySelector('#countdown');

        this.init();
    }

    /**
     * @method init
     * @description Inicjalizuje logikę komponentu. Uruchamia odliczanie, jeśli jest to
     * wymagane, oraz na stałe przypisuje obsługę zdarzenia 'click' do przycisku.
     * @private
     */
    init() {
        if (this.remaining > 0) {
            this.startCountdown();
        }

        // Logika kliknięcia jest niezależna od odliczania i powinna być zawsze aktywna.
        this.button.addEventListener('click', (e) => {
            e.preventDefault();
            if (!this.button.disabled) {
                this.button.disabled = true;
                this.button.textContent = 'Wysyłanie...';
                // Przekierowuje na adres, który uruchomi wysyłkę w kontrolerze.
                window.location.href = '/examly/public/verify_email?send=true';
            }
        });
    }

    /**
     * @method startCountdown
     * @description Zarządza cyklem życia odliczania. Blokuje przycisk, uruchamia
     * interwał, który co sekundę aktualizuje licznik, a po zakończeniu
     * odblokowuje przycisk i przywraca jego pierwotną treść.
     * @private
     */
    startCountdown() {
        this.button.disabled = true;

        const timer = setInterval(() => {
            this.remaining--;
            if (this.countdownSpan) {
                this.countdownSpan.textContent = this.remaining;
            }

            if (this.remaining <= 0) {
                clearInterval(timer);
                this.button.disabled = false;
                // Zamieniamy całą zawartość przycisku, aby usunąć licznik.
                this.button.innerHTML = 'Wyślij ponownie e-mail';
            }
        }, 1000);
    }
}

/**
 * @event DOMContentLoaded
 * @description Punkt wejściowy skryptu. Po załadowaniu struktury DOM,
 * tworzy instancję `VerificationCountdown` dla przycisku ponownego wysłania,
 * aby aktywować logikę odliczania.
 */
document.addEventListener('DOMContentLoaded', () => {
    new VerificationCountdown('resendButton');
});