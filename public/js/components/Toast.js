/**
 * @file Toast.js
 * @module Toast
 * @description
 * Reużywalny, samowystarczalny komponent do wyświetlania powiadomień
 * typu "toast". Moduł eksportuje jedną, gotową do użycia instancję (Singleton),
 * co zapewnia spójne zarządzanie powiadomieniami w całej aplikacji.
 *
 * ## Wymagania
 *
 * Do poprawnego działania komponent wymaga obecności w pliku HTML kontenera,
 * do którego będą wstrzykiwane powiadomienia:
 * ```html
 * <div id="toast-container"></div>
 * ```
 *
 * ## Przykład Użycia (w innym module JS)
 *
 * ```javascript
 * import Toast from './Toast.js';
 *
 * // Wyświetlenie powiadomienia o sukcesie
 * Toast.show('Operacja zakończona pomyślnie!', 'success');
 *
 * // Wyświetlenie błędu, który zniknie po 10 sekundach
 * Toast.show('Wystąpił błąd serwera.', 'error', 10000);
 * ```
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */

class Toast {
  /**
   * @constructs Toast
   * @description
   * Inicjalizuje domyślne właściwości komponentu.
   * Konstruktor jest wywoływany tylko raz na dole tego pliku.
   */
  constructor() {
    /**
     * Przechowuje referencję do kontenera powiadomień w DOM.
     * Jest inicjalizowane przy pierwszym wywołaniu `show()`.
     * @private
     * @type {HTMLElement|null}
     */
    this.container = null;

    /**
     * Maksymalna liczba powiadomień, które mogą być widoczne jednocześnie.
     * @private
     * @type {number}
     */
    this.maxToasts = 3;
  }

  /**
   * Tworzy i wyświetla nowe powiadomienie typu "toast".
   *
   * Logika działania:
   * 1. Znajduje w DOM kontener `#toast-container`.
   * 2. Sprawdza, czy liczba istniejących powiadomień nie przekracza limitu.
   *    Jeśli tak, usuwa najstarsze powiadomienia, aby zrobić miejsce.
   * 3. Tworzy dynamicznie nowy element `<div>` dla powiadomienia.
   * 4. Wypełnia go treścią (ikona, wiadomość, przycisk zamykania).
   * 5. Dodaje nasłuchiwacz na przycisk zamykania.
   * 6. Ustawia `setTimeout`, aby powiadomienie automatycznie zniknęło.
   * 7. Dodaje gotowe powiadomienie do kontenera w DOM.
   *
   * @param {string} message - Treść wiadomości do wyświetlenia.
   * @param {string} [type='info'] - Typ powiadomienia ('info', 'success', 'error'),
   * wpływa na styl i ikonę.
   * @param {number} [duration=5000] - Czas wyświetlania w milisekundach.
   */
  show(message, type = 'info', duration = 5000) {
    // Krok 1: Znajdź kontener (lub potwierdź, że już go mamy).
    if (!this.container) {
      this.container = document.getElementById('toast-container');
    }

    if (!this.container) {
      console.error('Błąd krytyczny: Nie znaleziono elementu #toast-container w DOM.');
      return;
    }

    // Krok 2: Zarządzaj limitem wyświetlanych powiadomień.
    // Pętla `while` zapewnia usunięcie nadmiarowych powiadomień,
    // robiąc miejsce dla nowego.
    while (this.container.children.length >= this.maxToasts) {
      // Usuwamy najstarsze powiadomienie (pierwsze dziecko kontenera).
      this.container.removeChild(this.container.firstChild);
    }

    // Krok 3: Stwórz nowy element `toast` i wypełnij go treścią.
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;

    const icons = {
      info: 'fas fa-info-circle',
      success: 'fas fa-check-circle',
      error: 'fas fa-exclamation-circle',
    };

    toast.innerHTML = `
            <i class="toast__icon ${icons[type] || icons.info}"></i>
            <p class="toast__message">${message}</p>
            <button type="button" class="toast__close">&times;</button>
        `;

    // Krok 4: Dodaj powiadomienie do kontenera.
    this.container.appendChild(toast);

    // Krok 5: Dodaj logikę zamykania.
    // a) Po kliknięciu przycisku "x".
    const closeButton = toast.querySelector('.toast__close');
    closeButton.addEventListener('click', () => toast.remove());

    // b) Automatycznie po upływie określonego czasu.
    setTimeout(() => {
      // Sprawdzenie `toast.parentElement` to zabezpieczenie na wypadek,
      // gdyby użytkownik zamknął powiadomienie ręcznie tuż przed
      // wykonaniem się `setTimeout`. Zapobiega to błędowi.
      if (toast.parentElement) {
        toast.remove();
      }
    }, duration);
  }
}

/**
 * --- Eksport Instancji (Singleton) ---
 *
 * Eksportujemy jedną, gotową do użycia instancję klasy `Toast`.
 * Dzięki temu cała aplikacja korzysta z tego samego "managera" powiadomień,
 * co zapewnia spójne działanie i stan (np. poprawną obsługę limitu).
 */
export default new Toast();
