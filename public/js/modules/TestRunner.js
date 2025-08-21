/**
 * @module TestRunner
 * @description Moduł dostarcza reużywalną klasę `TestRunner`, która jest generycznym
 * "silnikiem" do przeprowadzania testów. Jest zaprojektowany tak, aby być
 * niezależnym od sposobu tworzenia testu (np. pełny egzamin, test spersonalizowany).
 * Jego odpowiedzialność to renderowanie pytań, obsługa interakcji, mierzenie czasu
 * i kompleksowe zapisywanie wyników w API.
 */

import api from './ApiClient.js';
import { escapeHTML } from '../utils/sanitize.js';

/**
 * @class TestRunner
 * @classdesc Generyczna klasa do obsługi logiki dowolnego testu opartego na pytaniach.
 * Otrzymuje wszystkie zależności (elementy interfejsu) i dane (pytania)
 * z zewnątrz, co pozwala na jej wielokrotne wykorzystanie w różnych kontekstach.
 * @property {Array<Object>} questionsData - Tablica przechowująca dane pytań i odpowiedzi dla bieżącego testu.
 * @property {?number} timerInterval - ID interwału zegara, używane do jego zatrzymania.
 * @property {number} timeSpent - Czas spędzony na teście w sekundach.
 * @property {boolean} isTestInProgress - Flaga wskazująca, czy test jest aktualnie w toku.
 */
export class TestRunner {
    /**
     * @constructs TestRunner
     * @description Inicjalizuje instancję TestRunnera z podaną konfiguracją.
     * @param {object} config - Obiekt konfiguracyjny z zależnościami.
     * @param {HTMLElement} config.testView - Kontener widoku aktywnego testu.
     * @param {HTMLElement} config.questionsWrapper - Kontener, w którym renderowane są pytania.
     * @param {HTMLElement} config.resultsScreen - Ekran wyników wyświetlany po zakończeniu testu.
     * @param {HTMLElement} config.finishBtn - Przycisk do ręcznego zakończenia testu.
     * @param {HTMLElement} config.resultsDetailsContainer - Kontener na szczegółowe, pokolorowane wyniki.
     * @param {boolean} [config.isFullExam=false] - Flaga określająca, czy test jest pełnym egzaminem.
     */
    constructor(config) {
        this.testView = config.testView;
        this.questionsWrapper = config.questionsWrapper;
        this.resultsScreen = config.resultsScreen;
        this.finishBtn = config.finishBtn;
        this.resultsDetailsContainer = config.resultsDetailsContainer;
        
        this.isFullExam = config.isFullExam || false;
        this.questionsData = [];
        this.timerInterval = null;
        this.timeSpent = 0;
        this.isTestInProgress = false;

        // Bindowanie metod w celu zapewnienia prawidłowego kontekstu `this`.
        this.handleBeforeUnload = this.handleBeforeUnload.bind(this);
        this.finishTest = this.finishTest.bind(this);
        this.handleAnswerSelection = this.handleAnswerSelection.bind(this);
    }

    /**
     * Uruchamia wykonanie testu z podanym zestawem pytań.
     * @param {Array<Object>} questions - Tablica obiektów z danymi pytań.
     */
    run(questions) {
        this.questionsData = questions;
        this.bindListeners();
        this.renderAllQuestions(this.questionsData);
        // Czas na odpowiedź: 1.5 minuty (90 sekund) na pytanie.
        const duration = this.questionsData.length * 90;
        this.startTimer(duration);
        this.isTestInProgress = true;
    }

    /**
     * Wiąże wszystkie niezbędne nasłuchiwacze zdarzeń.
     * @private
     */
    bindListeners() {
        window.addEventListener('beforeunload', this.handleBeforeUnload);
        this.finishBtn.addEventListener('click', this.finishTest);
        this.questionsWrapper.addEventListener('click', this.handleAnswerSelection);
    }

    /**
     * Renderuje wszystkie pytania i odpowiedzi na stronie.
     * @param {Array<Object>} questions - Tablica z danymi pytań.
     * @private
     */
    renderAllQuestions(questions) {
        this.questionsWrapper.innerHTML = '';
        questions.forEach((qData, index) => {
            const question = qData.question;
            const answers = qData.answers;
            let answersHTML = '';
            answers.forEach((answer, index) => {
                const letter = String.fromCharCode(65 + index);
                const isCode = /[;(){}<>]/.test(answer.content);
                const contentHTML = isCode ? `<code class="code">${escapeHTML(answer.content)}</code>` : escapeHTML(answer.content);

                answersHTML += `
                    <label class="quiz-card__answer">
                        <input type="radio" name="question_${question.id}" value="${answer.id}">
                        <span class="quiz-card__answer-prefix">${letter}</span>
                        <span class="quiz-card__answer-text">${contentHTML}</span>
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
                <div class="quiz-card__answers">${answersHTML}</div>
                <div class="quiz-card__actions">
                    <div class="quiz-card__button-container"></div>
                    <div class="quiz-card__explanation"></div>
                </div>
            `;
            this.questionsWrapper.appendChild(questionElement);
        });
        const counter = document.getElementById('question-counter');
        if(counter) {
            counter.textContent = `Pytanie 1 / ${questions.length}`;
        }
    }

    /**
     * Finalizuje test, oblicza wyniki i zapisuje je w API.
     * Metoda zatrzymuje zegar, zbiera odpowiedzi, a następnie asynchronicznie
     * i równolegle wysyła do API dwa żądania:
     * 1. Ogólny wynik testu (`saveTestResult`).
     * 2. Szczegółowy postęp dla każdego pytania (`saveBulkProgress`).
     * @async
     */
    async finishTest() {
        this.isTestInProgress = false;
        clearInterval(this.timerInterval);
        window.removeEventListener('beforeunload', this.handleBeforeUnload);
        
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
                userAnswers[questionId] = null;
            }
            progressData.push({ questionId: questionId, isCorrect: isCorrect });
        });

        let score;
        const baseScore = this.questionsData.length > 0 ? (correctAnswersCount / this.questionsData.length) * 100 : 0;

        if (this.isFullExam) {
            // Dla pełnego egzaminu nie zaokrąglamy wyniku
            score = baseScore;
        } else {
            // Dla testu spersonalizowanego zaokrąglamy do najbliższej liczby całkowitej
            score = Math.round(baseScore);
        }
        
        this.showResults(score, correctAnswersCount, userAnswers);

        // Sprawdzamy status zalogowania PRZED wysłaniem zapytania do API.
        if (!window.examlyAppState || !window.examlyAppState.isUserLoggedIn) {
            return;
        }

        const resultData = {
            score_percent: score,
            correct_answers: correctAnswersCount,
            total_questions: this.questionsData.length,
            duration_seconds: this.timeSpent,
            topic_ids: Array.from(topicIdsInTest),
            is_full_exam: this.isFullExam
        };
        
        // Równoległe wysyłanie wyników i postępów za pomocą Promise.all
        await Promise.all([
            api.saveTestResult(resultData),
            api.saveBulkProgress(progressData)
        ]);
    }

    /**
     * Wyświetla nowy, ulepszony ekran wyników po zakończeniu testu.
     * @param {number} score - Wynik testu w procentach.
     * @param {number} correctCount - Całkowita liczba poprawnych odpowiedzi.
     * @param {Object<string, number|null>} userAnswers - Obiekt z odpowiedziami użytkownika.
     * @private
     */
    showResults(score, correctCount, userAnswers) {
        this.testView.classList.add('hidden');
        this.resultsScreen.classList.remove('hidden');

        // Pobieramy główny kontener, w którym zbudujemy nowy widok wyników.
        const scoreSummaryContainer = document.getElementById('score-summary');
        
        // Wynik dla animacji (musi być liczbą całkowitą)
        const scoreForAnimation = Math.round(score);

        // Wynik do wyświetlenia (zachowuje precyzję dla pełnego egzaminu)
        const scoreToDisplay = this.isFullExam ? score : Math.round(score);

        scoreSummaryContainer.innerHTML = `
            <div class="score-circle" style="--score: ${scoreForAnimation}">s
                <div class="score-circle__percent">${scoreToDisplay}%</div>
            </div>
            <div class="score-stats">
                <div class="stat-item">
                    <i class="stat-item__icon fas fa-check"></i>
                    <div>
                        <p class="stat-item__label">Poprawne odpowiedzi</p>
                        <p class="stat-item__value">${correctCount} / ${this.questionsData.length}</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="stat-item__icon fas fa-clock"></i>
                    <div>
                        <p class="stat-item__label">Czas ukończenia</p>
                        <p class="stat-item__value" id="duration-value">00:00</p>
                    </div>
                </div>
            </div>
        `;
        
        // Obliczamy i wstawiamy czas ukończenia do nowego elementu.
        const durationMinutes = Math.floor(this.timeSpent / 60);
        const durationSeconds = this.timeSpent % 60;
        document.getElementById('duration-value').textContent = `${String(durationMinutes).padStart(2, '0')}:${String(durationSeconds).padStart(2, '0')}`;

        // 1. Stwórz element na wiadomość o statystykach.
        const statsInfo = document.createElement('p');
        statsInfo.className = 'results-container__stats-info'; 

        // 2. Sprawdź stan zalogowania i ustaw odpowiednią treść.
        if (window.examlyAppState?.isUserLoggedIn) {
            statsInfo.innerHTML = `
                Świetna robota! Możesz śledzić swoje postępy i analizować wyniki 
                w zakładce <a href="statistics">Statystyki</a>.
            `;
        } else {
            statsInfo.innerHTML = `
                Chcesz śledzić swoje postępy i zapisywać wyniki? 
                <a href="register">Załóż darmowe konto</a>, aby odblokować statystyki!
            `;
        }

        // 3. Wstaw nową wiadomość zaraz PO kontenerze z wynikami.
        if (scoreSummaryContainer) {
            scoreSummaryContainer.insertAdjacentElement('afterend', statsInfo);
        }

        // Poniższa logika pozostaje bez zmian, ponieważ nadal jest poprawna.
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
                        correctLabel?.classList.add('correct');
                    }
                } else {
                    correctLabel?.classList.add('missed');
                }
            }
            
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

        this.resultsDetailsContainer.querySelectorAll('.quiz-card__answer.selected').forEach(label => {
            label.classList.remove('selected');
        });

        const actionsContainer = this.resultsScreen.querySelector('.results-container__actions');
        if (actionsContainer && !actionsContainer.querySelector('#solve-again-btn')) {
            const solveAgainButton = document.createElement('button');
            solveAgainButton.id = 'solve-again-btn';
            solveAgainButton.className = 'btn btn--secondary';
            solveAgainButton.textContent = 'Rozwiąż ponownie';
            solveAgainButton.addEventListener('click', () => location.reload());
            actionsContainer.appendChild(solveAgainButton);
        }

        window.scrollTo(0, 0);
    }

    /**
     * Obsługuje zdarzenie 'beforeunload', aby zapobiec przypadkowemu zamknięciu strony.
     * @param {Event} event - Obiekt zdarzenia.
     * @private
     */
    handleBeforeUnload(event) {
        if (this.isTestInProgress) {
            event.preventDefault();
            event.returnValue = '';
        }
    }

    /**
     * Obsługuje kliknięcie na odpowiedź, dodając wizualny feedback.
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
            const timerEl = document.getElementById('timer');
            if (timerEl) {
                timerEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
            if (timeLeft <= 0) {
                this.finishTest();
            }
        }, 1000);
    }
}