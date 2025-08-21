/**
 * @module ui
 * @version 1.2.0
 * @description Moduł ten hermetyzuje całą logikę odpowiedzialną za manipulację
 * i renderowanie elementów DOM. Działa jako warstwa "widoku", która jest
 * wywoływana przez kontrolery (np. z modułu `quiz`). Eksportuje jedną,
 * gotową do użycia instancję `UIHandler` (wzorzec Singleton).
 */

import { escapeHTML } from '../../utils/sanitize.js';

/**
 * @class UIHandler
 * @classdesc Zestaw metod do renderowania komponentów UI, takich jak karty pytań,
 * przyciski akcji, czy komunikaty dla użytkownika.
 * @property {string} IMAGE_BASE_PATH - Stała przechowująca bazową ścieżkę do obrazków pytań.
 */
class UIHandler {
    /**
     * @constructs UIHandler
     * @description Inicjalizuje handlera, ustawiając niezbędne stałe.
     */
    constructor() {
        this.IMAGE_BASE_PATH = '/examly/public/images/questions/';
    }

    /**
     * Renderuje kompletną kartę pytania wewnątrz podanego kontenera.
     * @param {HTMLElement} container - Element DOM, w którym ma być wyrenderowane pytanie.
     * @param {object} question - Obiekt z danymi pytania (id, content, image_path).
     * @param {Array<object>} answers - Tablica obiektów z danymi odpowiedzi.
     * @returns {void}
     */
    renderQuestion(container, question, answers) {
        let answersHTML = '';
        answers.forEach((answer, index) => {
            const letter = String.fromCharCode(65 + index); // A, B, C, D...
            answersHTML += `
                <label class="quiz-card__answer">
                    <input type="radio" name="answer" value="${answer.id}">
                    <span class="quiz-card__answer-prefix">${letter}</span>
                    <span class="quiz-card__answer-text">${escapeHTML(answer.content)}</span>
                </label>`;
        });
        
        const imageHTML = (question.image_path && question.image_path.trim() !== '') ? `
            <div class="quiz-card__image-container">
                <img src="${this.IMAGE_BASE_PATH}${escapeHTML(question.image_path)}" alt="Ilustracja do pytania" class="quiz-card__image">
            </div>` : '';

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
            </section>`;
    }

    /**
     * Wyświetla wizualny feedback dla odpowiedzi (poprawna/błędna).
     * @param {boolean} isCorrect - Czy odpowiedź użytkownika była poprawna.
     * @param {(number|string)} correctAnswerId - ID poprawnej odpowiedzi.
     * @param {(number|string)} userAnswerId - ID odpowiedzi wybranej przez użytkownika.
     * @returns {void}
     */
    showAnswerFeedback(isCorrect, correctAnswerId, userAnswerId) {
        const answerLabels = document.querySelectorAll('.quiz-card__answer');
        
        answerLabels.forEach(label => {
            const radioInput = label.querySelector('input[type="radio"]');
            if (!radioInput) return;

            // Oznacz błędną odpowiedź użytkownika na czerwono
            if (!isCorrect && radioInput.value == userAnswerId) {
                label.classList.add('quiz-card__answer--incorrect');
            }
            
            // Zawsze oznacz poprawną odpowiedź na zielono
            if (radioInput.value == correctAnswerId) {
                label.classList.add('quiz-card__answer--correct');
            }
        });
    }

    /**
     * Renderuje przyciski akcji (np. "Następne pytanie") po udzieleniu odpowiedzi.
     * Kluczową funkcją jest obsługa callbacku `onNextClick`, który pozwala
     * kontrolerowi zadecydować, co się stanie po kliknięciu przycisku.
     * @param {?string} explanation - Tekst wyjaśnienia do pytania (jeśli istnieje).
     * @param {Function} onNextClick - Funkcja zwrotna (callback) do wykonania po kliknięciu "Następne pytanie".
     * @returns {void}
     */
    renderActionButtons(explanation, onNextClick) {
        const buttonContainer = document.querySelector('.quiz-card__button-container');
        const explanationContainer = document.querySelector('.quiz-card__explanation');

        if (!buttonContainer || !explanationContainer) {
            console.error('Nie znaleziono kontenerów na przyciski lub wyjaśnienie!');
            return;
        }

        // Czyszczenie poprzednich przycisków i wyjaśnień
        buttonContainer.innerHTML = '';
        explanationContainer.innerHTML = '';
        explanationContainer.classList.remove('quiz-card__explanation--visible');

        // Renderowanie przycisku wyjaśnienia (jeśli jest dostępne)
        if (explanation && explanation.trim() !== '') {
            explanationContainer.innerHTML = `<p>${escapeHTML(explanation)}</p>`;
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

        // Renderowanie przycisku "Następne pytanie"
        const nextButton = document.createElement('button');
        nextButton.type = 'button';
        nextButton.textContent = 'Następne pytanie';
        nextButton.className = 'btn btn--primary';
        nextButton.addEventListener('click', onNextClick); // Przypisanie callbacku
        buttonContainer.appendChild(nextButton);
    }
}

/**
 * Singletonowa instancja handlera UI.
 * Tworzymy i eksportujemy jedną, gotową do użycia instancję,
 * aby cała aplikacja korzystała z tego samego obiektu do manipulacji UI.
 */
const ui = new UIHandler();

// Eksportujemy całą instancję jako domyślny eksport.
export default ui;