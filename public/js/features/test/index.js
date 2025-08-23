/**
 * @file /features/test/index.js
 * @module test-launcher
 * @description
 * Moduł odpowiedzialny za inicjalizację strony pełnego testu egzaminacyjnego.
 * Działa jako prosty "launcher" lub "bootstrapper". Jego jedynym zadaniem jest
 * pobranie z API odpowiedniego zestawu 40 pytań, a następnie zainicjowanie
 * i przekazanie kontroli do generycznego modułu `TestRunner`.
 *
 * ## Wzorce i Architektura
 *
 * - Launcher / Bootstrapper: Prosta klasa, której celem jest przygotowanie
 *   danych i uruchomienie bardziej złożonego komponentu (`TestRunner`).
 * - Separation of Concerns: Logika pobierania danych dla pełnego testu
 *   jest oddzielona od logiki samego przebiegu testu.
 *
 * ## Wymagania
 *
 * - Struktura HTML: Skrypt oczekuje istnienia w DOM kontenera `#test-container`
 *  (z atrybutem `data-exam-code`) oraz elementów `#loading-screen` i `#test-view`.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */

import api from '../../modules/ApiClient.js';
import { TestRunner } from '../../modules/TestRunner.js';

class Test {
  /**
   * Wyszukuje kluczowe elementy DOM i uruchamia proces inicjalizacji testu.
   * @constructs Test
   */
  constructor() {
    // Krok 1: Wyszukaj główne kontenery UI.
    this.testContainer = document.getElementById('test-container');
    if (!this.testContainer) {
      console.error('Błąd krytyczny: Nie znaleziono kontenera #test-container.');
      return;
    }

    this.loadingScreen = document.getElementById('loading-screen');
    this.testView = document.getElementById('test-view');

    // Krok 2: Odczytaj kod egzaminu z atrybutu data.
    this.examCode = this.testContainer.dataset.examCode;

    // Krok 3: Uruchom asynchroniczny proces pobierania danych i inicjalizacji.
    this.init();
  }

  /**
   * Główna metoda inicjalizująca, która pobiera dane i uruchamia TestRunner.
   * @private
   * @async
   */
  async init() {
    // Krok 1: Walidacja - sprawdź, czy kod egzaminu jest dostępny.
    if (!this.examCode) {
      this.loadingScreen.innerHTML = '<h2>Błąd krytyczny: Brak kodu egzaminu!</h2>';
      return;
    }

    // Krok 2: Wywołaj API, aby pobrać dane pełnego testu.
    const response = await api.fetchFullTest(this.examCode);

    // Krok 3: Przetwórz odpowiedź z API.
    if (response.success && response.data.questions) {
      // Scenariusz A: Sukces - ukryj loader i pokaż widok testu.
      this.loadingScreen.classList.add('hidden');
      this.testView.classList.remove('hidden');

      // Krok 4: Stwórz instancję `TestRunner`, wstrzykując mu wszystkie zależności.
      const testRunner = new TestRunner({
        testView: this.testView,
        questionsWrapper: document.getElementById('questions-wrapper'),
        resultsScreen: document.getElementById('results-screen'),
        finishBtn: document.getElementById('finish-test-btn'),
        resultsDetailsContainer: document.getElementById('results-details'),
        isFullExam: true, // Ten tryb to zawsze pełny egzamin.
      });

      // Krok 5: Uruchom `TestRunner` z pobranymi pytaniami.
      testRunner.run(response.data.questions);
    } else {
      // Scenariusz B: Błąd - wyświetl komunikat o błędzie w miejscu loadera.
      const errorMessage = response.error || 'Nie udało się załadować pytań.';
      this.loadingScreen.innerHTML = `<h2>Wystąpił błąd</h2><p>${errorMessage}</p>`;
    }
  }
}

/**
 * --- Punkt Startowy Aplikacji ---
 *
 * Po pełnym załadowaniu struktury DOM, tworzy nową instancję klasy Test,
 * aby aktywować logikę strony.
 */
document.addEventListener('DOMContentLoaded', () => {
  new Test();
});