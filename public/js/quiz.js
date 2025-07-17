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

        const specialOptions = ['toDiscover', 'toRemind', 'toImprove'];
        const selectedTopics = this.currentSubjects.filter(subject => !specialOptions.includes(subject));

        if (selectedTopics.length === 0) {
            const message = this.currentSubjects.length > 0 ?
                'Proszę wybrać przynajmniej jedną kategorię tematyczną.' :
                'Wybierz temat, aby rozpocząć naukę.';
            ui.showError(this.quizContainer, message);
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
                // Sprawdzamy, czy serwer nie poinformował nas o braku pytań
                if (data.status && data.status === 'no_questions_left') {
                    ui.showInfo(this.quizContainer, data.message); // Wyświetlamy niebieski alert
                    this.topicForm.style.display = 'block'; // Pokazujemy ponownie opcje
                } else {
                    // Standardowa ścieżka - renderujemy pytanie
                    this.currentExplanation = data.question.explanation;
                    ui.renderQuestion(this.quizContainer, data.question, data.answers);
                }
            } else {
                // Tutaj trafiają tylko prawdziwe błędy
                ui.showError(this.quizContainer, data.message);
                this.topicForm.style.display = 'block';
            }
        } catch (error) {
            console.error(error);
            ui.showError(this.quizContainer, 'Wystąpił błąd komunikacji z serwerem.');
        }
    }
    
    // Obsługuje kliknięcie w odpowiedź (dzięki delegacji zdarzeń)
    async handleAnswerClick(event) {
        // Sprawdzamy, czy kliknięty element to odpowiedź, której szukamy
        const clickedLabel = event.target.closest('.quiz-card__answer');
        
        // Jeśli kliknięto gdzie indziej (np. w tło), ignorujemy to
        if (!clickedLabel) return;
        
        // Sprawdzamy, czy odpowiedzi nie są już zablokowane
        const answersContainer = clickedLabel.closest('.quiz-card__answers');
        if (answersContainer.style.pointerEvents === 'none') {
            return; // Już odpowiedziano, nie rób nic
        }

        // Blokujemy dalsze kliknięcia
        answersContainer.style.pointerEvents = 'none';

        const userAnswerId = clickedLabel.querySelector('input[type="radio"]').value;
        // Upewniamy się, że element question_id_hidden istnieje przed próbą odczytu
        const questionIdInput = document.getElementById('question_id_hidden');
        if (!questionIdInput) return; // Zabezpieczenie
        const questionId = questionIdInput.value;


        try {
            const data = await api.checkAnswer(questionId, userAnswerId);
            if (data.success) {
                ui.showAnswerFeedback(data.is_correct, data.correct_answer_id, userAnswerId);
                // Przekazujemy this.startNewQuestion jako callback do przycisku "Następne pytanie"
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