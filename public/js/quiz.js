// Plik: public/js/quiz.js

// Importujemy funkcje z naszych modułów
import * as api from './modules/api.js';
import * as ui from './modules/ui.js';

// Klasa Quiz zarządza stanem i logiką całego quizu
class Quiz {
    constructor(topicFormId, quizContainerId) {
        this.topicForm = document.getElementById(topicFormId);
        this.quizContainer = document.getElementById(quizContainerId);
        this.currentSubjects = [];
        this.currentExplanation = null;

        // "Bindowanie" metod, aby `this` wskazywało na instancję klasy
        this.handleTopicSubmit = this.handleTopicSubmit.bind(this);
        this.handleAnswerClick = this.handleAnswerClick.bind(this);
        this.startNewQuestion = this.startNewQuestion.bind(this);
    }

    // Inicjalizuje quiz, dodając nasłuchiwacze
    init() {
        if (this.topicForm) {
            this.topicForm.addEventListener('submit', this.handleTopicSubmit);
        }
        if (this.quizContainer) {
            this.quizContainer.addEventListener('click', this.handleAnswerClick);
        }
    }
    // Obsługuje wysłanie formularza z tematami
    async handleTopicSubmit(event) {
        event.preventDefault();
        const formData = new FormData(this.topicForm);
        this.currentSubjects = formData.getAll('subject[]');

        if (this.currentSubjects.length === 0) {
            // Jeśli nie ma wybranych tematów, czyścimy kontener z quizem
            this.quizContainer.innerHTML = '<p class="quiz-placeholder">Wybierz przynajmniej jeden temat, aby rozpocząć naukę.</p>';
            return;
        }

        this.topicForm.style.pointerEvents = 'none';
        await this.startNewQuestion();
        this.topicForm.style.pointerEvents = 'auto';
    }
    
    // Pobiera i renderuje nowe pytanie
    async startNewQuestion() {
        ui.showLoading(this.quizContainer);
        this.currentExplanation = null;

        try {
            const data = await api.fetchQuestion(this.currentSubjects);
            if (data.success) {
                this.currentExplanation = data.question.explanation;
                ui.renderQuestion(this.quizContainer, data.question, data.answers);

                const answersContainer = document.querySelector('.quiz-card__answers');
                if (answersContainer) {
                    answersContainer.addEventListener('click', this.handleAnswerClick);
                }
            } else {
                ui.showError(this.quizContainer, data.message);
                this.topicForm.style.display = 'block';
            }
        } catch (error) {
            console.error(error);
            ui.showError(this.quizContainer, 'Wystąpił błąd komunikacji z serwerem.');
        }
    }
    
    // Obsługuje kliknięcie w odpowiedź
    async handleAnswerClick(event) {
        const clickedLabel = event.target.closest('.quiz-card__answer');
        if (!clickedLabel) return;
        
        const answersContainer = document.querySelector('.quiz-card__answers');
        if (answersContainer) {
            answersContainer.style.pointerEvents = 'none'; // Blokujemy kliknięcia
        }
        answersContainer.style.pointerEvents = 'none'; // Blokujemy kliknięcia

        const userAnswerId = clickedLabel.querySelector('input[type="radio"]').value;
        const questionId = document.getElementById('question_id_hidden').value;

        try {
            const data = await api.checkAnswer(questionId, userAnswerId);
            if (data.success) {
                ui.showAnswerFeedback(data.is_correct, data.correct_answer_id, userAnswerId);
                ui.renderActionButtons(this.currentExplanation, this.startNewQuestion);
            } else {
                alert(`Błąd sprawdzania odpowiedzi: ${data.message}`);
                answersContainer.style.pointerEvents = 'auto'; // Odblokowujemy w razie błędu
            }
        } catch (error) {
            console.error(error);
            alert('Wystąpił błąd komunikacji z serwerem.');
            answersContainer.style.pointerEvents = 'auto';
        }
    }
}

// Uruchamiamy quiz po załadowaniu strony
document.addEventListener('DOMContentLoaded', () => {
    const quiz = new Quiz('topic-form', 'quiz-container');
    quiz.init();
});