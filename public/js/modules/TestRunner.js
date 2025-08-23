/**
 * @file TestRunner.js
 * @module TestRunner
 * @description
 * Moduł dostarcza reużywalną klasę `TestRunner`, która jest generycznym
 * "silnikiem" do przeprowadzania testów. Został zaprojektowany zgodnie z zasadą
 * wstrzykiwania zależności (Dependency Injection), co oznacza, że nie wie nic
 * o otaczającym go świecie – wszystkie potrzebne elementy (przyciski, kontenery)
 * otrzymuje z zewnątrz. To czyni go ekstremalnie elastycznym i łatwym do
 * testowania.
 *
 * ## Wymagania i Zależności
 *
 * 1. Struktura HTML: Komponent wymaga istnienia w DOM elementów, których ID
 *    zostaną przekazane w obiekcie konfiguracyjnym do konstruktora.
 * 2. Biblioteki zewnętrzne: Skrypt polega na globalnie dostępnych
 *    bibliotekach `marked` i `DOMPurify` (do parsowania i czyszczenia HTML).
 * 3. API Client: Importuje moduł `ApiClient` do komunikacji z backendem.
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */

import api from './ApiClient.js';
import { escapeHTML } from '../utils/sanitize.js';

export class TestRunner {
  /**
   * Inicjalizuje instancję TestRunnera, wstrzykując wszystkie zależności.
   *
   * @constructs TestRunner
   * @param {object} config - Obiekt konfiguracyjny z zależnościami.
   * @param {HTMLElement} config.testView - Główny kontener widoku testu.
   * @param {HTMLElement} config.questionsWrapper - Kontener do renderowania pytań.
   * @param {HTMLElement} config.resultsScreen - Ekran wyników.
   * @param {HTMLElement} config.finishBtn - Przycisk "Zakończ test".
   * @param {HTMLElement} config.resultsDetailsContainer - Kontener na szczegółowe wyniki.
   * @param {boolean} [config.isFullExam=false] - Flaga, czy to pełny egzamin.
   */
  constructor(config) {
    // Krok 1: Przypisz wstrzyknięte zależności (elementy UI) do właściwości klasy.
    this.testView = config.testView;
    this.questionsWrapper = config.questionsWrapper;
    this.resultsScreen = config.resultsScreen;
    this.finishBtn = config.finishBtn;
    this.resultsDetailsContainer = config.resultsDetailsContainer;

    // Krok 2: Zainicjuj wewnętrzny stan komponentu.
    this.isFullExam = config.isFullExam || false;
    this.questionsData = [];
    this.timerInterval = null;
    this.timeSpent = 0;
    this.isTestInProgress = false;

    // Krok 3: Zapewnij prawidłowy kontekst `this` dla metod używanych jako event handlery.
    this.handleBeforeUnload = this.handleBeforeUnload.bind(this);
    this.finishTest = this.finishTest.bind(this);
    this.handleAnswerSelection = this.handleAnswerSelection.bind(this);
  }

  /**
   * Uruchamia wykonanie testu z podanym zestawem pytań.
   *
   * @param {Array<Object>} questions - Tablica obiektów z danymi pytań.
   */
  run(questions) {
    // Krok 1: Ustaw stan wewnętrzny testu.
    this.questionsData = questions;
    this.isTestInProgress = true;

    // Krok 2: Powiąż nasłuchiwacze zdarzeń.
    this.bindListeners();

    // Krok 3: Wyrenderuj pytania na stronie.
    this.renderAllQuestions(this.questionsData);

    // Krok 4: Uruchom zegar z czasem obliczonym na podstawie liczby pytań.
    const duration = this.questionsData.length * 90; // 1.5 minuty na pytanie
    this.startTimer(duration);
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
   * Zawiera krytyczną logikę bezpieczeństwa (ochrona przed XSS).
   *
   * @param {Array<Object>} questions - Tablica z danymi pytań.
   * @private
   */
  renderAllQuestions(questions) {
    // Krok 1: Wyczyść kontener przed renderowaniem nowych pytań.
    this.questionsWrapper.innerHTML = '';

    // Krok 2: Przejdź przez każde pytanie, aby stworzyć jego kartę.
    questions.forEach((qData, index) => {
      const { question, answers } = qData;
      let answersHTML = '';

      // Krok 2a: Wygeneruj HTML dla wszystkich odpowiedzi do danego pytania.
      answers.forEach((answer, answerIndex) => {
        const letter = String.fromCharCode(65 + answerIndex);
        const unsafeHTML = marked.parse(answer.content, { gfm: true, breaks: true });
        const contentHTML = DOMPurify.sanitize(unsafeHTML); // Ochrona XSS!

        answersHTML += `
          <label class="quiz-card__answer">
            <input type="radio" name="question_${question.id}" value="${answer.id}">
            <span class="quiz-card__answer-prefix">${letter}</span>
            <span class.quiz-card__answer-text">${contentHTML}</span>
          </label>`;
      });

      // Krok 2b: Stwórz główny element karty pytania.
      const questionElement = document.createElement('section');
      questionElement.className = 'quiz-card';
      questionElement.id = `question-${question.id}`;
      const imageHTML = question.image_path
        ? `<div class="quiz-card__image-container">
             <img src="/examly/public/images/questions/${escapeHTML(question.image_path)}"
                  alt="Ilustracja do pytania" class="quiz-card__image">
           </div>`
        : '';

      // Krok 2c: Wypełnij kartę szablonem HTML.
      questionElement.innerHTML = `
        <header class="quiz-card__header">
          <span class="quiz-card__question-number">Pytanie ${index + 1} / ${questions.length}</span>
        </header>
        <div class="quiz-card__content">
          <p class="quiz-card__question-text">${escapeHTML(question.content)}</p>
          ${imageHTML}
        </div>
        <div class="quiz-card__answers">${answersHTML}</div>
        <div class="quiz-card__actions">
          <div class="quiz-card__button-container"></div>
          <div class="quiz-card__explanation"></div>
        </div>`;

      // Krok 2d: Dodaj gotową kartę do głównego kontenera.
      this.questionsWrapper.appendChild(questionElement);
    });
  }

  /**
   * Finalizuje test, oblicza wyniki i zapisuje je w API.
   * @async
   */
  async finishTest() {
    // Krok 1: Zakończ stan "w toku" i wyczyść zegar oraz nasłuchiwacze.
    this.isTestInProgress = false;
    clearInterval(this.timerInterval);
    window.removeEventListener('beforeunload', this.handleBeforeUnload);

    // Krok 2: Zbierz i przetwórz odpowiedzi użytkownika.
    let correctAnswersCount = 0;
    const topicIdsInTest = new Set();
    const userAnswers = {};
    const progressData = [];

    this.questionsData.forEach((qData) => {
      topicIdsInTest.add(qData.question.topic_id);
      const questionId = qData.question.id;
      const correctAnswer = qData.answers.find((a) => a.is_correct === 1);
      const selectedInput = this.questionsWrapper.querySelector(`input[name="question_${questionId}"]:checked`);
      let isCorrect = false;

      if (selectedInput) {
        const selectedAnswerId = parseInt(selectedInput.value, 10);
        userAnswers[questionId] = selectedAnswerId;
        if (correctAnswer && selectedAnswerId === correctAnswer.id) {
          correctAnswersCount++;
          isCorrect = true;
        }
      } else {
        userAnswers[questionId] = null;
      }
      progressData.push({ questionId, isCorrect });
    });

    // Krok 3: Oblicz ostateczny wynik.
    const totalQuestions = this.questionsData.length;
    const baseScore = totalQuestions > 0 ? (correctAnswersCount / totalQuestions) * 100 : 0;
    const score = this.isFullExam ? baseScore : Math.round(baseScore);

    // Krok 4: Wyświetl ekran z wynikami.
    this.showResults(score, correctAnswersCount, userAnswers);

    // Krok 5: Jeśli użytkownik jest zalogowany, zapisz wyniki w API.
    if (!window.examlyAppState?.isUserLoggedIn) {
      return;
    }

    const resultData = {
      score_percent: score,
      correct_answers: correctAnswersCount,
      total_questions: totalQuestions,
      duration_seconds: this.timeSpent,
      topic_ids: Array.from(topicIdsInTest),
      is_full_exam: this.isFullExam,
    };

    // Używamy Promise.all, aby wysłać oba zapytania równolegle dla optymalizacji.
    await Promise.all([api.saveTestResult(resultData), api.saveBulkProgress(progressData)]);
  }

  /**
   * Buduje i wyświetla ekran wyników po zakończeniu testu.
   * @private
   * @param {number} score - Wynik testu w procentach.
   * @param {number} correctCount - Liczba poprawnych odpowiedzi.
   * @param {Object<string, number|null>} userAnswers - Mapa odpowiedzi użytkownika.
   */
  showResults(score, correctCount, userAnswers) {
    // Krok 1: Przełącz widoczność kontenerów.
    this.testView.classList.add('hidden');
    this.resultsScreen.classList.remove('hidden');

    // Krok 2: Zbuduj i wstaw HTML dla głównego podsumowania wyników.
    const scoreSummaryContainer = document.getElementById('score-summary');
    const scoreForAnimation = Math.round(score);
    const scoreToDisplay = this.isFullExam ? score.toFixed(2) : Math.round(score);

    scoreSummaryContainer.innerHTML = `
      <div class="score-circle" style="--score: ${scoreForAnimation}">
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
            <p class="stat-item__value" id="duration-value"></p>
          </div>
        </div>
      </div>`;

    // Krok 3: Oblicz i wstaw sformatowany czas ukończenia.
    const minutes = Math.floor(this.timeSpent / 60);
    const seconds = this.timeSpent % 60;
    document.getElementById('duration-value').textContent =
      `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

    // Krok 4: Stwórz i wstaw wiadomość o statystykach, zależną od stanu zalogowania.
    const statsInfo = document.createElement('p');
    statsInfo.className = 'results-container__stats-info';
    if (window.examlyAppState?.isUserLoggedIn) {
      statsInfo.innerHTML = `Świetna robota! Możesz śledzić swoje postępy i analizować wyniki
        w zakładce <a href="statistics">Statystyki</a>.`;
    } else {
      statsInfo.innerHTML = `Chcesz śledzić swoje postępy i zapisywać wyniki?
        <a href="register">Załóż darmowe konto</a>, aby odblokować statystyki!`;
    }
    scoreSummaryContainer.insertAdjacentElement('afterend', statsInfo);

    // Krok 5: Przygotuj szczegółowy podgląd odpowiedzi.
    // 5a. Skopiuj wyrenderowane pytania do kontenera wyników.
    this.resultsDetailsContainer.innerHTML = this.questionsWrapper.innerHTML;
    // 5b. Wyłącz wszystkie przyciski radio, aby uniemożliwić zmianę odpowiedzi.
    this.resultsDetailsContainer.querySelectorAll('input[type="radio"]').forEach((input) => (input.disabled = true));

    // 5c. Przejdź przez każdą kartę pytania, aby pokolorować odpowiedzi i dodać wyjaśnienia.
    this.questionsData.forEach((qData) => {
      const questionId = qData.question.id;
      const questionCard = this.resultsDetailsContainer.querySelector(`#question-${questionId}`);
      if (!questionCard) return;

      // Usuń wszystkie klasy .selected z tej karty pytania, aby uniknąć konfliktów stylów.
      questionCard.querySelectorAll('.quiz-card__answer.selected').forEach((label) => {
        label.classList.remove('selected');
      });

      const correctAnswer = qData.answers.find((a) => a.is_correct === 1);
      const selectedAnswerId = userAnswers[questionId];

      if (selectedAnswerId !== null) {
        const selectedInput = questionCard.querySelector(`input[value="${selectedAnswerId}"]`);
        selectedInput.checked = true; // Upewnij się, że odpowiedź jest zaznaczona.
      }

      // Pokoloruj etykiety odpowiedzi.
      const correctLabel = correctAnswer
        ? questionCard.querySelector(`input[value="${correctAnswer.id}"]`)?.closest('.quiz-card__answer')
        : null;
      const selectedLabel =
        selectedAnswerId !== null
          ? questionCard.querySelector(`input[value="${selectedAnswerId}"]`)?.closest('.quiz-card__answer')
          : null;

      if (selectedLabel) {
        selectedLabel.classList.add(selectedAnswerId === correctAnswer?.id ? 'correct' : 'incorrect');
      }
      if (correctAnswer && selectedAnswerId !== correctAnswer.id) {
        correctLabel?.classList.add(selectedAnswerId === null ? 'missed' : 'correct');
      }

      // Renderuj przycisk wyjaśnienia, jeśli jest dostępne.
      if (qData.question.explanation?.trim()) {
        this.renderExplanation(questionCard, qData.question.explanation);
      }
    });

    // Krok 6: Przewiń stronę na górę, aby użytkownik zobaczył swoje wyniki.
    window.scrollTo(0, 0);
  }

  /**
   * Renderuje przycisk i kontener na wyjaśnienie do pytania.
   * @private
   * @param {HTMLElement} questionCard - Element DOM karty pytania.
   * @param {string} explanation - Treść wyjaśnienia w formacie Markdown.
   */
  renderExplanation(questionCard, explanation) {
    const buttonContainer = questionCard.querySelector('.quiz-card__button-container');
    const explanationContainer = questionCard.querySelector('.quiz-card__explanation');

    if (buttonContainer && explanationContainer) {
      explanationContainer.innerHTML = DOMPurify.sanitize(marked.parse(explanation));
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = 'Pokaż wyjaśnienie';
      button.className = 'btn btn--secondary btn--small';

      button.addEventListener('click', () => {
        const isVisible = explanationContainer.classList.toggle('quiz-card__explanation--visible');
        button.textContent = isVisible ? 'Ukryj wyjaśnienie' : 'Pokaż wyjaśnienie';
      });

      buttonContainer.appendChild(button);
    }
  }

  /**
   * Ostrzega użytkownika przed niezapisanymi zmianami przy próbie zamknięcia strony.
   * @private
   * @param {Event} event - Obiekt zdarzenia `beforeunload`.
   */
  handleBeforeUnload(event) {
    if (this.isTestInProgress) {
      event.preventDefault();
      event.returnValue = '';
    }
  }

  /**
   * Dodaje wizualny feedback (klasę CSS) po kliknięciu na odpowiedź.
   * @private
   * @param {Event} event - Obiekt zdarzenia `click`.
   */
  handleAnswerSelection(event) {
    const answerLabel = event.target.closest('.quiz-card__answer');
    if (!answerLabel) return;

    const currentQuestionCard = answerLabel.closest('.quiz-card');
    if (currentQuestionCard) {
      // Usuń zaznaczenie z poprzednio wybranej odpowiedzi w ramach TEGO pytania.
      currentQuestionCard
        .querySelectorAll('.quiz-card__answer.selected')
        .forEach((label) => label.classList.remove('selected'));
      // Dodaj zaznaczenie do klikniętej odpowiedzi.
      answerLabel.classList.add('selected');
    }
  }

  /**
   * Uruchamia i zarządza licznikiem czasu.
   * @private
   * @param {number} duration - Czas testu w sekundach.
   */
  startTimer(duration) {
    let timeLeft = duration;
    const timerEl = document.getElementById('timer');

    this.timerInterval = setInterval(() => {
      // Krok 1: Aktualizuj upływający czas.
      this.timeSpent++;
      timeLeft = duration - this.timeSpent;
      if (timeLeft < 0) timeLeft = 0;

      // Krok 2: Zaktualizuj interfejs użytkownika.
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      if (timerEl) {
        timerEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
      }

      // Krok 3: Jeśli czas się skończył, automatycznie zakończ test.
      if (timeLeft <= 0) {
        this.finishTest();
      }
    }, 1000);
  }
}