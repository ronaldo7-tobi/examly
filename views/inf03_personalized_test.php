<?php

/**
 * ========================================================================
 * Plik Widoku: Spersonalizowany Test
 * ========================================================================
 *
 * @description Renderuje interfejs do tworzenia i przeprowadzania
 * spersonalizowanego testu. Użytkownik przechodzi przez wieloetapowy
 * formularz (konfigurator), aby wybrać tematy, liczbę pytań
 * i opcjonalne filtry inteligentnej nauki.
 *
 * @dependencies 
 * - partials/navbar.php (Nawigacja)
 * - partials/footer.php (Stopka)
 * - main.css (Główne style)
 * - Font Awesome (Ikony)
 * - marked.min.js, purify.min.js (Renderowanie treści pytań)
 * - slider-enhancer.js (Obsługa suwaka liczby pytań)
 * - topic-form-enhancer.js (Ulepszenia formularza wyboru tematów)
 * - personalized-test/index.js (Główna logika konfiguratora i testu)
 *
 * @state_variables 
 * - $examCode (string): Kod kwalifikacji (np. "inf03"),
 *   przekazywany do JS w celu pobrania właściwych pytań.
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <!-- Meta Tagi SEO i podstawowe informacje -->
  <title>Examly - INF.03 spersonalizowany test</title>
  <meta name="description" content="Stwórz własny test z kwalifikacji INF.03 / E.14. Wybierz tematy, liczbę pytań i ucz się tak, jak lubisz z platformą Examly.">
  <meta name="author" content="Examly.pl">
  <link rel="canonical" href="https://www.examly.pl/inf03_personalized_test">

  <!-- Meta Tagi Open Graph & X Card -->
  <!-- ... (pozostałe meta tagi) ... -->

  <!-- Zasoby (Assets) -->
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../public/scss/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
</head>
<body>
  
  <?php include 'partials/navbar.php'; ?>

  <!-- Nagłówek wprowadzający w kontekst strony -->
  <header class="page-header">
    <div class="page-header__content">
      <h1 class="page-header__title"><span class="text-gradient">EE.09 / INF.03</span> - Spersonalizowany test</h1>
      <p class="page-header__text">
        Stwórz test idealnie dopasowany do Twoich potrzeb. Wybierz interesujące Cię tematy, ustaw liczbę pytań i skup się na tym, co jest dla Ciebie najważniejsze.
      </p>
      <p class="page-header__text">
        <strong class="text-gradient">Pro Tip:</strong> Filtry inteligentnej nauki działają najskuteczniej, gdy system pozna Twoje postępy. Rozwiąż kilka testów w trybie standardowym, aby w pełni odblokować ich moc!
      </p>
    </div>
  </header>

  <!-- Główny kontener strony, przekazujący kod egzaminu do JS -->
  <div class="container" id="personalized-test-page" data-exam-code="<?= htmlspecialchars($examCode) ?>">

    <!-- 
      =====================================================================
      Komponent: Konfigurator Testu
      ---------------------------------------------------------------------
      Opis: Wieloetapowy formularz, który prowadzi użytkownika przez proces
            tworzenia testu. Każdy krok to osobny `<fieldset>`.
            Widoczność kroków jest zarządzana przez JavaScript.
      =====================================================================
    -->
    <div class="test-configurator">
      <form id="topic-form">

        <!-- Krok 1: Wybór tematów -->
        <fieldset class="config-step" data-step="1">
          <legend class="config-step__title">
            <span class="config-step__number">1</span>
            Wybierz tematy, z których chcesz się uczyć
          </legend>
          <div class="topic-selector__options">
            <label class="topic-selector__label">
              <input type="checkbox" value="inf03" id="select-all-inf03">
              <span>Cały materiał INF.03</span>
            </label>
            <label class="topic-selector__label">
              <input type="checkbox" name="subject[]" value="1" class="topic-checkbox">
              <span>HTML</span>
            </label>
            <label class="topic-selector__label">
              <input type="checkbox" name="subject[]" value="2" class="topic-checkbox">
              <span>CSS</span>
            </label>
            <label class="topic-selector__label">
              <input type="checkbox" name="subject[]" value="3" class="topic-checkbox">
              <span>JS</span>
            </label>
            <label class="topic-selector__label">
              <input type="checkbox" name="subject[]" value="4" class="topic-checkbox">
              <span>PHP</span>
            </label>
            <label class="topic-selector__label">
              <input type="checkbox" name="subject[]" value="5" class="topic-checkbox">
              <span>SQL</span>
            </label>
            <label class="topic-selector__label">
              <input type="checkbox" name="subject[]" value="6" class="topic-checkbox">
              <span>Inne pytania teoretyczne</span>
            </label>
          </div>
          <div class="config-step__actions">
            <button type="button" class="btn btn--primary" data-action="next">Dalej</button>
          </div>
        </fieldset>

        <!-- Krok 2: Wybór liczby pytań -->
        <fieldset class="config-step" data-step="2" style="display: none;">
          <legend class="config-step__title">
            <span class="config-step__number">2</span>
            Określ liczbę pytań
          </legend>
          <div class="slider-container">
            <label for="question-count" class="slider-container__label">
              Wybierz liczbę pytań: <output id="question-count-value">25</output>
            </label>
            <input type="range" id="question-count" name="question_count" min="10" max="40" value="25" class="slider-container__input">
          </div>
          <div class="config-step__actions">
            <button type="button" class="btn btn--secondary" data-action="prev">Wróć</button>
            <button type="button" class="btn btn--primary" data-action="next">Dalej</button>
          </div>
        </fieldset>

        <!-- Krok 3: Opcje inteligentnej nauki -->
        <fieldset class="config-step" data-step="3" style="display: none;">
          <legend class="config-step__title">
            <span class="config-step__number">3</span>
            Chcesz uczyć się inteligentnie? (Opcjonalnie)
          </legend>
          <div class="topic-selector__options">
            <label class="topic-selector__label premium-option">
              <input type="checkbox" name="premium_option" value="toDiscover" class="premium-checkbox">
              <span>Nieodkryte pytania</span>
            </label>
            <label class="topic-selector__label premium-option">
              <input type="checkbox" name="premium_option" value="toImprove" class="premium-checkbox">
              <span>Pytania, które gorzej Ci idą</span>
            </label>
            <label class="topic-selector__label premium-option">
              <input type="checkbox" name="premium_option" value="toRemind" class="premium-checkbox">
              <span>Pytania najdawniej powtórzone</span>
            </label>
            <label class="topic-selector__label premium-option">
              <input type="checkbox" name="premium_option" value="lastMistakes" class="premium-checkbox">
              <span>Ostatnio błędne</span>
            </label>
          </div>
          <div class="config-step__actions">
            <button type="button" class="btn btn--secondary" data-action="prev">Wróć</button>
            <button type="submit" class="btn btn--primary btn">Rozpocznij Test!</button>
          </div>
        </fieldset>
      </form>
    </div>

    <!-- 
      Główny kontener na dynamicznie renderowany test.
      Początkowo pusty, zostaje wypełniony przez JS po wysłaniu formularza.
    -->
    <main id="quiz-container"></main>
  </div>

  <?php include 'partials/footer.php'; ?>

  <!-- Skrypty JS ładowane jako moduły na końcu strony -->
  <!-- Skrypt obsługujący interaktywność suwaka -->
  <script type="module" src="/examly/public/js/components/slider-enhancer.js"></script>
  <!-- Główny skrypt logiki dla tej strony (zarządzanie krokami, pobieranie i renderowanie testu) -->
  <script type="module" src="/examly/public/js/features/personalized-test/index.js"></script>
  <!-- Skrypt pomocniczy do obsługi checkboxów (np. "zaznacz wszystko") -->
  <script type="module" src="/examly/public/js/components/topic-form-enhancer.js"></script>
</body>
</html>