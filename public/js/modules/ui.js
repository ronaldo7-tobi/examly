/**
 * @module ui
 * @description Moduł odpowiedzialny za renderowanie komponentów interfejsu użytkownika quizu.
 * Zawiera funkcje do wyświetlania pytań, odpowiedzi, przycisków i komunikatów.
 */

/**
 * Ścieżka bazowa do obrazków powiązanych z pytaniami.
 * @type {string}
 * @constant
 */
const IMAGE_BASE_PATH = '/examly/public/images/questions/';

/**
 * Zabezpiecza tekst przed atakami XSS poprzez zamianę znaków specjalnych na encje HTML.
 * @private
 * @param {string} str - Tekst do "oczyszczenia".
 * @returns {string} - Bezpieczny do wstawienia w HTML tekst.
 */
function escapeHTML(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Renderuje pełen widok pytania wraz z odpowiedziami w określonym kontenerze.
 * @param {HTMLElement} container - Element DOM, w którym ma być renderowany quiz.
 * @param {object} question - Obiekt pytania, np. `{id: 1, content: '...', image: 'img.png'}`.
 * @param {object[]} answers - Tablica obiektów odpowiedzi, np. `[{id: 1, content: '...'}, ...]`.
 * @returns {void}
 */
export function renderQuestion(container, question, answers) {
    let answersHTML = '';
    answers.forEach((answer, index) => {
        const letter = String.fromCharCode(65 + index);
        answersHTML += `
            <label class="quiz-card__answer">
                <input type="radio" name="answer" value="${answer.id}">
                <span class="quiz-card__answer-prefix">${letter}</span>
                <span class="quiz-card__answer-text">${escapeHTML(answer.content)}</span>
            </label>
        `;
    });
    
    let imageHTML = '';
    if (question.image_path && question.image_path.trim() !== '') {
        imageHTML = `
            <div class="quiz-card__image-container">
                <img src="${IMAGE_BASE_PATH}${escapeHTML(question.image_path)}" alt="Ilustracja do pytania" class="quiz-card__image">
            </div>
        `;
    }

    container.innerHTML = `
        <section class="quiz-card">
            <p class="quiz-card__question-text">${escapeHTML(question.content)}</p>
            ${imageHTML}
            <div class="quiz-card__answers">${answersHTML}</div>
            <div class="quiz-card__actions">
                <div class="quiz-card__button-container"></div>
                <div class="quiz-card__explanation"></div>
            </div>
            <input type="hidden" id="question_id_hidden" value="${question.id}">
        </section>
    `;
}

/**
 * Wyświetla wizualny feedback po udzieleniu odpowiedzi.
 * Koloruje odpowiedź użytkownika (na czerwono, jeśli błędna) i poprawną odpowiedź (na zielono).
 * @param {boolean} isCorrect - Czy odpowiedź użytkownika była poprawna.
 * @param {number|string} correctAnswerId - ID poprawnej odpowiedzi.
 * @param {number|string} userAnswerId - ID odpowiedzi wybranej przez użytkownika.
 * @returns {void}
 */
export function showAnswerFeedback(isCorrect, correctAnswerId, userAnswerId) {
    const answerLabels = document.querySelectorAll('.quiz-card__answer');
    
    answerLabels.forEach(label => {
        const radioInput = label.querySelector('input[type="radio"]');
        if (!radioInput) return;

        // Oznacz błędną odpowiedź użytkownika
        if (!isCorrect && radioInput.value == userAnswerId) {
            label.classList.add('quiz-card__answer--incorrect');
        }
        
        // Zawsze oznacz poprawną odpowiedź
        if (radioInput.value == correctAnswerId) {
            label.classList.add('quiz-card__answer--correct');
        }
    });
}

/**
 * Tworzy i wyświetla przyciski akcji (np. "Następne pytanie", "Pokaż wyjaśnienie")
 * po udzieleniu odpowiedzi przez użytkownika.
 * @param {string|null} explanation - Tekst wyjaśnienia pytania. Jeśli null, przycisk wyjaśnienia się nie pojawi.
 * @param {function} onNextClick - Funkcja (callback), która zostanie wywołana po kliknięciu przycisku "Następne".
 * @returns {void}
 */
export function renderActionButtons(explanation, onNextClick) {
    const buttonContainer = document.querySelector('.quiz-card__button-container');
    const explanationContainer = document.querySelector('.quiz-card__explanation');

    if (!buttonContainer || !explanationContainer) {
        console.error('Nie znaleziono kontenerów na przyciski lub wyjaśnienie!');
        return;
    }

    // Wyczyść kontenery przed dodaniem nowej zawartości
    buttonContainer.innerHTML = '';
    explanationContainer.innerHTML = '';
    explanationContainer.classList.remove('quiz-card__explanation--visible');

    // Renderuj przycisk "Pokaż wyjaśnienie", jeśli wyjaśnienie istnieje
    if (explanation && explanation.trim() !== '') {
        explanationContainer.innerHTML = escapeHTML(explanation);
        const explanationButton = document.createElement('button');
        explanationButton.type = 'button';
        explanationButton.textContent = 'Pokaż wyjaśnienie';
        explanationButton.className = 'btn btn--secondary';
        explanationButton.style.marginRight = '10px';
        
        explanationButton.addEventListener('click', () => {
            explanationContainer.classList.toggle('quiz-card__explanation--visible');
            explanationButton.textContent = explanationContainer.classList.contains('quiz-card__explanation--visible') 
                ? 'Ukryj wyjaśnienie' 
                : 'Pokaż wyjaśnienie';
        });
        
        buttonContainer.appendChild(explanationButton);
    }

    // Zawsze renderuj przycisk "Następne pytanie"
    const nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.textContent = 'Następne';
    nextButton.className = 'btn btn--primary';
    nextButton.addEventListener('click', onNextClick);
    buttonContainer.appendChild(nextButton);
}

/**
 * Wyświetla sformatowany komunikat o błędzie w danym kontenerze.
 * @param {HTMLElement} container - Element DOM, w którym ma się pojawić komunikat.
 * @param {string} message - Treść komunikatu o błędzie.
 * @returns {void}
 */
export function showError(container, message) {
    container.innerHTML = `<p class="alert alert--error";">Błąd: ${escapeHTML(message)}</p>`;
}

/**
 * Wyświetla sformatowany komunikat informacyjny w danym kontenerze.
 * @param {HTMLElement} container - Element DOM, w którym ma się pojawić komunikat.
 * @param {string} message - Treść komunikatu informacyjnego.
 * @returns {void}
 */
export function showInfo(container, message) {
    container.innerHTML = `<div class="alert alert--info">${escapeHTML(message)}</div>`;
}