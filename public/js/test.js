import { fetchFullTest, saveTestResult } from './modules/api.js';

document.addEventListener('DOMContentLoaded', () => {
    // === GŁÓWNY KONTENER I SPRAWDZENIE ===
    const testContainer = document.getElementById('test-container');
    if (!testContainer) {
        // Jeśli nie ma tego elementu, to nie jesteśmy na stronie testu.
        return; 
    }

    // === SELEKTORY POZOSTAŁYCH ELEMENTÓW DOM ===
    const loadingScreen = document.getElementById('loading-screen');
    const testView = document.getElementById('test-view');
    const questionsWrapper = document.getElementById('questions-wrapper');
    const timerElement = document.getElementById('timer');
    const questionCounterElement = document.getElementById('question-counter');
    const finishBtn = document.getElementById('finish-test-btn');
    const resultsScreen = document.getElementById('results-screen');
    
    // === ZMIENNE STANU ===
    const examCode = testContainer.dataset.examCode;
    let questionsData = []; // Przechowuje pytania i odpowiedzi z API
    let timerInterval = null; // Przechowuje referencję do interwału timera
    let timeSpent = 0;      // Liczba sekund, która upłynęła

    /**
     * Główna funkcja inicjalizująca test.
     */
    async function initializeTest() {
        if (!examCode) {
            showError('Błąd krytyczny: Brak zdefiniowanego kodu egzaminu (atrybut data-exam-code)!');
            return;
        }

        const response = await fetchFullTest(examCode);

        if (response.success && response.data.questions) {
            questionsData = response.data.questions;
            renderAllQuestions(questionsData);

            // Ukryj ładowanie i pokaż widok testu
            loadingScreen.classList.add('hidden');
            testView.classList.remove('hidden');
            
            startTimer(3600); // Rozpocznij odliczanie (60 minut)
        } else {
            showError(response.error || 'Nie udało się załadować pytań.');
        }
    }

    /**
     * Renderuje wszystkie pytania i odpowiedzi na stronie.
     */
    function renderAllQuestions(questions) {
        questionsWrapper.innerHTML = ''; // Wyczyść kontener na wszelki wypadek
        questions.forEach((qData, index) => {
            const question = qData.question;
            const answers = qData.answers;

            let answersHtml = '';
            answers.forEach(answer => {
                answersHtml += `
                    <label class="quiz-card__answer">
                        <input type="radio" name="question_${question.id}" value="${answer.id}">
                        <span class="quiz-card__answer-text">${answer.content}</span>
                    </label>
                `;
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
                <div class="quiz-card__explanation hidden"><p>${question.explanation || 'Brak wyjaśnienia dla tego pytania.'}</p></div>
            `;
            questionsWrapper.appendChild(questionElement);
        });
        questionCounterElement.textContent = `Pytanie 1 / ${questions.length}`;
    }

    /**
     * Uruchamia licznik czasu.
     */
    function startTimer(duration) {
        let timeLeft = duration;
        timerInterval = setInterval(() => {
            timeLeft--;
            timeSpent = duration - timeLeft;
            const minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds; // Dodaj zero wiodące
            timerElement.textContent = `${minutes}:${seconds}`;

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                finishTest(); // Automatycznie zakończ test, gdy czas się skończy
            }
        }, 1000);
    }
    
    /**
     * Zakańcza test, sprawdza odpowiedzi i wywołuje zapis wyniku.
     */
    async function finishTest() {
        clearInterval(timerInterval);

        let correctAnswersCount = 0;
        let topicIdsInTest = new Set();
        
        questionsData.forEach(qData => {
            topicIdsInTest.add(qData.question.topic_id);
            const questionId = qData.question.id;
            const correctAnswer = qData.answers.find(a => a.is_correct === 1);
            const selectedAnswerInput = questionsWrapper.querySelector(`input[name="question_${questionId}"]:checked`);
            
            if (selectedAnswerInput && correctAnswer && selectedAnswerInput.value == correctAnswer.id) {
                correctAnswersCount++;
            }
        });
        
        const score = questionsData.length > 0 ? Math.round((correctAnswersCount / questionsData.length) * 100) : 0;
        
        showResults(score, correctAnswersCount);

        const resultData = {
            score_percent: score,
            correct_answers: correctAnswersCount,
            total_questions: questionsData.length,
            duration_seconds: timeSpent,
            topic_ids: Array.from(topicIdsInTest)
        };

        const saveResponse = await saveTestResult(resultData);
        if (!saveResponse.success) {
            console.warn('Nie udało się zapisać wyniku (być może nie jesteś zalogowany):', saveResponse.error);
        }
    }

    /**
     * Pokazuje ekran wyników i aktualizuje wygląd pytań.
     */
    function showResults(score, correctCount) {
        testView.classList.add('hidden');
        resultsScreen.classList.remove('hidden');

        const durationMinutes = Math.floor(timeSpent / 60);
        const durationSeconds = timeSpent % 60;
        document.getElementById('score-percent').textContent = `${score}%`;
        document.getElementById('correct-count').textContent = `${correctCount} / ${questionsData.length}`;
        document.getElementById('duration').textContent = `${durationMinutes}:${durationSeconds < 10 ? '0' : ''}${durationSeconds}`;

        questionsData.forEach(qData => {
            const questionId = qData.question.id;
            const questionCard = document.getElementById(`question-${questionId}`);
            if (!questionCard) return;

            const correctAnswer = qData.answers.find(a => a.is_correct === 1);
            const selectedAnswerInput = questionCard.querySelector(`input[name="question_${questionId}"]:checked`);

            questionCard.querySelectorAll('.quiz-card__answer').forEach(label => {
                const input = label.querySelector('input');
                input.disabled = true; // Zablokuj wszystkie odpowiedzi
                if (correctAnswer && input.value == correctAnswer.id) {
                    label.classList.add('correct');
                }
                if (selectedAnswerInput && input.checked && selectedAnswerInput.value != correctAnswer?.id) {
                    label.classList.add('incorrect');
                }
            });

            const header = questionCard.querySelector('.quiz-card__header');
            if (header && !header.querySelector('.btn')) {
                const explanationBtn = document.createElement('button');
                explanationBtn.className = 'btn btn--info btn--small';
                explanationBtn.textContent = 'Wyjaśnienie';
                explanationBtn.onclick = () => questionCard.querySelector('.quiz-card__explanation').classList.toggle('hidden');
                header.appendChild(explanationBtn);
            }
        });

        document.getElementById('results-details').innerHTML = questionsWrapper.innerHTML;
        window.scrollTo(0, 0); // Przewiń na górę strony
    }
    
    /**
     * Prosta funkcja do pokazywania błędów na ekranie ładowania.
     */
    function showError(message) {
        loadingScreen.innerHTML = `<h2>Wystąpił błąd</h2><p>${message}</p>`;
    }
    
    // === NASŁUCHIWACZ I START ===
    finishBtn.addEventListener('click', finishTest);
    initializeTest();
});