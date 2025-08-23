/**
 * @file /features/one-question/index.js
 * @module one-question-quiz
 * @description
 * Główny skrypt sterujący logiką interaktywnego quizu w trybie "jedno pytanie".
 * Działa jako **Kontroler** w architekturze frontendowej, który orkiestruje
 * komunikację między modelem (`ApiClient`) a widokiem (`UiHandler`),
 * zarządzając całym stanem i przepływem aplikacji.
 *
 * ## Wzorce i Architektura
 *
 * - Kontroler: Centralny punkt logiki, który reaguje na akcje użytkownika.
 * - Event Delegation:** Efektywnie obsługuje zdarzenia na dynamicznie
 *   tworzonych elementach.
 * - Zależności: Wstrzykuje i wykorzystuje usługi `ApiClient` i `UiHandler`.
 *
 * @version 1.6.0
 * @author Tobiasz Szerszeń
 */

import api from '../../modules/ApiClient.js';
import ui from '../../modules/UiHandler.js';
import Toast from '../../components/Toast.js';

class Quiz {
  /**
   * Wyszukuje kluczowe elementy DOM i dane inicjalizacyjne, a następnie
   * uruchamia logikę quizu.
   * @constructs Quiz
   */
  constructor() {
    // Krok 1: Wyszukaj i przypisz kluczowe elementy DOM.
    this.pageContainer = document.getElementById('quiz-single-question');
    this.topicForm = document.getElementById('topic-form');
    this.quizContainer = document.getElementById('quiz-container');

    // Krok 2: Pobierz dane konfiguracyjne z atrybutów HTML.
    this.examCode = this.pageContainer?.dataset.examCode || null;

    // Krok 3: Zainicjuj wewnętrzny stan.
    this.currentExplanation = null;
    this.loaderTimer = null;

    // Krok 4: Sprawdź, czy wszystko jest na miejscu i uruchom logikę.
    if (this.topicForm && this.quizContainer && this.examCode) {
      this.init();
    } else {
      console.error('Błąd inicjalizacji quizu: brak kluczowych elementów DOM lub atrybutu data-exam-code.');
    }
  }

  /**
   * Wiąże główne nasłuchiwacze zdarzeń z elementami DOM.
   * @private
   */
  init() {
    // Krok 1: Nasłuchuj na wysłanie formularza z tematami.
    this.topicForm.addEventListener('submit', this.handleTopicSubmit.bind(this));
    // Krok 2: Użyj delegacji zdarzeń do obsługi kliknięć wewnątrz kontenera quizu.
    // Jest to wydajniejsze niż dodawanie wielu listenerów do dynamicznych elementów.
    this.quizContainer.addEventListener('click', this.handleInteraction.bind(this));
  }

  /**
   * Obsługuje wysłanie formularza z kategoriami.
   * @private
   * @async
   * @param {Event} event - Obiekt zdarzenia 'submit'.
   */
  async handleTopicSubmit(event) {
    // Krok 1: Zatrzymaj domyślną akcję przeładowania strony przez formularz.
    event.preventDefault();
    // Krok 2: Rozpocznij proces ładowania nowego pytania.
    await this.startNewQuestion();
  }

  /**
   * Główna metoda rozpoczynająca ładowanie nowego pytania.
   * @private
   * @async
   */
  async startNewQuestion() {
    // Krok 1: Zresetuj stan dla nowego pytania.
    this.currentExplanation = null;

    // Krok 2: Zbierz dane z formularza (wybrane tematy i opcje premium).
    const selectedSubjects = [...this.topicForm.querySelectorAll('input[name="subject[]"]:checked')]
      .map(cb => cb.value);
    const premiumOption = this.topicForm.querySelector('input[name="premium_option"]:checked')?.value || null;

    // Krok 3: Walidacja - sprawdź, czy użytkownik wybrał jakikolwiek temat.
    if (selectedSubjects.length === 0) {
      Toast.show('Wybierz przynajmniej jedną kategorię, aby rozpocząć naukę.', 'info');
      return;
    }

    try {
      // Krok 4: Ustaw opóźniony wskaźnik ładowania dla lepszego UX.
      // Pokaże się tylko, jeśli API będzie odpowiadać dłużej niż 300ms.
      this.loaderTimer = setTimeout(() => {
        ui.showLoader(this.quizContainer);
      }, 300);

      // Krok 5: Wywołaj API, aby pobrać dane pytania.
      const result = await api.fetchQuestion(this.examCode, selectedSubjects, premiumOption);

      // Krok 6: Przetwórz odpowiedź z API.
      if (result.success) {
        const data = result.data;
        if (data.status === 'no_questions_left') {
          // Scenariusz A: Brak dostępnych pytań.
          Toast.show(data.message, 'info');
          this.quizContainer.innerHTML = '<p class="quiz-placeholder">Wybierz inne kryteria, aby kontynuować naukę.</p>';
        } else {
          // Scenariusz B: Pytanie pobrane pomyślnie.
          this.currentExplanation = data.question.explanation;
          ui.renderQuestion(this.quizContainer, data.question, data.answers);
        }
      } else {
        // Scenariusz C: API zwróciło błąd.
        Toast.show(result.error, 'error');
      }
    } catch (error) {
      // Scenariusz D: Wystąpił błąd sieciowy lub inny krytyczny.
      Toast.show('Wystąpił krytyczny błąd. Spróbuj ponownie.', 'error');
    } finally {
      // Krok 7: Zawsze anuluj timer. To kluczowe, aby loader nie pojawił
      // się już PO załadowaniu treści (jeśli API odpowiedziało szybko).
      clearTimeout(this.loaderTimer);
    }
  }

  /**
   * Centralny handler dla kliknięć wewnątrz kontenera quizu (delegacja zdarzeń).
   * @private
   * @param {Event} event - Obiekt zdarzenia 'click'.
   */
  handleInteraction(event) {
    // Krok 1: Sprawdź, czy celem kliknięcia była etykieta odpowiedzi.
    const clickedLabel = event.target.closest('.quiz-card__answer');

    if (clickedLabel) {
      // Krok 2: Sprawdź, czy na to pytanie już odpowiedziano (ochrona przed wielokrotnym sprawdzaniem).
      const answersContainer = clickedLabel.closest('.quiz-card__answers');
      if (answersContainer?.dataset.answered === 'true') {
        return; // Ignoruj kliknięcie.
      }
      // Krok 3: Jeśli wszystko się zgadza, przekaż kontrolę do metody sprawdzającej.
      this.checkSelectedAnswer(clickedLabel);
    }
  }

  /**
   * Sprawdza poprawność wybranej odpowiedzi i zarządza dalszym przepływem.
   * @private
   * @async
   * @param {HTMLElement} clickedLabel - Etykieta odpowiedzi, która została kliknięta.
   */
  async checkSelectedAnswer(clickedLabel) {
    // Krok 1: Zablokuj możliwość ponownej odpowiedzi na to samo pytanie.
    const answersContainer = clickedLabel.closest('.quiz-card__answers');
    answersContainer.dataset.answered = 'true';

    // Krok 2: Zbierz potrzebne dane z DOM.
    const userAnswerId = clickedLabel.querySelector('input[type="radio"]').value;
    const questionId = document.getElementById('question_id_hidden').value;

    // Krok 3: Wywołaj API, aby zweryfikować odpowiedź.
    const result = await api.checkAnswer(questionId, userAnswerId);

    // Krok 4: Przetwórz wynik weryfikacji.
    if (result.success) {
      const data = result.data;
      // 4a. Zleć modułowi UI pokazanie feedbacku (zielony/czerwony kolor).
      ui.showAnswerFeedback(data.is_correct, data.correct_answer_id, userAnswerId);
      // 4b. Zleć UI renderowanie przycisków, przekazując jako callback metodę
      // `startNewQuestion`, która uruchomi się po kliknięciu "Następne pytanie".
      ui.renderActionButtons(this.currentExplanation, this.startNewQuestion.bind(this));
    } else {
      // W razie błędu API, odblokuj możliwość ponownej odpowiedzi i pokaż błąd.
      Toast.show('Wystąpił nieoczekiwany błąd.', 'error');
      delete answersContainer.dataset.answered;
    }
  }
}

/**
 * --- Punkt Startowy Aplikacji ---
 *
 * Po pełnym załadowaniu struktury DOM, tworzy nową instancję klasy Quiz,
 * inicjując w ten sposób całą logikę aplikacji.
 */
document.addEventListener('DOMContentLoaded', () => {
  new Quiz();
});