/**
 * @module personalized-test
 * @version 2.1.0
 * @description
 * Ten moduł zarządza interaktywnym, krokowym procesem konfiguracji
 * i uruchamiania spersonalizowanego testu.
 */

import api from '../../modules/ApiClient.js';
import { TestRunner } from '../../modules/TestRunner.js';
import Toast from '../../components/Toast.js';

/**
 * @class PersonalizedTest
 * @classdesc Główna klasa orkiestrująca działanie strony spersonalizowanego testu.
 */
class PersonalizedTest {
    constructor() {
        this.pageContainer = document.getElementById('personalized-test-page');
        if (!this.pageContainer) return;

        this.form = document.getElementById('topic-form');
        this.quizContainer = document.getElementById('quiz-container');
        this.configurator = document.querySelector('.test-configurator');
        this.examCode = this.pageContainer.dataset.examCode;
        
        this.steps = this.configurator.querySelectorAll('.config-step');
        this.currentStep = 1;

        this.init();
    }

    /**
     * @method init
     * @description Inicjalizuje nasłuchiwacze zdarzeń.
     * @private
     */
    init() {
        if (this.form) {
            this.form.addEventListener('submit', this.startTest.bind(this));
            this.configurator.addEventListener('click', this.handleNavigation.bind(this));
        }
    }
    
    /**
     * Obsługuje nawigację między krokami.
     * @param {Event} event 
     * @private
     */
    handleNavigation(event) {
        const action = event.target.dataset.action;
        if (!action) return;

        if (action === 'next') {
            if (this.validateStep(this.currentStep)) {
                this.changeStep(this.currentStep + 1);
            }
        } else if (action === 'prev') {
            this.changeStep(this.currentStep - 1);
        }
    }

    /**
     * Waliduje bieżący krok przed przejściem do następnego.
     * @param {number} step - Numer kroku do walidacji.
     * @returns {boolean}
     * @private
     */
    validateStep(step) {
        if (step === 1) {
            const selectedSubjects = this.form.querySelectorAll('input[name="subject[]"]:checked');
            if (selectedSubjects.length === 0) {
                Toast.show('Wybierz przynajmniej jedną kategorię tematyczną.', 'info');
                return false;
            }
        }
        return true;
    }

    /**
     * Zmienia widoczny krok w formularzu.
     * @param {number} targetStep - Numer kroku do wyświetlenia.
     * @private
     */
    changeStep(targetStep) {
        const currentStepElement = this.configurator.querySelector(`[data-step="${this.currentStep}"]`);
        const targetStepElement = this.configurator.querySelector(`[data-step="${targetStep}"]`);
        
        if (!targetStepElement) return;

        if (currentStepElement) {
            currentStepElement.style.display = 'none';
        }
        
        targetStepElement.style.display = 'block';
        this.currentStep = targetStep;
    }
    
    /**
     * Przywraca widok konfiguratora do stanu początkowego.
     * @private
     */
    resetToConfigurator() {
        this.configurator.classList.remove('hidden');
        document.querySelector('.page-header').classList.remove('hidden');
        this.quizContainer.innerHTML = '';
        
        // 1. Zdejmij klasę z minimalną wysokością.
        this.quizContainer.classList.remove('test-container--active');
    }

    /**
     * Uruchamiana po kliknięciu "Rozpocznij Test!".
     * @param {Event} event
     * @private
     * @async
     */
    async startTest(event) {
        event.preventDefault();

        const selectedSubjects = [...this.form.querySelectorAll('input[name="subject[]"]:checked')].map(cb => cb.value);
        const premiumOption = this.form.querySelector('input[name="premium_option"]:checked')?.value || null;
        const questionCount = this.form.querySelector('#question-count').value;
        const areAllTopicsSelected = [...this.form.querySelectorAll('.topic-checkbox')].every(cb => cb.checked);
        const isConsideredFullExam = areAllTopicsSelected && parseInt(questionCount, 10) === 40 && !premiumOption;
        
        this.configurator.classList.add('hidden');
        
        // 2. Nadaj kontenerowi klasę, która utrzyma jego minimalną wysokość i zapobiegnie "skokowi" stopki.
        this.quizContainer.classList.add('test-container--active');

        // 3. Wstrzyknij HTML z domyślnie ukrytym spinnerem.
        this.quizContainer.innerHTML = `
            <div class="test-container">
                <div id="loading-screen" class="test-loading hidden"><h2>Trwa przygotowywanie Twojego testu...</h2><div class="spinner"></div></div>
                <div id="test-view" class="hidden">
                    <header class="test-header"><h1 class="test-header__title">Test Spersonalizowany - <span class="text-gradient">INF.03</span></h1><div class="test-header__meta"><div id="timer">00:00</div></div></header>
                    <div id="questions-wrapper" class="questions-wrapper"></div>
                    <footer class="test-footer"><button id="finish-test-btn" class="btn btn--primary">Zakończ i sprawdź test</button></footer>
                </div>
                <div id="results-screen" class="results-container hidden">
                    <h1 class="results-container__title">Wyniki Testu</h1>
                    <div id="score-summary" class="score-summary"><p>Twój wynik: <strong id="score-percent">0%</strong></p><p>Poprawne odpowiedzi: <strong id="correct-count">0</strong></p><p>Czas ukończenia: <strong id="duration">00:00</strong></p></div>
                    <div id="results-details" class="results-details"></div>
                    <div class="results-container__actions"><a href="/examly/public/" class="btn btn--primary">Wróć na stronę główną</a></div>
                </div>
            </div>`;
        
        const loadingScreen = document.getElementById('loading-screen');
        const testView = document.getElementById('test-view');

        let spinnerTimeout = setTimeout(() => {
            loadingScreen.classList.remove('hidden');
        }, 400);

        const response = await api.fetchPersonalizedTest(this.examCode, selectedSubjects, premiumOption, questionCount);

        clearTimeout(spinnerTimeout);

        if (response.success && response.data.questions && response.data.questions.length > 0) {
            loadingScreen.classList.add('hidden');
            testView.classList.remove('hidden');
            
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
            if (response.data && response.data.status === 'no_questions_left') {
                Toast.show(response.data.message, 'info');
            } else {
                Toast.show(response.error || 'Nie udało się załadować pytań.', 'error');
            }
            // Resetujemy widok (w tej funkcji odblokujemy przycisk).
            this.resetToConfigurator();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new PersonalizedTest();
});