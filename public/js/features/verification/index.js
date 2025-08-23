/**
 * @file /features/verification/index.js
 * @module verification-countdown
 * @description
 * Dostarcza reużywalny komponent UI (`VerificationCountdown`) do zarządzania
 * przyciskiem z funkcją odliczania (cooldown). Komponent jest samowystarczalny
 * i odczytuje swój stan początkowy z atrybutów `data-` elementu HTML.
 *
 * ## Wymagania i Przykład Użycia (HTML)
 *
 * Komponent do działania wymaga przycisku z określonym `id`. Jego początkowy
 * stan jest kontrolowany przez atrybut `data-remaining`.
 *
 * ### Stan 1: Przycisk z aktywnym odliczaniem
 * ```html
 * <button id="resendButton" data-remaining="55">
 *  Wyślij ponownie za <span id="countdown">55</span>s
 * </button>
 * ```
 *
 * ### Stan 2: Przycisk gotowy do użycia
 * ```html
 * <button id="resendButton" data-remaining="0">
 *  Wyślij ponownie e-mail
 * </button>
 * ```
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class VerificationCountdown {
  /**
   * Wyszukuje elementy DOM i inicjalizuje komponent.
   *
   * @constructs VerificationCountdown
   * @param {string} buttonId - ID przycisku, który ma być kontrolowany.
   */
  constructor(buttonId) {
    // Krok 1: Wyszukaj kluczowe elementy DOM.
    this.button = document.getElementById(buttonId);
    if (!this.button) return;

    // Krok 2: Odczytaj początkowy czas i znajdź element licznika.
    this.remaining = parseInt(this.button.dataset.remaining || '0', 10);
    this.countdownSpan = this.button.querySelector('#countdown');

    // Krok 3: Uruchom logikę komponentu.
    this.init();
  }

  /**
   * Inicjalizuje logikę komponentu, uruchamiając odliczanie (jeśli jest
   * wymagane) i przypisując główną akcję kliknięcia.
   * @private
   */
  init() {
    // Krok 1: Jeśli z serwera przyszedł czas większy od zera, uruchom odliczanie.
    if (this.remaining > 0) {
      this.startCountdown();
    }

    // Krok 2: Zawsze przypisz główną akcję kliknięcia do przycisku.
    this.button.addEventListener('click', (e) => {
      e.preventDefault();
      // Ta akcja wykona się tylko, jeśli przycisk NIE jest zablokowany przez odliczanie.
      if (!this.button.disabled) {
        // Natychmiast zablokuj przycisk i zmień tekst dla lepszego UX.
        this.button.disabled = true;
        this.button.textContent = 'Wysyłanie...';
        // Przekieruj na adres, który uruchomi wysyłkę w kontrolerze PHP.
        window.location.href = '/examly/public/verify_email?send=true';
      }
    });
  }

  /**
   * Zarządza cyklem życia odliczania.
   * @private
   */
  startCountdown() {
    // Krok 1: Zablokuj przycisk, aby zapobiec kliknięciom podczas odliczania.
    this.button.disabled = true;

    // Krok 2: Uruchom interwał, który będzie wykonywał się co sekundę.
    const timer = setInterval(() => {
      // Krok 2a: Zmniejsz pozostały czas i zaktualizuj tekst w liczniku.
      this.remaining--;
      if (this.countdownSpan) {
        this.countdownSpan.textContent = this.remaining;
      }

      // Krok 2b: Sprawdź, czy odliczanie dobiegło końca.
      if (this.remaining <= 0) {
        // Jeśli tak, zatrzymaj interwał, odblokuj przycisk i przywróć jego pierwotny tekst.
        clearInterval(timer);
        this.button.disabled = false;
        this.button.innerHTML = 'Wyślij ponownie e-mail';
      }
    }, 1000);
  }
}

/**
 * --- Punkt Startowy Aplikacji ---
 *
 * Po pełnym załadowaniu struktury DOM, tworzy nową instancję komponentu
 * dla przycisku ponownego wysłania e-maila.
 */
document.addEventListener('DOMContentLoaded', () => {
  new VerificationCountdown('resendButton');
});