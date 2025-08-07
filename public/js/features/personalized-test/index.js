/**
 * @module personalized-test
 * @version 1.1.0
 * @description
 * Ten moduł zarządza procesem generowania i uruchamiania spersonalizowanego
 * testu. Jego głównym zadaniem jest:
 * 1. Obsługa formularza konfiguracji testu (wybór tematów, liczby pytań).
 * 2. Komunikacja z API w celu pobrania zdefiniowanego zestawu pytań.
 * 3. Dynamiczne przygotowanie interfejsu testu.
 * 4. Przekazanie kontroli do generycznej klasy `TestRunner`, która zarządza przebiegiem testu.
 */

import api from '../../modules/api.js';
import { TestRunner } from '../../modules/TestRunner.js';

/**
 * @class PersonalizedTest
 * @classdesc Główna klasa orkiestrująca działanie strony spersonalizowanego testu.
 * Działa jako "konfigurator", zbierając ustawienia od użytkownika, a następnie
 * inicjalizuje i przekazuje kontrolę do `TestRunner`.
 *
 * @property {HTMLElement} pageContainer - Główny kontener strony, przechowujący m.in. atrybut data-exam-code.
 * @property {HTMLFormElement} form - Formularz HTML służący do konfiguracji testu.
 * @property {HTMLElement} quizContainer - Kontener, w którym dynamicznie renderowany jest interfejs testu.
 * @property {HTMLElement} sidebar - Boczny panel z formularzem, ukrywany po rozpoczęciu testu.
 * @property {string} examCode - Kod egzaminu (np. "INF.03") pobrany z atrybutu data.
 */
class PersonalizedTest {
    /**
     * @constructs PersonalizedTest
     * @description Wyszukuje kluczowe elementy DOM, pobiera dane konfiguracyjne (np. kod egzaminu)
     * i wiąże niezbędne nasłuchiwacze zdarzeń poprzez wywołanie `init()`.
     */
    constructor() {
        this.pageContainer = document.getElementById('quiz-personalized-test');
        if (!this.pageContainer) {
            console.error("Błąd krytyczny: Nie znaleziono kontenera strony #quiz-personalized-test.");
            return;
        }

        this.form = document.getElementById('topic-form');
        this.quizContainer = document.getElementById('quiz-container');
        this.sidebar = document.querySelector('.quiz-page-layout__sidebar');
        this.examCode = this.pageContainer.dataset.examCode;

        this.init();
    }

    /**
     * @method init
     * @description Inicjalizuje nasłuchiwacz zdarzenia 'submit' na formularzu konfiguracyjnym.
     * @private
     */
    init() {
        if (this.form) {
            this.form.addEventListener('submit', this.startTest.bind(this));
        }
    }
    
    /**
     * @method startTest
     * @description Główna metoda orkiestrująca, uruchamiana po wysłaniu formularza.
     * Jej zadaniem jest: zebrać dane z formularza, przygotować interfejs,
     * pobrać dane testu z API, a na końcu zainicjalizować i uruchomić `TestRunner`,
     * przekazując mu pobrane dane i kontrolę nad dalszym przebiegiem testu.
     * @param {Event} event - Obiekt zdarzenia 'submit' z formularza.
     * @private
     * @async
     */
    async startTest(event) {
        // Krok 1: Zapobieganie domyślnemu odświeżeniu strony.
        event.preventDefault();

        // Krok 2: Zbieranie opcji skonfigurowanych przez użytkownika.
        const selectedSubjects = [...this.form.querySelectorAll('input[name="subject[]"]:checked')].map(cb => cb.value);
        const premiumOption = this.form.querySelector('input[name="premium_option"]:checked')?.value || null;
        const questionCount = this.form.querySelector('#question-count').value;

        // Krok 3: Walidacja.
        if (selectedSubjects.length === 0) {
            alert('Wybierz przynajmniej jedną kategorię tematyczną.');
            return;
        }

        // Krok 4: Logika określająca, czy test jest pełną symulacją egzaminu.
        const allTopicCheckboxes = [...this.form.querySelectorAll('.topic-checkbox')];
        const areAllTopicsSelected = allTopicCheckboxes.every(cb => cb.checked);
        const isConsideredFullExam = areAllTopicsSelected && parseInt(questionCount, 10) === 40;

        // Krok 5: Przygotowanie interfejsu - ukrycie formularza i wstawienie szablonu testu.
        this.sidebar.classList.add('hidden');
        this.quizContainer.innerHTML = `
            <div id="loading-screen" class="test-loading"><h2>Trwa przygotowywanie Twojego testu...</h2><div class="spinner"></div></div>
            <div id="test-view" class="hidden">
                <header class="test-header"><h1 class="test-header__title">Test Spersonalizowany</h1><div class="test-header__meta"><div id="timer">00:00</div><div id="question-counter">Pytanie 1 / ${questionCount}</div></div></header>
                <div id="questions-wrapper" class="questions-wrapper"></div>
                <footer class="test-footer"><button id="finish-test-btn" class="btn btn--danger">Zakończ i sprawdź test</button></footer>
            </div>
            <div id="results-screen" class="results-container hidden">
                <h1 class="results-container__title">Wyniki Testu</h1>
                <div id="score-summary" class="score-summary"><p>Twój wynik: <strong id="score-percent">0%</strong></p><p>Poprawne odpowiedzi: <strong id="correct-count">0</strong></p><p>Czas ukończenia: <strong id="duration">00:00</strong></p></div>
                <div id="results-details" class="results-details"></div>
                <div class="results-container__actions"><a href="/examly/public/" class="btn btn--primary">Wróć na stronę główną</a></div>
            </div>`;
        
        const loadingScreen = document.getElementById('loading-screen');
        const testView = document.getElementById('test-view');

        // Krok 6: Wywołanie API w celu pobrania pytań.
        const response = await api.fetchPersonalizedTest(this.examCode, selectedSubjects, premiumOption, questionCount);

        // Krok 7: Przetworzenie odpowiedzi z API.
        if (response.success && response.data.questions) {
            loadingScreen.classList.add('hidden');
            testView.classList.remove('hidden');

            // Krok 8: Inicjalizacja i uruchomienie `TestRunner` z pobranymi danymi.
            // Od tego momentu `TestRunner` przejmuje pełną kontrolę nad logiką testu.
            const testRunner = new TestRunner({
                testView: testView,
                questionsWrapper: document.getElementById('questions-wrapper'),
                resultsScreen: document.getElementById('results-screen'),
                finishBtn: document.getElementById('finish-test-btn'),
                resultsDetailsContainer: document.getElementById('results-details'),
                isFullExam: isConsideredFullExam
            });
            testRunner.run(response.data.questions);
        } else {
            // W przypadku błędu, wyświetlenie komunikatu.
            this.quizContainer.innerHTML = `<p class="alert alert--error">${response.error || 'Nie udało się załadować pytań.'}</p>`;
        }
    }
}

/**
 * @event DOMContentLoaded
 * @description Punkt wejściowy skryptu. Po załadowaniu struktury DOM,
 * tworzy instancję klasy `PersonalizedTest`, aby aktywować całą logikę strony.
 */
document.addEventListener('DOMContentLoaded', () => {
    new PersonalizedTest();
});