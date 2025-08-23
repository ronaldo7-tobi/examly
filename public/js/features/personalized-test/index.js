/**
 * @file /features/personalized-test/index.js
 * @module personalized-test
 * @description
 * Ten moduł zarządza interaktywnym, krokowym procesem konfiguracji
 * i uruchamiania spersonalizowanego testu. Działa jako Kontroler, który
 * prowadzi użytkownika przez kolejne etapy, a na końcu inicjalizuje
 * i przekazuje kontrolę do głównego silnika testowego (`TestRunner`).
 *
 * ## Wzorce i Architektura
 *
 * - Kontroler / Bootstrapper: Zarządza logiką kreatora konfiguracji,
 * a następnie tworzy i uruchamia (`bootstrap`) instancję `TestRunner`,
 * wstrzykując do niej wszystkie potrzebne zależności.
 *
 * ## Wymagania
 *
 * - Struktura HTML: Skrypt oczekuje istnienia kontenera strony
 * (`#personalized-test-page`), formularza (`#topic-form`) oraz
 * poszczególnych kroków kreatora (`[data-step]`).
 *
 * @version 2.2.0
 * @author Tobiasz Szerszeń
 */

import api from '../../modules/ApiClient.js';
import { TestRunner } from '../../modules/TestRunner.js';
import Toast from '../../components/Toast.js';

class PersonalizedTest {
  /**
   * Inicjalizuje kreator testu spersonalizowanego.
   * @constructs PersonalizedTest
   */
  constructor() {
    // Krok 1: Wyszukaj kluczowe elementy DOM.
    this.pageContainer = document.getElementById('personalized-test-page');
    if (!this.pageContainer) return;

    this.form = document.getElementById('topic-form');
    this.quizContainer = document.getElementById('quiz-container');
    this.configurator = document.querySelector('.test-configurator');
    this.steps = this.configurator.querySelectorAll('.config-step');

    // Krok 2: Pobierz dane konfiguracyjne i ustaw stan początkowy.
    this.examCode = this.pageContainer.dataset.examCode;
    this.currentStep = 1;

    // Krok 3: Uruchom logikę.
    this.init();
  }

  /**
   * Inicjalizuje nasłuchiwacze zdarzeń dla kreatora.
   * @private
   */
  init() {
    if (this.form) {
      this.form.addEventListener('submit', this.startTest.bind(this));
      this.configurator.addEventListener('click', this.handleNavigation.bind(this));
    }
  }

  /**
   * Obsługuje nawigację "Dalej" / "Wstecz" między krokami kreatora.
   * @private
   * @param {Event} event - Obiekt zdarzenia `click`.
   */
  handleNavigation(event) {
    // Krok 1: Sprawdź, czy kliknięto w przycisk nawigacyjny.
    const action = event.target.dataset.action;
    if (!action) return;

    // Krok 2: Wykonaj odpowiednią akcję.
    if (action === 'next') {
      // Przy przejściu do przodu, waliduj bieżący krok.
      if (this.validateStep(this.currentStep)) {
        this.changeStep(this.currentStep + 1);
      }
    } else if (action === 'prev') {
      this.changeStep(this.currentStep - 1);
    }
  }

  /**
   * Waliduje dane w bieżącym kroku przed przejściem do następnego.
   * @private
   * @param {number} step - Numer kroku do walidacji.
   * @returns {boolean} - `true`, jeśli walidacja przeszła pomyślnie.
   */
  validateStep(step) {
    if (step === 1) {
      // Walidacja kroku 1: Sprawdź, czy wybrano co najmniej jeden temat.
      const selectedSubjects = this.form.querySelectorAll('input[name="subject[]"]:checked');
      if (selectedSubjects.length === 0) {
        Toast.show('Wybierz przynajmniej jedną kategorię tematyczną.', 'info');
        return false;
      }
    }
    return true;
  }

  /**
   * Zmienia widoczny krok w formularzu konfiguracji.
   * @private
   * @param {number} targetStep - Numer kroku do wyświetlenia.
   */
  changeStep(targetStep) {
    // Krok 1: Znajdź element bieżącego i docelowego kroku.
    const currentStepElement = this.configurator.querySelector(`[data-step="${this.currentStep}"]`);
    const targetStepElement = this.configurator.querySelector(`[data-step="${targetStep}"]`);
    if (!targetStepElement) return;

    // Krok 2: Ukryj bieżący krok.
    if (currentStepElement) {
      currentStepElement.style.display = 'none';
    }

    // Krok 3: Pokaż krok docelowy i zaktualizuj stan.
    targetStepElement.style.display = 'block';
    this.currentStep = targetStep;
  }

  /**
   * Przywraca widok konfiguratora do stanu początkowego (np. po błędzie API).
   * @private
   */
  resetToConfigurator() {
    this.configurator.classList.remove('hidden');
    this.quizContainer.innerHTML = '';
    // Usuwamy klasę, która utrzymywała minimalną wysokość kontenera.
    this.quizContainer.classList.remove('test-container--active');
  }

  /**
   * Uruchamiana po kliknięciu "Rozpocznij Test!". Główna metoda orkiestrująca.
   * @private
   * @async
   * @param {Event} event - Obiekt zdarzenia `submit`.
   */
  async startTest(event) {
    // Krok 1: Zatrzymaj domyślną akcję formularza.
    event.preventDefault();

    // Krok 2: Zbierz wszystkie dane konfiguracyjne z formularza.
    const selectedSubjects = [...this.form.querySelectorAll('input[name="subject[]"]:checked')].map((cb) => cb.value);
    const premiumOption = this.form.querySelector('input[name="premium_option"]:checked')?.value || null;
    const questionCount = this.form.querySelector('#question-count').value;

    // Krok 3: Zastosuj regułę biznesową - czy ten test jest traktowany jak pełny egzamin?
    const areAllTopicsSelected = [...this.form.querySelectorAll('.topic-checkbox')].every((cb) => cb.checked);
    const isConsideredFullExam = areAllTopicsSelected && parseInt(questionCount, 10) === 40 && !premiumOption;

    // Krok 4: Przygotuj interfejs do rozpoczęcia testu.
    this.configurator.classList.add('hidden');
    // Nadaj kontenerowi klasę, aby zapobiec "skokowi" stopki podczas ładowania.
    this.quizContainer.classList.add('test-container--active');
    // Wstrzyknij cały szkielet HTML dla widoku testu i wyników.
    this.quizContainer.innerHTML = `
      <div class="test-container">
        <div id="loading-screen" class="test-loading hidden">
          <h2>Trwa przygotowywanie Twojego testu...</h2><div class="spinner"></div>
        </div>
        <div id="test-view" class="hidden">
          <header class="test-header">
            <h1 class="test-header__title">Test Spersonalizowany - <span class="text-gradient">INF.03</span></h1>
            <div class="test-header__meta"><div id="timer">00:00</div></div>
          </header>
          <div id="questions-wrapper" class="questions-wrapper"></div>
          <footer class="test-footer">
            <button id="finish-test-btn" class="btn btn--primary">Zakończ i sprawdź test</button>
          </footer>
        </div>
        <div id="results-screen" class="results-container hidden">
          <h1 class="results-container__title">Wyniki Testu</h1>
          <div id="score-summary" class="score-summary"></div>
          <div id="results-details" class="results-details"></div>
          <div class="results-container__actions">
            <a href="/examly/public/" class="btn btn--primary">Wróć na stronę główną</a>
          </div>
        </div>
      </div>`;

    // Krok 5: Uruchom opóźniony wskaźnik ładowania.
    const loadingScreen = document.getElementById('loading-screen');
    const testView = document.getElementById('test-view');
    let spinnerTimeout = setTimeout(() => {
      loadingScreen.classList.remove('hidden');
    }, 400);

    // Krok 6: Wywołaj API, aby pobrać pytania.
    const response = await api.fetchPersonalizedTest(this.examCode, selectedSubjects, premiumOption, questionCount);

    // Krok 7: Zawsze anuluj timer spinnera po otrzymaniu odpowiedzi.
    clearTimeout(spinnerTimeout);

    // Krok 8: Przetwórz odpowiedź z API.
    if (response.success && response.data.questions?.length > 0) {
      // Scenariusz A: Sukces - przygotuj i uruchom TestRunner.
      loadingScreen.classList.add('hidden');
      testView.classList.remove('hidden');

      const testRunner = new TestRunner({
        testView: testView,
        questionsWrapper: document.getElementById('questions-wrapper'),
        resultsScreen: document.getElementById('results-screen'),
        finishBtn: document.getElementById('finish-test-btn'),
        resultsDetailsContainer: document.getElementById('results-details'),
        isFullExam: isConsideredFullExam,
      });
      testRunner.run(response.data.questions);
    } else {
      // Scenariusz B: Błąd lub brak pytań - pokaż wiadomość i zresetuj widok.
      if (response.data?.status === 'no_questions_left') {
        Toast.show(response.data.message, 'info');
      } else {
        Toast.show(response.error || 'Nie udało się załadować pytań.', 'error');
      }
      this.resetToConfigurator();
    }
  }
}

/**
 * --- Punkt Startowy Aplikacji ---
 */
document.addEventListener('DOMContentLoaded', () => {
  new PersonalizedTest();
});