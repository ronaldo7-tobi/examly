/**
 * @module quiz
 * @description Główny skrypt sterujący logiką interaktywnego quizu "jedno pytanie".
 * @version 1.3.0
 */

import * as api from './modules/api.js';
import * as ui from './modules/ui.js';

class Quiz {
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

    init() {
        this.topicForm.addEventListener('submit', this.handleTopicSubmit.bind(this));
        // Nasłuchujemy na całym kontenerze, aby łapać kliknięcia na odpowiedziach i przyciskach.
        this.quizContainer.addEventListener('click', this.handleInteraction.bind(this));
    }
    
    async handleTopicSubmit(event) {
        event.preventDefault();
        await this.startNewQuestion();
    }
    
    async startNewQuestion() {
        this.currentExplanation = null;
        
        const selectedSubjects = [...this.topicForm.querySelectorAll('input[name="subject[]"]:checked')].map(cb => cb.value);
        const premiumOption = this.topicForm.querySelector('input[name="premium_option"]:checked')?.value || null;

        if (selectedSubjects.length === 0) {
            ui.showInfo(this.quizContainer, 'Wybierz przynajmniej jedną kategorię, aby rozpocząć naukę.');
            return;
        }
        
        const result = await api.fetchQuestion(this.examCode, selectedSubjects, premiumOption);

        if (result.success) {
            const data = result.data;
            if (data.status === 'no_questions_left') {
                ui.showInfo(this.quizContainer, data.message);
            } else {
                this.currentExplanation = data.question.explanation;
                // Przekazujemy ID pytania do renderQuestion, aby utworzyć ukryte pole.
                ui.renderQuestion(this.quizContainer, data.question, data.answers);
            }
        } else {
            ui.showError(this.quizContainer, result.error);
        }
    }
    
    /**
     * Centralna metoda obsługująca kliknięcia wewnątrz kontenera quizu.
     * Reaguje na kliknięcie w etykietę odpowiedzi lub w przyciski akcji.
     * @private
     * @param {Event} event - Obiekt zdarzenia 'click'.
     */
    handleInteraction(event) {
        // --- NAJWAŻNIEJSZA ZMIANA JEST TUTAJ ---
        const clickedLabel = event.target.closest('.quiz-card__answer');
        
        // Sprawdzamy, czy kliknięto na odpowiedź i czy nie jest ona zablokowana
        if (clickedLabel) {
            const answersContainer = clickedLabel.closest('.quiz-card__answers');
            if (answersContainer && answersContainer.dataset.answered === 'true') {
                return; // Już odpowiedziano, ignorujemy kliknięcie.
            }
            this.checkSelectedAnswer(clickedLabel);
            return; // Kończymy, aby uniknąć sprawdzania przycisków
        }
    }

    /**
     * Sprawdza wybraną przez użytkownika odpowiedź.
     * @private
     * @param {HTMLElement} clickedLabel - Etykieta odpowiedzi, która została kliknięta.
     */
    async checkSelectedAnswer(clickedLabel) {
        const answersContainer = clickedLabel.closest('.quiz-card__answers');
        answersContainer.dataset.answered = 'true'; // Ustawiamy flagę, że odpowiedziano

        const userAnswerId = clickedLabel.querySelector('input[type="radio"]').value;
        const questionId = document.getElementById('question_id_hidden').value;
        
        const result = await api.checkAnswer(questionId, userAnswerId);

        if (result.success) {
            const data = result.data;
            ui.showAnswerFeedback(data.is_correct, data.correct_answer_id, userAnswerId);
            // Przekazujemy callback `this.startNewQuestion` do renderActionButtons
            ui.renderActionButtons(this.currentExplanation, this.startNewQuestion.bind(this));
        } else {
            alert(`Błąd: ${result.error}`);
            delete answersContainer.dataset.answered; // Odblokowujemy w razie błędu
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Quiz();
});