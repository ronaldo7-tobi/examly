/**
 * @module quiz
 * @description Główny skrypt sterujący logiką interaktywnego quizu "jedno pytanie".
 * Inicjalizuje klasę Quiz, która zarządza całym procesem.
 */

import * as api from './modules/api.js';
import * as ui from './modules/ui.js';

/**
 * @class Quiz
 * @classdesc Zarządza stanem i logiką całego quizu w trybie "jedno pytanie".
 * Odpowiada za obsługę formularza, pobieranie pytań, sprawdzanie odpowiedzi i renderowanie UI.
 */
class Quiz {
    /**
     * @param {string} topicFormId - ID elementu formularza wyboru tematów.
     * @param {string} quizContainerId - ID kontenera, w którym będzie renderowany quiz.
     */
    constructor(topicFormId, quizContainerId) {
        /**
         * Element DOM formularza wyboru tematów.
         * @type {HTMLFormElement}
         * @private
         */
        this.topicForm = document.getElementById(topicFormId);

        /**
         * Główny kontener do renderowania pytań i odpowiedzi.
         * @type {HTMLElement}
         * @private
         */
        this.quizContainer = document.getElementById(quizContainerId);

        /**
         * Przechowuje aktualnie wybrane przez użytkownika kategorie tematyczne.
         * @type {string[]}
         * @private
         */
        this.currentSubjects = [];

        /**
         * Przechowuje wyjaśnienie do aktualnego pytania, aby móc je wyświetlić po odpowiedzi.
         * @type {string|null}
         * @private
         */
        this.currentExplanation = null;

        // Bindowanie metod, aby `this` wewnątrz nich zawsze wskazywało na instancję klasy.
        this.handleTopicSubmit = this.handleTopicSubmit.bind(this);
        this.handleAnswerClick = this.handleAnswerClick.bind(this);
        this.startNewQuestion = this.startNewQuestion.bind(this);
    }

    /**
     * Inicjalizuje quiz poprzez dodanie nasłuchiwaczy zdarzeń (event listeners).
     * @public
     * @returns {void}
     */
    init() {
        if (this.topicForm) {
            this.topicForm.addEventListener('submit', this.handleTopicSubmit);
        }
        if (this.quizContainer) {
            // Używamy delegacji zdarzeń, nasłuchując na całym kontenerze.
            this.quizContainer.addEventListener('click', this.handleAnswerClick);
        }
    }
    
    /**
     * Obsługuje wysłanie formularza z tematami.
     * Pobiera dane z formularza i rozpoczyna proces ładowania pierwszego pytania.
     * @private
     * @param {Event} event - Obiekt zdarzenia 'submit'.
     * @returns {Promise<void>}
     */
    async handleTopicSubmit(event) {
        event.preventDefault();
        const formData = new FormData(this.topicForm);
        this.currentSubjects = formData.getAll('subject[]');

        const premiumCheckbox = this.topicForm.querySelector('.premium-checkbox:checked');
        this.currentOption = premiumCheckbox ? premiumCheckbox.value : null;

        if (this.currentSubjects.length === 0) {
            ui.showError(this.quizContainer, 'Wybierz temat, aby rozpocząć naukę.');
            return;
        }

        this.topicForm.style.pointerEvents = 'none'; // Blokujemy formularz na czas ładowania
        await this.startNewQuestion();
        this.topicForm.style.pointerEvents = 'auto'; // Odblokowujemy po załadowaniu
    }
    
    /**
     * Pobiera nowe pytanie z API i zleca jego wyrenderowanie w kontenerze.
     * Obsługuje obiekt wyniku z `api.fetchQuestion` bez użycia `try...catch`.
     * @private
     * @returns {Promise<void>}
     */
    async startNewQuestion() {
        this.currentExplanation = null;
        
        const result = await api.fetchQuestion(this.currentSubjects, this.currentOption);

        if (result.success) {
            const data = result.data;
            // Sprawdzamy, czy serwer nie poinformował nas o braku pytań
            if (data.status && data.status === 'no_questions_left') {
                ui.showInfo(this.quizContainer, data.message);
                this.topicForm.style.display = 'block';
            } else {
                // Standardowa ścieżka - renderujemy pytanie
                this.currentExplanation = data.question.explanation;
                ui.renderQuestion(this.quizContainer, data.question, data.answers);
            }
        } else {
            // Obsługa błędu zwróconego przez API
            ui.showError(this.quizContainer, result.error);
            this.topicForm.style.display = 'block';
        }
    }
    
    /**
     * Obsługuje kliknięcie w jedną z odpowiedzi.
     * Identyfikuje wybraną odpowiedź, wysyła ją do sprawdzenia i aktualizuje UI z wynikiem.
     * Obsługuje obiekt wyniku z `api.checkAnswer` bez użycia `try...catch`.
     * @private
     * @param {Event} event - Obiekt zdarzenia 'click'.
     * @returns {Promise<void>}
     */
    async handleAnswerClick(event) {
        const clickedLabel = event.target.closest('.quiz-card__answer');
        if (!clickedLabel) return; // Kliknięto poza odpowiedzią
        
        const answersContainer = clickedLabel.closest('.quiz-card__answers');
        if (answersContainer.style.pointerEvents === 'none') return; // Już odpowiedziano

        answersContainer.style.pointerEvents = 'none';

        const userAnswerId = clickedLabel.querySelector('input[type="radio"]').value;
        const questionIdInput = document.getElementById('question_id_hidden');
        if (!questionIdInput) return;
        const questionId = questionIdInput.value;
        
        const result = await api.checkAnswer(questionId, userAnswerId);

        if (result.success) {
            const data = result.data;
            ui.showAnswerFeedback(data.is_correct, data.correct_answer_id, userAnswerId);
            ui.renderActionButtons(this.currentExplanation, this.startNewQuestion);
        } else {
            alert(`Błąd sprawdzania odpowiedzi: ${result.error}`);
            answersContainer.style.pointerEvents = 'auto'; // Odblokowujemy w razie błędu
        }
    }
}

/**
 * Punkt wejściowy skryptu.
 * Po załadowaniu całej struktury DOM, tworzona jest nowa instancja klasy Quiz,
 * która następnie jest inicjalizowana.
 */
document.addEventListener('DOMContentLoaded', () => {
    const quiz = new Quiz('topic-form', 'quiz-container');
    quiz.init();
});