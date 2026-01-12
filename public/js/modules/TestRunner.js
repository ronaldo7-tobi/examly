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
   * Zaktualizowana metoda renderAllQuestions
   */
  renderAllQuestions(questions) {
    this.questionsWrapper.innerHTML = '';

    questions.forEach((qData, index) => {
      // Pobieramy dane z nowymi kluczami
      const { question_id, question_version_id, question_text, image_path } = qData.question;
      const answers = qData.answers;
      let answersHTML = '';

      answers.forEach((answer, answerIndex) => {
        const letter = String.fromCharCode(65 + answerIndex);
        
        // FIX: Zmiana z .content na .answer_text
        // Dodajemy || '', aby uniknąć błędu marked w razie pustej odpowiedzi
        const unsafeHTML = marked.parse(answer.answer_text || '', { gfm: true, breaks: true });
        const contentHTML = DOMPurify.sanitize(unsafeHTML);

        answersHTML += `
          <label class="quiz-card__answer">
            <input type="radio" name="q_${question_version_id}" value="${answer.id}">
            <span class="quiz-card__answer-prefix">${letter}</span>
            <span class="quiz-card__answer-text">${contentHTML}</span>
          </label>`;
      });

      const questionElement = document.createElement('section');
      questionElement.className = 'quiz-card';
      questionElement.id = `question-${question_id}`; // ID bazowe dla DOM
      
      const imageHTML = image_path
        ? `<div class="quiz-card__image-container">
             <img src="/examly/public/images/questions/${escapeHTML(image_path)}"
                  alt="Ilustracja do pytania" class="quiz-card__image">
           </div>`
        : '';

      questionElement.innerHTML = `
        <header class="quiz-card__header">
          <span class="quiz-card__question-number">Pytanie ${index + 1} / ${questions.length}</span>
        </header>
        <div class="quiz-card__content">
          <p class="quiz-card__question-text">${escapeHTML(question_text)}</p>
          ${imageHTML}
        </div>
        <div class="quiz-card__answers">${answersHTML}</div>
        <div class="quiz-card__actions">
          <div class="quiz-card__button-container"></div>
          <div class="quiz-card__explanation"></div>
        </div>`;

      this.questionsWrapper.appendChild(questionElement);
    });
  }

async finishTest() {
    this.isTestInProgress = false;
    clearInterval(this.timerInterval);
    window.removeEventListener('beforeunload', this.handleBeforeUnload);

    let correctAnswersCount = 0;
    const topicIdsInTest = new Set();
    const answersDetails = [];
    const userAnswersMap = {}; // DODANE: Mapa do podsumowania

    this.questionsData.forEach((qData) => {
      const { question_version_id, topic_id } = qData.question;
      topicIdsInTest.add(topic_id);

      const selectedInput = this.questionsWrapper.querySelector(`input[name="q_${question_version_id}"]:checked`);
      const selectedAnswerId = selectedInput ? parseInt(selectedInput.value, 10) : null;
      
      // Zapisujemy wybór do mapy
      userAnswersMap[question_version_id] = selectedAnswerId;

      const correctAnswer = qData.answers.find(a => a.is_correct === 1);
      if (selectedAnswerId && correctAnswer && selectedAnswerId === correctAnswer.id) {
        correctAnswersCount++;
      }

      if (selectedAnswerId) {
        answersDetails.push({
          question_version_id: question_version_id,
          answer_id: selectedAnswerId
        });
      }
    });

    const totalQuestions = this.questionsData.length;
    const score = totalQuestions > 0 ? (correctAnswersCount / totalQuestions) * 100 : 0;

    if (window.examlyAppState?.isUserLoggedIn) {
      const attemptPayload = {
        examCode: this.examCode || 'INF.03',
        isFullExam: this.isFullExam,
        correctAnswers: correctAnswersCount,
        totalQuestions: totalQuestions,
        duration: this.timeSpent,
        topicIds: Array.from(topicIdsInTest),
        answers: answersDetails
      };
      await api.saveAttempt(attemptPayload);
    }

    // WAŻNE: Przekazujemy trzeci argument
    this.showResults(score, correctAnswersCount, userAnswersMap);
  }

  /**
   * Buduje i wyświetla ekran wyników ze szczegółowym podsumowaniem.
   */
  showResults(score, correctCount, userAnswers) {
    this.testView.classList.add('hidden');
    this.resultsScreen.classList.remove('hidden');

    // Sekcja Podsumowania
    const scoreSummaryContainer = document.getElementById('score-summary');
    const scoreToDisplay = this.isFullExam ? score.toFixed(2) : Math.round(score);
    const scoreForAnimation = Math.round(score);

    const minutes = Math.floor(this.timeSpent / 60);
    const seconds = this.timeSpent % 60;
    const timeString = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

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
            <p class="stat-item__value">${timeString}</p>
          </div>
        </div>
      </div>`;

    this.resultsDetailsContainer.innerHTML = '';

    this.questionsData.forEach((qData, index) => {
      const { question_id, question_version_id, question_text, image_path, explanation } = qData.question;
      const userAnswerId = userAnswers[question_version_id]; // Odczyt z mapy
      const answers = qData.answers;

      const card = document.createElement('section');
      card.className = 'quiz-card';
      card.id = `result-q-${question_id}`;

      let answersHTML = '';
      answers.forEach((ans, idx) => {
        const letter = String.fromCharCode(65 + idx);
        let statusClass = '';

        // NOWA LOGIKA KOLORYSTYKI
        if (userAnswerId === ans.id) {
          // Użytkownik wybrał tę odpowiedź (Zawsze usuwamy 'selected', by nie było niebieskiego tła)
          statusClass = ans.is_correct === 1 ? 'correct' : 'incorrect';
        } else if (ans.is_correct === 1) {
          // To jest poprawna odpowiedź, której użytkownik nie wybrał
          // Jeśli w ogóle nie odpowiedział -> niebieski (missed)
          // Jeśli wybrał inną, błędną -> zielony (correct)
          statusClass = (userAnswerId === null || userAnswerId === undefined) ? 'missed' : 'correct';
        }

        const contentHTML = DOMPurify.sanitize(marked.parse(ans.answer_text || ''));

        answersHTML += `
          <div class="quiz-card__answer ${statusClass}">
            <span class="quiz-card__answer-prefix">${letter}</span>
            <span class="quiz-card__answer-text">${contentHTML}</span>
            ${userAnswerId === ans.id ? '<input type="radio" checked disabled style="display:none">' : ''}
          </div>`;
      });

      card.innerHTML = `
        <header class="quiz-card__header"><span class="quiz-card__question-number">Pytanie ${index + 1}</span></header>
        <div class="quiz-card__content">
          <p class="quiz-card__question-text">${escapeHTML(question_text)}</p>
          ${image_path ? `<img src="/examly/public/images/questions/${escapeHTML(image_path)}" class="quiz-card__image">` : ''}
        </div>
        <div class="quiz-card__answers">${answersHTML}</div>
        <div class="quiz-card__actions"></div>
      `;

      if (explanation && explanation.trim()) {
        this.renderExplanationResult(card, explanation);
      }

      this.resultsDetailsContainer.appendChild(card);
    });

    window.scrollTo(0, 0);
  }

  /**
   * Renderuje sekcję wyjaśnienia w wynikach (z działającym przyciskiem).
   */
  renderExplanationResult(card, explanation) {
    const actionsContainer = document.createElement('div');
    actionsContainer.className = 'quiz-card__actions'; // Zgodne z main.css

    const btnContainer = document.createElement('div');
    btnContainer.className = 'quiz-card__button-container';

    const explContainer = document.createElement('div');
    explContainer.className = 'quiz-card__explanation'; // Domyślnie ukryte w CSS
    explContainer.innerHTML = DOMPurify.sanitize(marked.parse(explanation || ''));

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn--secondary btn--small';
    button.textContent = 'Pokaż wyjaśnienie';

    button.addEventListener('click', () => {
      const isVisible = explContainer.classList.toggle('quiz-card__explanation--visible');
      button.textContent = isVisible ? 'Ukryj wyjaśnienie' : 'Pokaż wyjaśnienie';
    });

    btnContainer.appendChild(button);
    actionsContainer.appendChild(btnContainer);
    actionsContainer.appendChild(explContainer);

    card.appendChild(actionsContainer);
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