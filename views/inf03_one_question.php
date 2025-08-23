<?php

/**
 * ========================================================================
 * Plik Widoku: Tryb "Jedno Pytanie"
 * ========================================================================
 *
 * @description Renderuje interfejs użytkownika dla trybu nauki "Jedno Pytanie".
 * Umożliwia użytkownikowi wybór tematów, a następnie dynamicznie
 * (za pomocą JavaScript) ładuje i wyświetla pojedyncze pytanie
 * z wybranej puli.
 *
 * @dependencies 
 * - partials/navbar.php (Nawigacja)
 * - partials/footer.php (Stopka)
 * - main.css (Główne style)
 * - Font Awesome (Ikony)
 * - marked.min.js (Parser Markdown dla treści pytań)
 * - purify.min.js (Zabezpieczenie przed XSS przy renderowaniu HTML)
 * - topic-form-enhancer.js (Skrypt ulepszający formularz wyboru tematów)
 * - one-question/index.js (Główna logika trybu "Jedno Pytanie")
 *
 * @state_variables 
 * - $examCode (string): Kod kwalifikacji (np. "inf03"), przekazywany
 *   do atrybutu data-, aby JavaScript wiedział, z jakiej puli pytań korzystać.
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <!-- Meta Tagi SEO i podstawowe informacje -->
  <title>Examly - INF.03 jedno pytanie</title>
  <meta name="description" content="Examly to najlepsza platforma edukacyjna oferująca testy i materiały do egzaminów zawodowych. Przygotuj się skutecznie do egzaminu E.14 / INF.03.">
  <meta name="author" content="Examly.pl">
  <link rel="canonical" href="https://www.examly.pl/">

  <!-- Meta Tagi Open Graph & X Card (dla podglądów w mediach społecznościowych) -->
  <!-- ... (pozostałe meta tagi jak w poprzednich plikach) ... -->

  <!-- Zasoby (Assets) -->
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../public/scss/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <!-- Biblioteki JS ładowane w <head> ze względu na renderowanie treści -->
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>
</head>
<body>
  
  <!-- Dołączenie reużywalnego komponentu paska nawigacyjnego -->
  <?php include 'partials/navbar.php'; ?>

  <!-- 
    =====================================================================
    Nagłówek strony (Page Header)
    ---------------------------------------------------------------------
    Cel: Wprowadzenie użytkownika w kontekst bieżącego trybu nauki.
         Zawiera tytuł, krótki opis funkcjonalności oraz wskazówkę (Pro Tip).
    =====================================================================
  -->
  <header class="page-header">
    <div class="page-header__content">
      <h1 class="page-header__title"><span class="text-gradient">EE.09 / INF.03</span> - Jedno pytanie</h1>
      <p class="page-header__text">
        Nie masz czasu na pełny test? Spoko! Ten tryb jest idealny, żeby w kilka minut wbić do głowy coś nowego. Wybierz dział, wylosuj pytanie prosto z oficjalnych egzaminów i sprawdź, czy dasz radę.
      </p>
      <p class="page-header__text">
        <strong class="text-gradient">Pro Tip:</strong> Zapisuj na boku każde nowe hasło lub trudniejsze zagadnienie. Regularne, krótkie sesje potrafią zdziałać cuda!
      </p>
    </div>
  </header>

  <!-- 
    =====================================================================
    Główny Layout Strony Quizu
    ---------------------------------------------------------------------
    Opis: Dwukolumnowy layout, który oddziela panel sterowania (wybór
          tematów) od głównej treści (wyświetlane pytanie).
    Atrybut `data-exam-code`: Przekazuje kod egzaminu do logiki JS.
    =====================================================================
  -->
  <div class="quiz-page-layout" id="quiz-single-question" data-exam-code="<?= htmlspecialchars($examCode) ?>">

    <!-- Panel boczny (Sidebar) z formularzem wyboru tematów -->
    <aside class="quiz-page-layout__sidebar">
      <form id="topic-form" class="topic-selector">
        
        <!-- Grupa: Główne kategorie tematyczne -->
        <fieldset class="topic-selector__fieldset">
          <legend class="topic-selector__sub-legend">Główne kategorie</legend>
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
        </fieldset>

        <!-- Grupa: Opcje inteligentnej nauki (potencjalnie dla użytkowników premium) -->
        <fieldset class="topic-selector__fieldset">
          <legend class="topic-selector__sub-legend topic-selector__sub-legend--premium">Inteligentna nauka</legend>
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
        </fieldset>

        <button type="submit" class="btn btn--primary">Rozpocznij naukę!</button>
      </form>
    </aside>

    <!-- Główna treść (Main Content), gdzie dynamicznie ładowane jest pytanie -->
    <main class="quiz-page-layout__main-content">
      <!-- 
        Kontener na pytanie.
        Początkowo zawiera placeholder. Jego zawartość jest w całości
        zarządzana przez skrypt /public/js/features/one-question/index.js
      -->
      <div id="quiz-container" style="margin-top: 20px;">
        <p class="quiz-placeholder">Wybierz przynajmniej jeden temat, aby rozpocząć naukę.</p>
      </div>
    </main>
  </div>

  <!-- Dołączenie reużywalnego komponentu stopki -->
  <?php include 'partials/footer.php'; ?>

  <!-- Skrypty JS ładowane jako moduły na końcu strony -->
  <!-- Skrypt pomocniczy, dodający funkcjonalności do formularza (np. "zaznacz wszystko") -->
  <script type="module" src="/examly/public/js/components/topic-form-enhancer.js"></script>
  <!-- Główny skrypt aplikacji dla tego widoku, obsługujący logikę pobierania i wyświetlania pytań -->
  <script type="module" src="/examly/public/js/features/one-question/index.js"></script>
</body>
</html>