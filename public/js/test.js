// Plik: public/js/test.js (WERSJA Z POPRAWIONĄ LOGIKĄ WYNIKÓW)

import { fetchFullTest, saveTestResult } from './modules/api.js';

document.addEventListener('DOMContentLoaded', () => {
    // === SELEKTORY GŁÓWNYCH ELEMENTÓW ===
    const testContainer = document.getElementById('test-container');
    if (!testContainer) {
        return; 
    }
    const loadingScreen = document.getElementById('loading-screen');
    const testView = document.getElementById('test-view');
    const questionsWrapper = document.getElementById('questions-wrapper');
    const resultsScreen = document.getElementById('results-screen');
    const finishBtn = document.getElementById('finish-test-btn');
    const resultsDetailsContainer = document.getElementById('results-details');

    // === ZMIENNE STANU APLIKACJI ===
    const examCode = testContainer.dataset.examCode;
    let questionsData = [];
    let timerInterval = null;
    let timeSpent = 0;
    let isTestInProgress = false;

    /**
     * Funkcja obsługująca próbę zamknięcia lub odświeżenia strony.
     * @param {Event} event - Obiekt zdarzenia beforeunload.
     */
    function handleBeforeUnload(event) {
        // Wyświetlaj ostrzeżenie tylko wtedy, gdy test jest aktywny.
        if (isTestInProgress) {
            event.preventDefault(); // Wymagane przez większość przeglądarek
            event.returnValue = ''; // Wymagane przez niektóre starsze przeglądarki
        }
    }
    // Dodajemy globalny nasłuchiwacz zdarzenia.
    window.addEventListener('beforeunload', handleBeforeUnload);

    // =================================================================
    // 1. GŁÓWNA LOGIKA TESTU
    // =================================================================

    async function initializeTest() {
        if (!examCode) {
            showError('Błąd krytyczny: Brak kodu egzaminu!');
            return;
        }
        const response = await fetchFullTest(examCode);
        if (response.success && response.data.questions) {
            questionsData = response.data.questions;
            renderAllQuestions(questionsData);
            loadingScreen.classList.add('hidden');
            testView.classList.remove('hidden');
            startTimer(3600);
            isTestInProgress = true;
        } else {
            showError(response.error || 'Nie udało się załadować pytań.');
        }
    }

    function renderAllQuestions(questions) {
        questionsWrapper.innerHTML = '';
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
            questionsWrapper.appendChild(questionElement);
        });
        document.getElementById('question-counter').textContent = `Pytanie 1 / ${questions.length}`;
    }

    async function finishTest() {
        isTestInProgress = false;
        clearInterval(timerInterval);
        
        let correctAnswersCount = 0;
        let topicIdsInTest = new Set();
        const userAnswers = {}; // Mapa do przechowywania odpowiedzi: { questionId: answerId }

        // KROK 1: Zbierz wszystkie odpowiedzi ZANIM zmienisz widok
        questionsData.forEach(qData => {
            topicIdsInTest.add(qData.question.topic_id);
            const questionId = qData.question.id;
            const correctAnswer = qData.answers.find(a => a.is_correct === 1);
            const selectedAnswerInput = questionsWrapper.querySelector(`input[name="question_${questionId}"]:checked`);
            
            if (selectedAnswerInput) {
                const selectedAnswerId = parseInt(selectedAnswerInput.value, 10);
                userAnswers[questionId] = selectedAnswerId;
                if (correctAnswer && selectedAnswerId === correctAnswer.id) {
                    correctAnswersCount++;
                }
            } else {
                userAnswers[questionId] = null; // Zapisz null, jeśli nie ma odpowiedzi
            }
        });
        
        const score = questionsData.length > 0 ? (correctAnswersCount / questionsData.length) * 100 : 0;
        
        // KROK 2: Przekaż zebrane odpowiedzi do funkcji wyświetlającej wyniki
        showResults(score, correctAnswersCount, userAnswers);

        // KROK 3: Zapisz wynik w tle
        const resultData = {
            score_percent: score,
            correct_answers: correctAnswersCount,
            total_questions: questionsData.length,
            duration_seconds: timeSpent,
            topic_ids: Array.from(topicIdsInTest)
        };
        await saveTestResult(resultData);
    }

    function showResults(score, correctCount, userAnswers) {
        testView.classList.add('hidden');
        resultsScreen.classList.remove('hidden');

        document.getElementById('score-percent').textContent = `${score}%`;
        document.getElementById('correct-count').textContent = `${correctCount} / ${questionsData.length}`;
        const durationMinutes = Math.floor(timeSpent / 60);
        const durationSeconds = timeSpent % 60;
        document.getElementById('duration').textContent = `${String(durationMinutes).padStart(2, '0')}:${String(durationSeconds).padStart(2, '0')}`;

        resultsDetailsContainer.innerHTML = questionsWrapper.innerHTML;
        
        resultsDetailsContainer.querySelectorAll('input[type="radio"]').forEach(input => {
            input.disabled = true;
        });
        
        questionsData.forEach(qData => {
            const questionId = qData.question.id;
            const questionCard = resultsDetailsContainer.querySelector(`#question-${questionId}`);
            if (!questionCard) return;

            const correctAnswer = qData.answers.find(a => a.is_correct === 1);
            const selectedAnswerId = userAnswers[questionId]; // Użyj mapy odpowiedzi

            // Upewnij się, że zaznaczenie jest widoczne po skopiowaniu HTML
            if (selectedAnswerId !== null) {
                const selectedInput = questionCard.querySelector(`input[value="${selectedAnswerId}"]`);
                if (selectedInput) selectedInput.checked = true;
            }

            // POPRAWIONA LOGIKA KOLOROWANIA
            if (correctAnswer) {
                const correctLabel = questionCard.querySelector(`input[value="${correctAnswer.id}"]`)?.closest('.quiz-card__answer');
                
                if (selectedAnswerId !== null) { // Jeśli użytkownik odpowiedział
                    const selectedLabel = questionCard.querySelector(`input[value="${selectedAnswerId}"]`)?.closest('.quiz-card__answer');
                    if (selectedAnswerId === correctAnswer.id) {
                        selectedLabel?.classList.add('correct');
                    } else {
                        selectedLabel?.classList.add('incorrect');
                        correctLabel?.classList.add('correct');
                    }
                } else { // Jeśli użytkownik pominął pytanie
                    correctLabel?.classList.add('missed');
                }
            }

            // Logika dodawania przycisku wyjaśnienia (bez zmian)
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

        resultsDetailsContainer.querySelectorAll('.quiz-card__answer.selected').forEach(label => {
            label.classList.remove('selected');
        });

        // Znajdź kontener na przyciski akcji na ekranie wyników.
        const actionsContainer = resultsScreen.querySelector('.results-container__actions');
        
        // Sprawdź, czy przycisk już nie istnieje, aby uniknąć duplikatów
        if (actionsContainer && !actionsContainer.querySelector('#solve-again-btn')) {
            // Stwórz przycisk "Rozwiąż ponownie"
            const solveAgainButton = document.createElement('button');
            solveAgainButton.id = 'solve-again-btn';
            solveAgainButton.className = 'btn btn--secondary'; // Używamy innej klasy dla odróżnienia
            solveAgainButton.textContent = 'Rozwiąż ponownie';

            // Dodaj nasłuchiwanie na kliknięcie, które odświeży stronę
            solveAgainButton.addEventListener('click', () => {
                location.reload(); 
            });

            // Dodaj przycisk do kontenera
            actionsContainer.appendChild(solveAgainButton);
        }

        window.scrollTo(0, 0);
    }

    // ... (reszta pliku: nasłuchiwacze i funkcje pomocnicze bez zmian)
    
    questionsWrapper.addEventListener('click', (event) => {
        const answerLabel = event.target.closest('.quiz-card__answer');
        if (!answerLabel) return;
        const currentQuestionCard = answerLabel.closest('.quiz-card');
        if (!currentQuestionCard) return;
        currentQuestionCard.querySelectorAll('.quiz-card__answer').forEach(label => label.classList.remove('selected'));
        answerLabel.classList.add('selected');
    });

    finishBtn.addEventListener('click', finishTest);

    function startTimer(duration) {
        let timeLeft = duration;
        timerInterval = setInterval(() => {
            timeSpent++;
            timeLeft = duration - timeSpent;
            if (timeLeft < 0) timeLeft = 0;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                finishTest();
            }
        }, 1000);
    }
    
    function showError(message) {
        loadingScreen.innerHTML = `<h2>Wystąpił błąd</h2><p>${message}</p>`;
    }

    initializeTest();
});