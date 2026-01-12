/**
 * @file UiHandler.js
 * @module UiHandler
 * @description
 * Moduł ten hermetyzuje całą logikę odpowiedzialną za manipulację i renderowanie
 * elementów DOM. Działa jako dedykowana warstwa "Widoku" (View), która jest
 * wywoływana przez kontrolery (np. z `TestRunner`). Oddziela logikę
 * prezentacji od logiki biznesowej aplikacji.
 *
 * ## Wzorce i Zależności
 *
 * - **Singleton:** Moduł eksportuje jedną, gotową do użycia instancję `UIHandler`.
 * - **Separation of Concerns:** Odpowiada wyłącznie za DOM, nie zawiera logiki aplikacji.
 * - **Zależności zewnętrzne:** Wymaga globalnie dostępnych bibliotek `marked` i `DOMPurify`.
 *
 * ## Przykład Użycia
 *
 * ```javascript
 * import ui from './UiHandler.js';
 *
 * const container = document.getElementById('quiz-container');
 * const questionData = { ... };
 * const answersData = [ ... ];
 *
 * ui.renderQuestion(container, questionData, answersData);
 * ```
 *
 * @version 1.4.0
 * @author Tobiasz Szerszeń
 */

import { escapeHTML } from '../utils/sanitize.js';

class UIHandler {
  /**
   * @constructs UIHandler
   */
  constructor() {
    /**
     * Bazowa ścieżka do obrazków dołączanych do pytań.
     * @private
     * @type {string}
     */
    this.IMAGE_BASE_PATH = `${window.examlyAppState.baseUrl}/images/questions/`;
  }

  /**
   * Renderuje kompletną kartę pytania wewnątrz podanego kontenera.
   * Metoda zawiera krytyczną logikę bezpieczeństwa (XSS protection).
   *
   * @param {HTMLElement} container - Element DOM, w którym ma być wyrenderowane pytanie.
   * @param {object} question - Obiekt z danymi pytania (`id`, `content`, `image_path`).
   * @param {Array<object>} answers - Tablica obiektów z danymi odpowiedzi.
   */
  renderQuestion(container, question, answers) {
    // Krok 1: Wygeneruj HTML dla wszystkich odpowiedzi w pętli.
    let answersHTML = '';
    answers.forEach((answer, index) => {
      // 1a. Sparsuj treść odpowiedzi z formatu Markdown na HTML.
      const unsafeHTML = marked.parse(answer.answer_text, { gfm: true, breaks: true });
      // 1b. Zabezpiecz wygenerowany HTML przed atakami XSS. To jest krytyczny krok!
      const contentHTML = DOMPurify.sanitize(unsafeHTML);
      const letter = String.fromCharCode(65 + index);

      // 1c. Zbuduj i dołącz szablon HTML dla pojedynczej odpowiedzi.
      answersHTML += `
        <label class="quiz-card__answer">
          <input type="radio" name="answer" value="${answer.id}">
          <span class="quiz-card__answer-prefix">${letter}</span>
          <span class="quiz-card__answer-text">${contentHTML}</span>
        </label>`;
    });

    // Krok 2: Wygeneruj HTML dla obrazka, jeśli istnieje.
    const imageHTML = question.image_path?.trim()
      ? `<div class="quiz-card__image-container">
           <img src="${this.IMAGE_BASE_PATH}${escapeHTML(question.image_path)}"
                alt="Ilustracja do pytania" class="quiz-card__image">
         </div>`
      : '';

    // Krok 3: Zbuduj finalny szablon całej karty pytania i wstaw go do kontenera.
    container.innerHTML = `
      <section class="quiz-card">
        <p class="quiz-card__question-text">${escapeHTML(question.question_text)}</p>
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
   * Wyświetla wizualny feedback dla odpowiedzi (koloruje na zielono/czerwono).
   *
   * @param {boolean} isCorrect - Czy odpowiedź użytkownika była poprawna.
   * @param {number|string} correctAnswerId - ID poprawnej odpowiedzi.
   * @param {number|string} userAnswerId - ID odpowiedzi wybranej przez użytkownika.
   */
  showAnswerFeedback(isCorrect, correctAnswerId, userAnswerId) {
    // Krok 1: Znajdź wszystkie etykiety odpowiedzi na stronie.
    const answerLabels = document.querySelectorAll('.quiz-card__answer');

    // Krok 2: Przejdź przez każdą etykietę, aby dodać odpowiednie klasy CSS.
    answerLabels.forEach((label) => {
      const radioInput = label.querySelector('input[type="radio"]');
      if (!radioInput) return;

      // 2a. Zawsze oznacz poprawną odpowiedź na zielono.
      if (radioInput.value == correctAnswerId) {
        label.classList.add('quiz-card__answer--correct');
      }

      // 2b. Jeśli użytkownik odpowiedział błędnie, oznacz jego wybór na czerwono.
      if (!isCorrect && radioInput.value == userAnswerId) {
        label.classList.add('quiz-card__answer--incorrect');
      }
    });
  }

  // Metoda renderActionButtons
  renderActionButtons(explanation, nextCallback) {
    const actionContainer = document.querySelector('.quiz-card__actions');
    if (!actionContainer) return;

    actionContainer.innerHTML = `
      <div class="quiz-card__button-container"></div>
      <div class="quiz-card__explanation"></div>
    `;

    const btnContainer = actionContainer.querySelector('.quiz-card__button-container');
    const explContainer = actionContainer.querySelector('.quiz-card__explanation');

    // Sprawdzenie, czy wyjaśnienie nie jest puste
    if (explanation && explanation.trim() !== '') {
        const explBtn = document.createElement('button');
        explBtn.type = 'button';
        explBtn.className = 'btn btn--secondary btn--small';
        explBtn.textContent = 'Pokaż wyjaśnienie';
        
        explBtn.addEventListener('click', () => {
            const isVisible = explContainer.classList.toggle('quiz-card__explanation--visible');
            explBtn.textContent = isVisible ? 'Ukryj wyjaśnienie' : 'Pokaż wyjaśnienie';
        });

        // Parsowanie markdown dla wyjaśnienia
        explContainer.innerHTML = DOMPurify.sanitize(marked.parse(explanation));
        btnContainer.appendChild(explBtn);
    }

    // Przycisk "Następne"
    const nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.className = 'btn btn--primary';
    nextBtn.textContent = 'Następne pytanie';
    nextBtn.addEventListener('click', nextCallback);
    btnContainer.appendChild(nextBtn);
  }

  /**
   * Wyświetla wskaźnik ładowania (spinner) wewnątrz podanego kontenera.
   *
   * @param {HTMLElement} container - Element DOM, w którym ma być wyświetlony loader.
   */
  showLoader(container) {
    // Ta metoda jest na tyle prosta, że kroki są zbędne.
    // Po prostu zastępujemy zawartość kontenera szablonem HTML loadera.
    container.innerHTML = `
      <div id="loading-screen" class="test-loading">
        <h2>Trwa przygotowywanie Twojego pytania...</h2>
        <div class="spinner"></div>
      </div>`;
  }
}

/**
 * --- Eksport Instancji (Singleton) ---
 *
 * Eksportujemy jedną, gotową do użycia instancję klasy `UIHandler`.
 * Zapewnia to, że cała aplikacja korzysta z tego samego "silnika"
 * do renderowania interfejsu.
 */
const ui = new UIHandler();

export default ui;