import { fetchFullTest, saveTestResult, saveBulkProgress} from './modules/api.js';

/**
 * @class Test
 * @description Kompleksowo zarządza logiką pełnego testu egzaminacyjnego.
 * Odpowiada za pobieranie pytań, renderowanie interfejsu,
 * obsługę interakcji użytkownika, mierzenie czasu oraz
 * finalizację testu i zapisywanie wyników.
 * @property {HTMLElement} testContainer - Główny kontener komponentu testu.
 * @property {HTMLElement} loadingScreen - Ekran ładowania wyświetlany podczas pobierania danych.
 * @property {HTMLElement} testView - Kontener widoku aktywnego testu.
 * @property {HTMLElement} questionsWrapper - Kontener, w którym renderowane są wszystkie pytania.
 * @property {HTMLElement} resultsScreen - Ekran wyników wyświetlany po zakończeniu testu.
 * @property {HTMLElement} finishBtn - Przycisk do ręcznego zakończenia testu.
 * @property {HTMLElement} resultsDetailsContainer - Kontener na szczegółowe, pokolorowane wyniki.
 * @property {string} examCode - Kod egzaminu (np. "INF.03") pobrany z atrybutu data.
 * @property {Array<Object>} questionsData - Tablica przechowująca wszystkie dane pytań i odpowiedzi.
 * @property {?number} timerInterval - ID interwału zegara, używane do jego zatrzymania. Null, gdy nieaktywny.
 * @property {number} timeSpent - Czas spędzony na teście w sekundach.
 * @property {boolean} isTestInProgress - Flaga wskazująca, czy test jest w trakcie.
 */
class Test {
    /**
     * Konstruktor klasy Test.
     * Wyszukuje i przypisuje wszystkie niezbędne elementy DOM do właściwości klasy,
     * inicjalizuje stan początkowy (np. pobiera kod egzaminu)
     * i uruchamia całą logikę poprzez wywołanie metody `init()`.
     * Przerywa działanie, jeśli nie znajdzie głównego kontenera.
     */
    constructor() {
        this.testContainer = document.getElementById('test-container');
        if (!this.testContainer) {
            console.error("Nie znaleziono kontenera testu. Inicjalizacja przerwana.");
            return;
        }

        // === PRZECHOWYWANIE ELEMENTÓW DOM JAKO WŁAŚCIWOŚCI KLASY ===
        this.loadingScreen = document.getElementById('loading-screen');
        this.testView = document.getElementById('test-view');
        this.questionsWrapper = document.getElementById('questions-wrapper');
        this.resultsScreen = document.getElementById('results-screen');
        this.finishBtn = document.getElementById('finish-test-btn');
        this.resultsDetailsContainer = document.getElementById('results-details');

        // === STAN APLIKACJI JAKO WŁAŚCIWOŚCI KLASY ===
        this.examCode = this.testContainer.dataset.examCode;
        this.questionsData = [];
        this.timerInterval = null;
        this.timeSpent = 0;
        this.isTestInProgress = false;

        this.init();
    }

    /**
     * Inicjalizuje nasłuchiwacze zdarzeń.
     * Metoda "wiąże" wszystkie niezbędne event listenery do odpowiednich
     * elementów i akcji (np. kliknięcie, próba zamknięcia strony).
     * Po ustawieniu nasłuchiwaczy, rozpoczyna proces ładowania testu.
     * @private
     */
    init() {
        window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
        this.finishBtn.addEventListener('click', this.finishTest.bind(this));
        this.questionsWrapper.addEventListener('click', this.handleAnswerSelection.bind(this));

        this.initializeTest();
    }

    // =================================================================
    // 1. METODY GŁÓWNEJ LOGIKI TESTU
    // =================================================================

    /**
     * Rozpoczyna proces inicjalizacji testu.
     * Asynchronicznie pobiera dane pełnego testu z API. W przypadku sukcesu,
     * renderuje pytania, ukrywa ekran ładowania, wyświetla widok testu
     * i uruchamia licznik czasu. W przypadku błędu, wyświetla stosowny komunikat.
     * @async
     * @private
     */
    async initializeTest() {
        if (!this.examCode) {
            this.showError('Błąd krytyczny: Brak kodu egzaminu!');
            return;
        }
        const response = await fetchFullTest(this.examCode);
        if (response.success && response.data.questions) {
            this.questionsData = response.data.questions;
            this.renderAllQuestions(this.questionsData);
            this.loadingScreen.classList.add('hidden');
            this.testView.classList.remove('hidden');
            this.startTimer(3600); // Czas na egzamin: 60 minut = 3600 sekund
            this.isTestInProgress = true;
        } else {
            this.showError(response.error || 'Nie udało się załadować pytań.');
        }
    }

    /**
     * Renderuje wszystkie pytania i odpowiedzi na stronie.
     * Czyści kontener pytań, a następnie iteruje po danych, tworząc
     * dynamicznie kod HTML dla każdej karty pytania i wstawia go do DOM.
     * @param {Array<Object>} questions - Tablica obiektów zawierających dane pytania i odpowiedzi.
     * @private
     */
    renderAllQuestions(questions) {
        this.questionsWrapper.innerHTML = '';
        questions.forEach((qData, index) => {
            const question = qData.question;
            const answers = qData.answers;
            let answersHtml = '';
            answers.forEach(answer => {
                answersHtml += `
                    <label class="quiz-card__answer">
                        <input type="radio" name="question_${question.id}" value="${answer.id}">
                        <span class="quiz-card__answer-text">${answer.content}</span>
                    </label>`;
            });
            const questionElement = document.createElement('section');
            questionElement.className = 'quiz-card';
            questionElement.id = `question-${question.id}`;
            questionElement.innerHTML = `
                <header class="quiz-card__header">
                    <span class="quiz-card__question-number">Pytanie ${index + 1} / ${questions.length}</span>
                </header>
                <div class="quiz-card__content">
                    <p class="quiz-card__question-text">${question.content}</p>
                    ${question.image_path ? `<div class="quiz-card__image-container"><img src="/examly/public/images/questions/${question.image_path}" alt="Ilustracja do pytania" class="quiz-card__image"></div>` : ''}
                </div>
                <div class="quiz-card__answers">${answersHtml}</div>
                <div class="quiz-card__actions">
                    <div class="quiz-card__button-container"></div>
                    <div class="quiz-card__explanation"></div>
                </div>
            `;
            this.questionsWrapper.appendChild(questionElement);
        });
        document.getElementById('question-counter').textContent = `Pytanie 1 / ${questions.length}`;
    }

    /**
     * Finalizuje test.
     * Zatrzymuje zegar, zbiera odpowiedzi udzielone przez użytkownika, oblicza wynik,
     * a następnie wywołuje metodę `showResults` do wyświetlenia podsumowania.
     * Na końcu asynchronicznie wysyła dane o wyniku do API.
     * @async
     */
    async finishTest() {
        this.isTestInProgress = false;
        clearInterval(this.timerInterval);

        let correctAnswersCount = 0;
        let topicIdsInTest = new Set();
        const userAnswers = {};
        const progressData = [];

        this.questionsData.forEach(qData => {
            topicIdsInTest.add(qData.question.topic_id);
            const questionId = qData.question.id;
            const correctAnswer = qData.answers.find(a => a.is_correct === 1);
            const selectedAnswerInput = this.questionsWrapper.querySelector(`input[name="question_${questionId}"]:checked`);
            let isCorrect = false;

            if (selectedAnswerInput) {
                const selectedAnswerId = parseInt(selectedAnswerInput.value, 10);
                userAnswers[questionId] = selectedAnswerId;
                if (correctAnswer && selectedAnswerId === correctAnswer.id) {
                    correctAnswersCount++;
                    isCorrect = true;
                }
            } else {
                userAnswers[questionId] = null; // Użytkownik nie odpowiedział
            }

            progressData.push({
                questionId: questionId,
                isCorrect: isCorrect
            });
        });

        const score = this.questionsData.length > 0 ? (correctAnswersCount / this.questionsData.length) * 100 : 0;

        this.showResults(score, correctAnswersCount, userAnswers);

        // Zapisz ogólny wynik testu i szczegółowy postęp.
        // Używamy Promise.all, aby oba zapytania mogły lecieć równolegle.
        const resultData = {
            score_percent: score,
            correct_answers: correctAnswersCount,
            total_questions: this.questionsData.length,
            duration_seconds: this.timeSpent,
            topic_ids: Array.from(topicIdsInTest)
        };
        
        await Promise.all([
            saveTestResult(resultData),
            saveBulkProgress(progressData)
        ]);
    }

    /**
     * Wyświetla ekran wyników po zakończeniu testu.
     * Ukrywa widok testu i pokazuje ekran podsumowania. Wypełnia go danymi
     * (procent, liczba punktów, czas), a następnie koloruje wszystkie odpowiedzi
     * na poprawne/błędne i dynamicznie dodaje przyciski do pokazania wyjaśnień.
     * @param {number} score - Wynik testu w procentach.
     * @param {number} correctCount - Całkowita liczba poprawnych odpowiedzi.
     * @param {Object<string, number|null>} userAnswers - Obiekt mapujący ID pytania na ID odpowiedzi użytkownika.
     * @private
     */
    showResults(score, correctCount, userAnswers) {
        this.testView.classList.add('hidden');
        this.resultsScreen.classList.remove('hidden');

        // Wypełnianie podsumowania
        document.getElementById('score-percent').textContent = `${score}%`;
        document.getElementById('correct-count').textContent = `${correctCount} / ${this.questionsData.length}`;
        const durationMinutes = Math.floor(this.timeSpent / 60);
        const durationSeconds = this.timeSpent % 60;
        document.getElementById('duration').textContent = `${String(durationMinutes).padStart(2, '0')}:${String(durationSeconds).padStart(2, '0')}`;

        // Kopiowanie i kolorowanie odpowiedzi
        this.resultsDetailsContainer.innerHTML = this.questionsWrapper.innerHTML;
        this.resultsDetailsContainer.querySelectorAll('input[type="radio"]').forEach(input => input.disabled = true);

        this.questionsData.forEach(qData => {
            const questionId = qData.question.id;
            const questionCard = this.resultsDetailsContainer.querySelector(`#question-${questionId}`);
            if (!questionCard) return;

            const correctAnswer = qData.answers.find(a => a.is_correct === 1);
            const selectedAnswerId = userAnswers[questionId];

            if (selectedAnswerId !== null) {
                const selectedInput = questionCard.querySelector(`input[value="${selectedAnswerId}"]`);
                if (selectedInput) selectedInput.checked = true;
            }

            if (correctAnswer) {
                const correctLabel = questionCard.querySelector(`input[value="${correctAnswer.id}"]`)?.closest('.quiz-card__answer');
                if (selectedAnswerId !== null) {
                    const selectedLabel = questionCard.querySelector(`input[value="${selectedAnswerId}"]`)?.closest('.quiz-card__answer');
                    if (selectedAnswerId === correctAnswer.id) {
                        selectedLabel?.classList.add('correct');
                    } else {
                        selectedLabel?.classList.add('incorrect');
                        correctLabel?.classList.add('correct'); // Pokaż poprawną odpowiedź
                    }
                } else {
                    correctLabel?.classList.add('missed'); // Oznacz poprawną, gdy nie udzielono odpowiedzi
                }
            }
            
            // Dodawanie przycisków "Pokaż wyjaśnienie"
            const explanation = qData.question.explanation;
            if (explanation && explanation.trim() !== '') {
                const buttonContainer = questionCard.querySelector('.quiz-card__button-container');
                const explanationContainer = questionCard.querySelector('.quiz-card__explanation');
                if (buttonContainer && explanationContainer) {
                    explanationContainer.innerHTML = `<p>${explanation}</p>`;
                    const explanationButton = document.createElement('button');
                    explanationButton.type = 'button';
                    explanationButton.textContent = 'Pokaż wyjaśnienie';
                    explanationButton.className = 'btn btn--secondary btn--small';
                    explanationButton.addEventListener('click', () => {
                        explanationContainer.classList.toggle('quiz-card__explanation--visible');
                        explanationButton.textContent = explanationContainer.classList.contains('quiz-card__explanation--visible') 
                            ? 'Ukryj wyjaśnienie' : 'Pokaż wyjaśnienie';
                    });
                    buttonContainer.appendChild(explanationButton);
                }
            }
        });

        // Czyszczenie zaznaczenia 'selected' z oryginalnego widoku
        this.resultsDetailsContainer.querySelectorAll('.quiz-card__answer.selected').forEach(label => {
            label.classList.remove('selected');
        });
        
        // Dodawanie przycisku "Rozwiąż ponownie"
        const actionsContainer = this.resultsScreen.querySelector('.results-container__actions');
        if (actionsContainer && !actionsContainer.querySelector('#solve-again-btn')) {
            const solveAgainButton = document.createElement('button');
            solveAgainButton.id = 'solve-again-btn';
            solveAgainButton.className = 'btn btn--secondary';
            solveAgainButton.textContent = 'Rozwiąż ponownie';
            solveAgainButton.addEventListener('click', () => location.reload());
            actionsContainer.appendChild(solveAgainButton);
        }

        window.scrollTo(0, 0); // Przewiń na górę strony wyników
    }

    // =================================================================
    // 2. METODY POMOCNICZE I OBSŁUGI ZDARZEŃ
    // =================================================================

    /**
     * Obsługuje zdarzenie 'beforeunload'.
     * Jeśli test jest w toku, wyświetla natywne okno przeglądarki z ostrzeżeniem,
     * aby zapobiec przypadkowemu opuszczeniu strony i utracie postępu.
     * @param {Event} event - Obiekt zdarzenia przekazywany przez przeglądarkę.
     * @private
     */
    handleBeforeUnload(event) {
        if (this.isTestInProgress) {
            event.preventDefault();
            event.returnValue = '';
        }
    }

    /**
     * Obsługuje kliknięcie na odpowiedź podczas testu.
     * Dodaje klasę 'selected' do klikniętej odpowiedzi dla wizualnego feedbacku,
     * usuwając ją jednocześnie z innych odpowiedzi w ramach tego samego pytania.
     * @param {Event} event - Obiekt zdarzenia 'click'.
     * @private
     */
    handleAnswerSelection(event) {
        const answerLabel = event.target.closest('.quiz-card__answer');
        if (!answerLabel) return;
        const currentQuestionCard = answerLabel.closest('.quiz-card');
        if (!currentQuestionCard) return;

        currentQuestionCard.querySelectorAll('.quiz-card__answer').forEach(label => label.classList.remove('selected'));
        answerLabel.classList.add('selected');
    }

    /**
     * Uruchamia i zarządza licznikiem czasu.
     * Co sekundę aktualizuje wyświetlany czas. Jeśli czas dobiegnie końca,
     * zatrzymuje licznik i automatycznie kończy test.
     * @param {number} duration - Początkowy czas testu w sekundach.
     * @private
     */
    startTimer(duration) {
        let timeLeft = duration;
        this.timerInterval = setInterval(() => {
            this.timeSpent++;
            timeLeft = duration - this.timeSpent;
            if (timeLeft < 0) timeLeft = 0;
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            if (timeLeft <= 0) {
                clearInterval(this.timerInterval);
                this.finishTest();
            }
        }, 1000);
    }
    
    /**
     * Wyświetla prosty komunikat o błędzie na ekranie ładowania.
     * Używana, gdy nie uda się pobrać danych testu.
     * @param {string} message - Treść błędu do wyświetlenia.
     * @private
     */
    showError(message) {
        this.loadingScreen.innerHTML = `<h2>Wystąpił błąd</h2><p>${message}</p>`;
    }
}

/**
 * Punkt wejściowy skryptu.
 * Po całkowitym załadowaniu i sparsowaniu drzewa DOM, tworzy nową
 * instancję klasy Test, co rozpoczyna działanie całego mechanizmu.
 */
document.addEventListener('DOMContentLoaded', () => {
    new Test();
});