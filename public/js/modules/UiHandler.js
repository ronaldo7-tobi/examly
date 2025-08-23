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
    this.IMAGE_BASE_PATH = '/examly/public/images/questions/';
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
      const unsafeHTML = marked.parse(answer.content, { gfm: true, breaks: true });
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

  /**
   * Renderuje przyciski akcji po udzieleniu odpowiedzi, używając wzorca "callback".
   *
   * @param {?string} explanation - Tekst wyjaśnienia do pytania (jeśli istnieje).
   * @param {Function} onNextClick - Funkcja zwrotna (callback) do wykonania po
   * kliknięciu przycisku "Następne pytanie".
   */
  renderActionButtons(explanation, onNextClick) {
    // Krok 1: Znajdź kontenery na przyciski i wyjaśnienia.
    const buttonContainer = document.querySelector('.quiz-card__button-container');
    const explanationContainer = document.querySelector('.quiz-card__explanation');
    if (!buttonContainer || !explanationContainer) return;

    // Krok 2: Wyczyść poprzednią zawartość, aby przygotować miejsce na nowe elementy.
    buttonContainer.innerHTML = '';
    explanationContainer.innerHTML = '';
    explanationContainer.classList.remove('quiz-card__explanation--visible');

    // Krok 3: Jeśli istnieje wyjaśnienie, stwórz i dodaj przycisk "Pokaż wyjaśnienie".
    if (explanation?.trim()) {
      explanationContainer.innerHTML = DOMPurify.sanitize(marked.parse(explanation));
      const expButton = document.createElement('button');
      expButton.type = 'button';
      expButton.textContent = 'Pokaż wyjaśnienie';
      expButton.className = 'btn btn--secondary';
      expButton.style.marginRight = '10px';

      expButton.addEventListener('click', () => {
        const isVisible = explanationContainer.classList.toggle('quiz-card__explanation--visible');
        expButton.textContent = isVisible ? 'Ukryj wyjaśnienie' : 'Pokaż wyjaśnienie';
      });
      buttonContainer.appendChild(expButton);
    }

    // Krok 4: Stwórz przycisk "Następne pytanie" i przypisz do niego logikę z kontrolera (callback).
    const nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.textContent = 'Następne pytanie';
    nextButton.className = 'btn btn--primary';
    nextButton.addEventListener('click', onNextClick);
    buttonContainer.appendChild(nextButton);
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