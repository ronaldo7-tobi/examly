<?php
/**
 * Konfiguracja komponentu head
 */
$pageTitle = 'INF.03 - Egzamin próbny - Test Online 40 Pytań | Examly';
$pageDescription = 'Sprawdź swoją wiedzę przed egzaminem zawodowym INF.03. Rozwiąż pełny test próbny online, który symuluje prawdziwy egzamin i składa się z 40 pytań.';
$canonicalUrl = 'https://www.examly.pl/inf03-test-probny';
$extraScripts = [
  'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
  'https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js'
];

/**
 * ========================================================================
 * Plik Widoku: Pełny Egzamin Próbny
 * ========================================================================
 *
 * @description Renderuje interfejs dla pełnego egzaminu próbnego.
 * Ten widok zarządza trzema głównymi stanami: ekranem ładowania,
 * aktywnym testem oraz ekranem wyników. Logika przełączania
 * między tymi stanami oraz dynamiczne ładowanie pytań
 * i obliczanie wyników jest obsługiwana przez JavaScript.
 *
 * @dependencies 
 * - partials/head.php (head)
 * - partials/navbar.php (Nawigacja)
 * - partials/footer.php (Stopka)
 * - main.css (Główne style)
 * - Font Awesome (Ikony)
 * - marked.min.js, purify.min.js (Renderowanie treści pytań)
 * - test/index.js (Główny skrypt zarządzający logiką testu)
 *
 * @state_variables 
 * - $examCode (string): Kod kwalifikacji (np. "inf03"),
 *   używany przez JS do pobrania 40 losowych pytań.
 */
?>
<!DOCTYPE html>
<html lang="pl">

<?php include 'partials/head.php'; ?>

<body>

  <?php include 'partials/navbar.php'; ?>

  <!-- Nagłówek wprowadzający w kontekst strony -->
  <header class="page-header">
    <div class="page-header__content">
      <h1 class="page-header__title"><span class="text-gradient">EE.09 / INF.03</span> - Pełny Egzamin Próbny</h1>
      <p class="page-header__text">
        Zmierz się z pełnym egzaminem próbnym składającym się z 40 losowych pytań z naszej bazy.
        To najlepszy sposób, by sprawdzić swoją wiedzę, zidentyfikować słabe punkty i śledzić postępy w nauce.
      </p>
      <p class="page-header__text">
        <strong class="text-gradient">Pro Tip:</strong> Po teście skup się na analizie błędnych odpowiedzi.
        Zrozumienie, dlaczego popełniłeś błąd, jest kluczem do sukcesu na prawdziwym egzaminie.
      </p>
    </div>
  </header>

  <div class="container">
    <!-- 
      =====================================================================
      Główny Kontener Testu (State Machine)
      ---------------------------------------------------------------------
      Opis: Ten kontener pełni rolę "maszyny stanów" dla interfejsu testu.
            Zawiera trzy pod-kontenery reprezentujące różne stany:
            1. #loading-screen - widoczny podczas pobierania pytań.
            2. #test-view - widoczny podczas rozwiązywania testu.
            3. #results-screen - widoczny po zakończeniu testu.
            Logika przełączania widoczności jest w całości w JS.
      =====================================================================
    -->
    <main id="test-container" class="test-container" data-exam-code="<?= htmlspecialchars($examCode) ?>">

      <!-- Stan 1: Ekran ładowania -->
      <div id="loading-screen" class="test-loading">
        <h2>Trwa przygotowywanie testu...</h2>
        <p>Proszę czekać, losujemy 40 pytań z całej puli.</p>
        <div class="spinner"></div>
      </div>

      <!-- Stan 2: Widok aktywnego testu (początkowo ukryty) -->
      <div id="test-view" class="hidden">
        <header class="test-header">
          <h1 class="test-header__title">Test Całościowy - <span class="text-gradient">INF.03</span></h1>
          <div class="test-header__meta">
            <!-- Minutnik, zarządzany przez JS -->
            <div id="timer" class="timer">60:00</div>
          </div>
        </header>

        <!-- Kontener, do którego JS dynamicznie wstrzykuje pytania -->
        <div id="questions-wrapper" class="questions-wrapper"></div>

        <footer class="test-footer">
          <button id="finish-test-btn" class="btn btn--primary">Zakończ i sprawdź test</button>
        </footer>
      </div>

      <!-- Stan 3: Ekran wyników (początkowo ukryty) -->
      <div id="results-screen" class="results-container hidden">
        <h1 class="results-container__title">Wyniki Testu</h1>
        <!-- Podsumowanie wyniku, wypełniane przez JS -->
        <div id="score-summary" class="score-summary">
          <p>Twój wynik: <strong id="score-percent">0%</strong></p>
          <p>Poprawne odpowiedzi: <strong id="correct-count">0</strong></p>
          <p>Czas ukończenia: <strong id="duration">00:00</strong></p>
        </div>
        <!-- Kontener na szczegółową listę pytań z odpowiedziami, generowany przez JS -->
        <div id="results-details" class="results-details"></div>
        <div class="results-container__actions">
          <a href="<?= url('/') ?>" class="btn btn--primary">Wróć na stronę główną</a>
        </div>
      </div>

    </main>
  </div>

  <?php include 'partials/footer.php'; ?>

  <!-- Główny skrypt aplikacji dla tego widoku, zarządzający całą logiką testu -->
  <script type="module" src="<?= url('js/features/test/index.js') ?>"></script>
</body>

</html>