/**
 * @file TopicFormEnhancer.js
 * @module topicFormEnhancer
 * @description
 * Skrypt, który wzbogaca formularz wyboru tematów o zaawansowaną interaktywność.
 * Implementuje logikę "zaznacz/odznacz wszystko", synchronizuje stan checkboxów
 * i dynamicznie zarządza dostępnością opcji premium w zależności od stanu
 * zalogowania użytkownika i jego wyborów.
 *
 * ## Wymagania i Przykład Użycia (HTML)
 *
 * Komponent do działania wymaga specyficznej struktury formularza.
 * Poniżej znajduje się minimalny, wymagany szkielet HTML:
 *
 * ```html
 * <form id="topic-form">
 *   <input type="checkbox" id="select-all-inf03">
 *  <label for="select-all-inf03">Zaznacz wszystko</label>
 *
 *  <input type="checkbox" class="topic-checkbox" name="subject[]" value="1">
 *  <input type="checkbox" class="topic-checkbox" name="subject[]" value="2">
 *
 * * <label class="topic-selector__label"> * <input type="checkbox" class="premium-checkbox" name="premium_option" value="toDiscover">
 *  </label>
 * </form>
 * ```
 * ## Zależność od Stanu Globalnego
 *
 * Skrypt odczytuje stan zalogowania użytkownika z globalnego obiektu
 * `window.examlyAppState.isUserLoggedIn`. Obiekt ten musi być zdefiniowany
 * w kodzie HTML strony, np. w tagu `<script>`, przed załadowaniem tego pliku.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */

class TopicFormEnhancer {
  /**
   * Wyszukuje wszystkie niezbędne elementy w DOM i inicjalizuje logikę.
   * @constructs TopicFormEnhancer
   * @param {string} formId - Atrybut `id` elementu formularza, który ma zostać wzbogacony.
   */
  constructor(formId) {
    /**
     * Główny element DOM formularza.
     * @private
     * @type {HTMLFormElement|null}
     */
    this.form = document.getElementById(formId);
    if (!this.form) {
      console.warn(`TopicFormEnhancer: Nie znaleziono formularza o ID "${formId}".`);
      return;
    }

    /** @private @type {HTMLInputElement|null} */
    this.selectAllCheckbox = this.form.querySelector('#select-all-inf03');
    /** @private @type {NodeListOf<HTMLInputElement>} */
    this.topicCheckboxes = this.form.querySelectorAll('.topic-checkbox');
    /** @private @type {NodeListOf<HTMLInputElement>} */
    this.premiumCheckboxes = this.form.querySelectorAll('.premium-checkbox');

    /**
     * Lokalna kopia stanu zalogowania użytkownika.
     * @private
     * @type {boolean}
     */
    this.isUserLoggedIn = window.examlyAppState?.isUserLoggedIn || false;

    this.bindEvents();
    this.updateFormState();
  }

  /**
   * Wiąże wszystkie potrzebne zdarzenia z elementami formularza.
   * Centralne miejsce do zarządzania interakcjami użytkownika.
   * @private
   */
  bindEvents() {
    if (this.selectAllCheckbox) {
      this.selectAllCheckbox.addEventListener('change', () => this.handleSelectAll());
    }

    this.topicCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener('change', () => this.handleSingleTopicChange());
    });

    this.premiumCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener('change', (e) => this.handlePremiumChange(e.target));
    });
  }

  /**
   * Obsługuje kliknięcie w checkbox "Zaznacz wszystko".
   * Zaznacza lub odznacza wszystkie podrzędne checkboxy tematyczne.
   * @private
   */
  handleSelectAll() {
    this.topicCheckboxes.forEach((cb) => {
      cb.checked = this.selectAllCheckbox.checked;
    });
    this.updatePremiumOptionsState();
  }

  /**
   * Obsługuje kliknięcie w pojedynczy checkbox tematyczny.
   * Sprawdza, czy wszystkie tematy są zaznaczone, i na tej podstawie
   * aktualizuje stan checkboxa "Zaznacz wszystko" (synchronizacja).
   * @private
   */
  handleSingleTopicChange() {
    const allTopicsChecked = [...this.topicCheckboxes].every((cb) => cb.checked);
    if (this.selectAllCheckbox) {
      this.selectAllCheckbox.checked = allTopicsChecked;
    }
    this.updatePremiumOptionsState();
  }

  /**
   * Zapewnia działanie opcji premium w trybie "tylko jeden wybrany".
   * Działa podobnie do przycisków typu radio, ale na checkboxach.
   * @private
   * @param {HTMLInputElement} changedCheckbox - Checkbox, który wywołał zdarzenie.
   */
  handlePremiumChange(changedCheckbox) {
    if (changedCheckbox.checked) {
      this.premiumCheckboxes.forEach((cb) => {
        if (cb !== changedCheckbox) {
          cb.checked = false;
        }
      });
    }
  }

  /**
   * Ustawia poprawny stan całego formularza przy pierwszym załadowaniu skryptu.
   * @private
   */
  updateFormState() {
    this.handleSingleTopicChange(); // Synchronizuje "Zaznacz wszystko"
    // `updatePremiumOptionsState` jest już wołane w powyższej metodzie.
  }

  /**
   * Centralna metoda zarządzająca stanem (aktywny/nieaktywny) opcji premium.
   *
   * Logika działania:
   * 1. Sprawdza, czy spełnione są warunki do aktywacji opcji premium:
   *    - Użytkownik musi być zalogowany.
   *    - Co najmniej jeden temat musi być zaznaczony.
   * 2. Iteruje po wszystkich checkboxach premium.
   * 3. Dla każdego z nich, włącza lub wyłącza go (`disabled`).
   * 4. Aktualizuje również interfejs (tooltip, kursor, przezroczystość),
   *    aby dać użytkownikowi jasną informację zwrotną.
   * 5. Jeśli opcje stają się nieaktywne, odznacza je.
   * @private
   */
  updatePremiumOptionsState() {
    const anyTopicSelected = [...this.topicCheckboxes].some((cb) => cb.checked);
    const isEnabled = this.isUserLoggedIn && anyTopicSelected;

    this.premiumCheckboxes.forEach((checkbox) => {
      const label = checkbox.closest('.topic-selector__label');
      checkbox.disabled = !isEnabled;

      if (label) {
        if (this.isUserLoggedIn) {
          label.title = isEnabled ? 'Wybierz, aby filtrować pytania' : 'Najpierw wybierz kategorię tematyczną.';
        } else {
          label.title = 'Ta opcja jest dostępna tylko dla zalogowanych użytkowników.';
        }
        label.style.cursor = isEnabled ? 'pointer' : 'not-allowed';
        label.style.opacity = isEnabled ? '1' : '0.6';
      }

      // Jeśli opcja staje się nieaktywna, upewnij się, że jest odznaczona.
      if (!isEnabled) {
        checkbox.checked = false;
      }
    });
  }
}

/**
 * --- Punkt Startowy Aplikacji ---
 *
 * Po pełnym załadowaniu struktury DOM, tworzy nową instancję `TopicFormEnhancer`,
 * aby aktywować całą logikę interaktywną formularza.
 */
document.addEventListener('DOMContentLoaded', () => {
  new TopicFormEnhancer('topic-form');
});
