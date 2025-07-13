// Plik: public/js/modules/ui.js

/**
 * Pomocnicza funkcja do unikania XSS.
 * @param {string} str - Tekst do "oczyszczenia".
 * @returns {string} - Bezpieczny tekst HTML.
 */
function escapeHTML(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Renderuje pytanie i odpowiedzi w kontenerze.
 * @param {HTMLElement} container - Kontener, w którym ma być renderowany quiz.
 * @param {Object} question - Obiekt pytania.
 * @param {Array<Object>} answers - Tablica obiektów odpowiedzi.
 */
export function renderQuestion(container, question, answers) {
    let answersHTML = '';
    answers.forEach((answer, index) => {
        const letter = String.fromCharCode(65 + index);
        answersHTML += `
            <label class="answer-label">
                <input type="radio" name="answer" value="${answer.id}">
                <span class="answer-prefix">${letter}</span>
                <span class="answer-text">${escapeHTML(answer.content)}</span>
            </label>
        `;
    });

    container.innerHTML = `
        <section id="question-block">
            <p><strong>${escapeHTML(question.content)}</strong></p>
            <div id="answers-container">${answersHTML}</div>
            <div id="feedback-and-actions-container"></div>
            <input type="hidden" id="question_id_hidden" value="${question.id}">
        </section>
    `;
}

/**
 * Wyświetla feedback (koloruje odpowiedzi).
 * @param {boolean} isCorrect - Czy odpowiedź była poprawna.
 * @param {number|string} correctAnswerId - ID poprawnej odpowiedzi.
 * @param {number|string} userAnswerId - ID odpowiedzi wybranej przez użytkownika.
 */
export function showAnswerFeedback(isCorrect, correctAnswerId, userAnswerId) {
    const radioButtons = document.querySelectorAll('input[name="answer"]');
    radioButtons.forEach(radio => {
        const label = radio.closest('.answer-label');
        if (label) {
            if (!isCorrect && radio.value == userAnswerId) {
                label.classList.add('incorrect');
            }
            if (radio.value == correctAnswerId) {
                label.classList.add('correct');
            }
        }
    });
}

/**
 * Tworzy i wyświetla przyciski akcji (Następne, Wyjaśnienie).
 * @param {string|null} explanation - Tekst wyjaśnienia.
 * @param {function} onNextClick - Funkcja do wywołania po kliknięciu "Następne".
 */
export function renderActionButtons(explanation, onNextClick) {
    const actionsContainer = document.getElementById('feedback-and-actions-container');
    actionsContainer.innerHTML = '';

    const buttonContainer = document.createElement('div');
    buttonContainer.style.marginTop = '25px';

    const explanationContainer = document.createElement('div');
    explanationContainer.id = 'explanation-container';
    
    if (explanation && explanation.trim() !== '') {
        explanationContainer.innerHTML = escapeHTML(explanation);
        const explanationButton = document.createElement('button');
        explanationButton.type = 'button';
        explanationButton.textContent = 'Pokaż wyjaśnienie';
        explanationButton.className = 'quiz-button quiz-button--secondary';
        explanationButton.style.marginRight = '10px';
        explanationButton.addEventListener('click', () => {
            explanationContainer.classList.toggle('visible');
            explanationButton.textContent = explanationContainer.classList.contains('visible') 
                ? 'Ukryj wyjaśnienie' 
                : 'Pokaż wyjaśnienie';
        });
        buttonContainer.appendChild(explanationButton);
    }

    const nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.textContent = 'Następne';
    nextButton.className = 'quiz-button quiz-button--primary';
    nextButton.addEventListener('click', onNextClick);
    buttonContainer.appendChild(nextButton);

    actionsContainer.appendChild(buttonContainer);
    actionsContainer.appendChild(explanationContainer);
}

/**
 * Wyświetla komunikat o ładowaniu.
 * @param {HTMLElement} container - Kontener, w którym ma się pojawić komunikat.
 */
export function showLoading(container) {
    container.innerHTML = '<p>Ładowanie pytania...</p>';
}

/**
 * Wyświetla komunikat o błędzie.
 * @param {HTMLElement} container - Kontener, w którym ma się pojawić komunikat.
 * @param {string} message - Treść błędu.
 */
export function showError(container, message) {
    container.innerHTML = `<p style="color: red;">Błąd: ${message}</p>`;
}