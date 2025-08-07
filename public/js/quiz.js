/**
 * @module quiz
 * @description Główny skrypt sterujący logiką interaktywnego quizu w trybie "jedno pytanie".
 * Działa jako kontroler, który orkiestruje komunikację między modułem API (pobieranie danych)
 * a modułem UI (renderowanie interfejsu), zarządzając stanem aplikacji.
 * @version 1.5.0
 */

import api from './modules/api.js';
import ui from './modules/ui.js';

/**
 * @class Quiz
 * @classdesc Zarządza całym cyklem życia quizu "jedno pytanie".
 * Inicjalizuje widok, obsługuje wybór kategorii przez użytkownika,
 * pobiera pytania, sprawdza odpowiedzi i renderuje kolejne kroki.
 * @property {HTMLElement} pageContainer - Główny kontener całej strony quizu.
 * @property {HTMLFormElement} topicForm - Formularz, z którego pobierane są wybrane kategorie i opcje.
 * @property {HTMLElement} quizContainer - Kontener, w którym dynamicznie renderowane jest aktualne pytanie.
 * @property {?string} examCode - Kod egzaminu (np. "INF.03"), pobrany z atrybutu data HTML. Null, jeśli nie znaleziono.
 * @property {?string} currentExplanation - Przechowuje tekst wyjaśnienia dla aktualnego pytania, aby wyświetlić go po udzieleniu odpowiedzi.
 */
class Quiz {
    /**
     * @constructs Quiz
     * @description Wyszukuje kluczowe elementy DOM i dane inicjalizacyjne.
     * Jeśli wszystkie elementy są dostępne, uruchamia logikę przez wywołanie `init()`.
     * W przeciwnym razie, loguje błąd w konsoli.
     */
    constructor() {
        this.pageContainer = document.getElementById('quiz-single-question');
        this.topicForm = document.getElementById('topic-form');
        this.quizContainer = document.getElementById('quiz-container');
        this.examCode = this.pageContainer?.dataset.examCode || null;
        this.currentExplanation = null;

        if (this.topicForm && this.quizContainer && this.examCode) {
            this.init();
        } else {
            console.error("Nie udało się zainicjalizować quizu. Brak kluczowych elementów DOM lub atrybutu data-exam-code.");
        }
    }

    /**
     * @method init
     * @description "Wiąże" nasłuchiwacze zdarzeń z elementami DOM.
     * Używa delegacji zdarzeń na `quizContainer` dla efektywnej obsługi
     * kliknięć na dynamicznie tworzonych elementach (odpowiedzi, przyciski).
     * @private
     */
    init() {
        this.topicForm.addEventListener('submit', this.handleTopicSubmit.bind(this));
        this.quizContainer.addEventListener('click', this.handleInteraction.bind(this));
    }

    /**
     * @method handleTopicSubmit
     * @description Obsługuje zdarzenie wysłania formularza z kategoriami.
     * Zapobiega domyślnej akcji formularza i rozpoczyna proces pobierania nowego pytania.
     * @param {Event} event - Obiekt zdarzenia 'submit'.
     * @private
     * @async
     */
    async handleTopicSubmit(event) {
        event.preventDefault();
        await this.startNewQuestion();
    }

    /**
     * @method startNewQuestion
     * @description Główna metoda rozpoczynająca ładowanie nowego pytania.
     * Zbiera wybrane przez użytkownika kategorie, wywołuje API w celu pobrania pytania,
     * a następnie zleca modułowi UI jego wyrenderowanie lub wyświetlenie informacji o błędzie/braku pytań.
     * @private
     * @async
     */
    async startNewQuestion() {
        this.currentExplanation = null; // Resetuje wyjaśnienie przed nowym pytaniem

        const selectedSubjects = [...this.topicForm.querySelectorAll('input[name="subject[]"]:checked')].map(cb => cb.value);
        const premiumOption = this.topicForm.querySelector('input[name="premium_option"]:checked')?.value || null;

        if (selectedSubjects.length === 0) {
            ui.showInfo(this.quizContainer, 'Wybierz przynajmniej jedną kategorię, aby rozpocząć naukę.');
            return;
        }

        // Delegowanie wywołania API do zaimportowanej instancji `api`.
        const result = await api.fetchQuestion(this.examCode, selectedSubjects, premiumOption);

        if (result.success) {
            const data = result.data;
            if (data.status === 'no_questions_left') {
                // Delegowanie renderowania informacji do modułu `ui`.
                ui.showInfo(this.quizContainer, data.message);
            } else {
                this.currentExplanation = data.question.explanation;
                // Delegowanie renderowania pytania do modułu `ui`.
                ui.renderQuestion(this.quizContainer, data.question, data.answers);
            }
        } else {
            // Delegowanie renderowania błędu do modułu `ui`.
            ui.showError(this.quizContainer, result.error);
        }
    }

    /**
     * @method handleInteraction
     * @description Centralna metoda obsługująca kliknięcia wewnątrz kontenera quizu (delegacja zdarzeń).
     * Identyfikuje kliknięty element i wywołuje odpowiednią metodę (np. sprawdzanie odpowiedzi).
     * Zapobiega wielokrotnemu odpowiadaniu na to samo pytanie dzięki atrybutowi `data-answered`.
     * @param {Event} event - Obiekt zdarzenia 'click'.
     * @private
     */
    handleInteraction(event) {
        const clickedLabel = event.target.closest('.quiz-card__answer');
        
        if (clickedLabel) {
            const answersContainer = clickedLabel.closest('.quiz-card__answers');
            // Ignoruj kliknięcie, jeśli na pytanie już odpowiedziano (kontener jest "zablokowany").
            if (answersContainer && answersContainer.dataset.answered === 'true') {
                return;
            }
            this.checkSelectedAnswer(clickedLabel);
        }
    }

    /**
     * @method checkSelectedAnswer
     * @description Sprawdza poprawność wybranej odpowiedzi.
     * "Blokuje" możliwość dalszego odpowiadania, wysyła zapytanie do API,
     * a następnie zleca modułowi UI wyświetlenie feedbacku (poprawna/błędna odpowiedź)
     * oraz przycisków akcji (np. "Następne pytanie").
     * @param {HTMLElement} clickedLabel - Etykieta (`<label>`) odpowiedzi, która została kliknięta.
     * @private
     * @async
     */
    async checkSelectedAnswer(clickedLabel) {
        const answersContainer = clickedLabel.closest('.quiz-card__answers');
        answersContainer.dataset.answered = 'true'; // Ustaw flagę, że odpowiedziano.

        const userAnswerId = clickedLabel.querySelector('input[type="radio"]').value;
        const questionId = document.getElementById('question_id_hidden').value;

        // Delegowanie wywołania API do zaimportowanej instancji `api`.
        const result = await api.checkAnswer(questionId, userAnswerId);

        if (result.success) {
            const data = result.data;
            // Delegowanie wyświetlania feedbacku do modułu `ui`.
            ui.showAnswerFeedback(data.is_correct, data.correct_answer_id, userAnswerId);
            // Delegowanie renderowania przycisków do `ui`, przekazując `startNewQuestion` jako callback,
            // aby moduł UI mógł wywołać logikę kontrolera po kliknięciu przycisku.
            ui.renderActionButtons(this.currentExplanation, this.startNewQuestion.bind(this));
        } else {
            alert(`Błąd: ${result.error}`);
            // W razie błędu API, odblokuj możliwość ponownego odpowiedzenia.
            delete answersContainer.dataset.answered;
        }
    }
}

/**
 * @event DOMContentLoaded
 * @description Punkt wejściowy skryptu. Tworzy nową instancję klasy Quiz
 * po załadowaniu drzewa DOM, inicjując w ten sposób całą logikę aplikacji.
 */
document.addEventListener('DOMContentLoaded', () => {
    new Quiz();
});