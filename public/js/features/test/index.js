/**
 * @module test
 * @description Moduł odpowiedzialny za inicjalizację strony pełnego testu egzaminacyjnego.
 * Jego głównym zadaniem jest pobranie odpowiedniego zestawu 40 pytań z API,
 * a następnie przekazanie kontroli nad przebiegiem testu do generycznego modułu `TestRunner`.
 */

import api from '../../modules/ApiClient.js'; 
import { TestRunner } from '../../modules/TestRunner.js';

/**
 * @class Test
 * @classdesc Inicjalizuje środowisko dla pełnego testu egzaminacyjnego.
 * Działa jako "launcher", który przygotowuje dane i przekazuje je do `TestRunnera`.
 * @property {HTMLElement} testContainer - Główny kontener komponentu testu.
 * @property {HTMLElement} loadingScreen - Ekran ładowania wyświetlany podczas pobierania pytań.
 * @property {HTMLElement} testView - Główny widok aktywnego testu z pytaniami.
 * @property {string} examCode - Kod egzaminu (np. "INF.03") pobrany z atrybutu data-.
 */
class Test {
    /**
     * @constructs Test
     * @description Wyszukuje kluczowe elementy DOM i uruchamia asynchroniczny proces inicjalizacji testu.
     */
    constructor() {
        this.testContainer = document.getElementById('test-container');
        if (!this.testContainer) {
            console.error("Błąd krytyczny: Nie znaleziono kontenera #test-container.");
            return;
        }

        this.loadingScreen = document.getElementById('loading-screen');
        this.testView = document.getElementById('test-view');
        this.examCode = this.testContainer.dataset.examCode;

        this.init();
    }

    /**
     * @method init
     * @description Główna metoda inicjalizująca. Weryfikuje kod egzaminu,
     * pobiera dane testu z API, a po ich otrzymaniu tworzy instancję
     * `TestRunner` i deleguje do niej dalsze wykonanie testu.
     * @private
     * @async
     */
    async init() {
        if (!this.examCode) {
            this.loadingScreen.innerHTML = `<h2>Błąd krytyczny: Brak kodu egzaminu!</h2>`;
            return;
        }

        // Krok 1: Pobierz dane pełnego testu z API.
        const response = await api.fetchFullTest(this.examCode);

        // Krok 2: Przetwórz odpowiedź.
        if (response.success && response.data.questions) {
            this.loadingScreen.classList.add('hidden');
            this.testView.classList.remove('hidden');

            // Krok 3: Stwórz instancję `TestRunner`, przekazując mu wszystkie zależności.
            const testRunner = new TestRunner({
                testView: this.testView,
                questionsWrapper: document.getElementById('questions-wrapper'),
                resultsScreen: document.getElementById('results-screen'),
                finishBtn: document.getElementById('finish-test-btn'),
                resultsDetailsContainer: document.getElementById('results-details'),
                isFullExam: true // Ten tryb to zawsze pełny egzamin.
            });

            // Krok 4: Uruchom `TestRunner` z pobranymi pytaniami.
            testRunner.run(response.data.questions);
        } else {
            this.loadingScreen.innerHTML = `<h2>Wystąpił błąd</h2><p>${response.error || 'Nie udało się załadować pytań.'}</p>`;
        }
    }
}

/**
 * @event DOMContentLoaded
 * @description Punkt wejściowy skryptu. Po załadowaniu struktury DOM,
 * tworzy instancję klasy Test, aby aktywować logikę strony.
 */
document.addEventListener('DOMContentLoaded', () => {
    new Test();
});