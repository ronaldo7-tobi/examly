// Plik: public/js/modules/ui.js
import { IMAGE_BASE_PATH } from './api.js';

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
 * @param {Object} question - Obiekt pytania (może zawierać właściwość 'image').
 * @param {Array<Object>} answers - Tablica obiektów odpowiedzi.
 */
export function renderQuestion(container, question, answers) {
    let answersHTML = '';
    answers.forEach((answer, index) => {
        // ... (ta część pozostaje bez zmian) ...
        const letter = String.fromCharCode(65 + index);
        answersHTML += `
            <label class="quiz-card__answer">
                <input type="radio" name="answer" value="${answer.id}">
                <span class="quiz-card__answer-prefix">${letter}</span>
                <span class="quiz-card__answer-text">${escapeHTML(answer.content)}</span>
            </label>
        `;
    });
    
    // ZMIANA: Tworzymy zmienną na HTML obrazka
    let imageHTML = '';
    // Sprawdzamy, czy obiekt pytania zawiera niepustą właściwość 'image'
    if (question.image && question.image.trim() !== '') {
        imageHTML = `
            <div class="quiz-card__image-container">
                <img src="${IMAGE_BASE_PATH}${escapeHTML(question.image)}" alt="Ilustracja do pytania" class="quiz-card__image">
            </div>
        `;
    }

    container.innerHTML = `
        <section class="quiz-card">
            <p class="quiz-card__question-text">${escapeHTML(question.content)}</p>
            ${imageHTML}
            <div class="quiz-card__answers">${answersHTML}</div>
            <div class="quiz-card__actions">
                <!-- Te kontenery zostają puste, JS je wypełni -->
                <div class="quiz-card__button-container"></div>
                <div class="quiz-card__explanation"></div>
            </div>
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
    // Używamy querySelectorAll, aby znaleźć wszystkie elementy pasujące do selektora
    const answerLabels = document.querySelectorAll('.quiz-card__answer');
    
    answerLabels.forEach(label => {
        // Pobieramy input radio, który jest wewnątrz tej etykiety
        const radioInput = label.querySelector('input[type="radio"]');
        if (!radioInput) return; // Zabezpieczenie

        // Sprawdzamy, czy to jest błędna odpowiedź, którą zaznaczył użytkownik
        if (!isCorrect && radioInput.value == userAnswerId) {
            label.classList.add('quiz-card__answer--incorrect');
        }
        
        // Zawsze zaznaczamy poprawną odpowiedź na zielono
        if (radioInput.value == correctAnswerId) {
            label.classList.add('quiz-card__answer--correct');
        }
    });
}

/**
 * Tworzy i wyświetla przyciski akcji (Następne, Wyjaśnienie).
 * @param {string|null} explanation - Tekst wyjaśnienia.
 * @param {function} onNextClick - Funkcja do wywołania po kliknięciu "Następne".
 */
export function renderActionButtons(explanation, onNextClick) {
    // 1. Znajdź istniejące kontenery, które zostały stworzone przez renderQuestion
    const buttonContainer = document.querySelector('.quiz-card__button-container');
    const explanationContainer = document.querySelector('.quiz-card__explanation');

    if (!buttonContainer || !explanationContainer) {
        console.error('Nie znaleziono kontenerów na przyciski lub wyjaśnienie!');
        return;
    }

    // 2. Wyczyść ich zawartość, a nie całego rodzica
    buttonContainer.innerHTML = '';
    explanationContainer.innerHTML = '';
    explanationContainer.classList.remove('quiz-card__explanation--visible'); // Resetuj widoczność

    // Logika tworzenia przycisku 'Wyjaśnienie'
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

    // Logika tworzenia przycisku 'Następne'
    const nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.textContent = 'Następne';
    nextButton.className = 'btn btn--primary';
    nextButton.addEventListener('click', onNextClick);
    buttonContainer.appendChild(nextButton);
}

/**
 * Wyświetla komunikat o błędzie.
 * @param {HTMLElement} container - Kontener, w którym ma się pojawić komunikat.
 * @param {string} message - Treść błędu.
 */
export function showError(container, message) {
    container.innerHTML = `<p class="alert alert--error";">Błąd: ${message}</p>`;
}

/**
 * Wyświetla komunikat informacyjny. (NOWA FUNKCJA)
 * @param {HTMLElement} container - Kontener, w którym ma się pojawić komunikat.
 * @param {string} message - Treść informacji.
 */
export function showInfo(container, message) {
    container.innerHTML = `<div class="alert alert--info">${escapeHTML(message)}</div>`;
}