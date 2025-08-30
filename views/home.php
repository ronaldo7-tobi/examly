<?php
/**
 * Konfiguracja komponentu head
 */
$pageTitle = 'INF.02 INF.03 - Testy Online i kursy | Examly';
$pageDescription = 'Zwiększ swoje szanse na zdanie egzaminu INF.03 i INF.02. Rozwiązuj testy online z tysięcy pytań, śledź postępy i ucz się mądrzej z Examly. Zacznij dziś!';
$canonicalUrl = 'https://www.examly.pl/';

/**
 * ========================================================================
 * Plik Widoku: Strona Główna (home.php)
 * ========================================================================
 *
 * @description Główny plik widoku dla strony startowej aplikacji Examly.
 * Prezentuje kluczowe informacje o platformie, jej funkcje
 * oraz zachęca użytkowników do interakcji (call-to-action).
 * Struktura pliku opiera się na modułowym dołączaniu
 * komponentów (navbar, footer) oraz sekcjach semantycznych.
 *
 * @dependencies 
 * - partials/head.php (head)
 * - partials/navbar.php (nawigacja)
 * - partials/footer.php (stopka)
 * - main.css (główne style)
 * - Font Awesome (ikony)
 *
 * @state_variables 
 * - $isUserLoggedIn (bool): Zmienna z logiki serwera,
 *   która określa, czy użytkownik jest zalogowany.
 *   Używana do warunkowego renderowania elementów UI.
 */
?>
<!DOCTYPE html>
<html lang="pl">
<!-- Dołączenie reużywalnego komponentu head -->
<?php include 'partials/head.php'; ?>

<body class="home-page-background">
  <!-- Dołączenie reużywalnego komponentu paska nawigacyjnego -->
  <?php include 'partials/navbar.php'; ?>

  <main class="home-page">
    <!-- 
      =====================================================================
      Sekcja 1: Hero Section
      ---------------------------------------------------------------------
      Cel: Pierwsze wrażenie. Ma za zadanie przyciągnąć uwagę użytkownika,
           jasno zakomunikować główną wartość platformy i skierować go
           do kluczowych akcji (CTA).
      =====================================================================
    -->
    <section class="hero-section">
      <div class="hero-section__content">
        <h1 class="hero-section__title"><span class="text-gradient">Zdaj</span> egzamin zawodowy bez stresu.</h1>
        <p class="hero-section__subtitle">Opanuj cały materiał dzięki naszej inteligentnej platformie z tysiącami pytań i zdobądź wynik o którym marzysz.</p>
        <div class="hero-section__cta-group">
          <a href="<?= url('#egzaminy') ?>" class="btn btn--primary btn--large">Rozpocznij egzamin próbny</a>

          <!-- CTA widoczne tylko dla gości -->
          <?php if (!$isUserLoggedIn): ?>
            <a href="<?= url('rejestracja') ?>" class="btn btn--secondary btn--large">Załóż darmowe konto</a>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- 
      =====================================================================
      Sekcja 2: Features Section
      ---------------------------------------------------------------------
      Cel: Przedstawienie kluczowych funkcjonalności i zalet platformy
           w przystępnej formie kart.
      =====================================================================
    -->
    <section class="features-section">
      <h2 class="section-title">Wszystko, czego potrzebujesz w jednym miejscu</h2>
      <div class="features-grid">
        <!-- Przykładowa karta funkcji -->
        <div class="feature-card">
          <div class="feature-card__header">
            <i class="feature-card__icon fas fa-database"></i>
            <h3 class="feature-card__title">Ogromna Baza Pytań</h3>
          </div>
          <p class="feature-card__text">Korzystaj z tysięcy pytań z oficjalnych arkuszy egzaminacyjnych z poprzednich lat.</p>
          <div class="feature-card__details">
            <p>Nasza baza jest stale aktualizowana, co gwarantuje Ci dostęp do najbardziej kompletnego zbioru materiałów przygotowawczych.</p>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-card__header">
            <i class="feature-card__icon fas fa-lightbulb"></i>
            <h3 class="feature-card__title">Inteligentna Nauka <span class="sparkling">✨</span></h3>
          </div>
          <p class="feature-card__text">
            Nasz system śledzi Twoje postępy i oferuje inteligentne funkcje doboru pytań.
          </p>
          <div class="feature-card__details">
            <p>
              Ucz się mądrzej z naszymi inteligentnymi funkcjami:
            </p>
            <ul class="feature-card__list">
              <li>
                <strong>Cztery tryby nauki:</strong>
                Aplikacja oferuje wyjątkowe tryby nauki maksymalizujące efekty Twojej pracy.
              </li>
              <li>
                <strong>Gotowe wyjaśnienia:</strong>
                Każde pytanie zawiera szczegółowe wyjaśnienie,
                dzięki czemu nie tracisz cennego czasu na szukanie odpowiedzi.
              </li>
            </ul>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-card__header">
            <i class="feature-card__icon fas fa-chart-line"></i>
            <h3 class="feature-card__title">Szczegółowe Statystyki</h3>
          </div>
          <p class="feature-card__text">Analizuj swoje wyniki i śledź postępy, by wiedzieć, na czym się skupić.</p>
          <div class="feature-card__details">
            <p>
              Analizuj swoją skuteczność w każdym dziale dzięki czytelnym wykresom i tabelom. Obserwuj,
              jak rośnie Twoja gotowość do egzaminu – wszystkie te funkcje są dostępne już w darmowym koncie!
            </p>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-card__header">
            <i class="feature-card__icon fas fa-keyboard"></i>
            <h3 class="feature-card__title">Arkusze Praktyczne</h3>
          </div>
          <p class="feature-card__text">
            Pobieraj gotowe do druku arkusze PDF i ćwicz w warunkach zbliżonych do prawdziwego egzaminu.
          </p>
          <div class="feature-card__details">
            <p>
              Każdy arkusz to kompletny zestaw zadań praktycznych, który pomoże Ci oswoić się z formą egzaminu.
              (Wkrótce!)
            </p>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-card__header">
            <i class="feature-card__icon fas fa-graduation-cap"></i>
            <h3 class="feature-card__title">Najlepsze Kursy</h3>
          </div>
          <p class="feature-card__text">
            Skorzystaj z naszych kompleksowych kursów wideo, które przeprowadzą Cię przez każdy temat.
          </p>
          <div class="feature-card__details">
            <p>
              Nasi eksperci tłumaczą najtrudniejsze zagadnienia w prosty i zrozumiały sposób, krok po kroku.
              (Wkrótce!)
            </p>
          </div>
        </div>
        <div class="feature-card">
          <div class="feature-card__header">
            <i class="feature-card__icon fas fa-feather-alt"></i>
            <h3 class="feature-card__title">Przyjazny Interfejs</h3>
          </div>
          <p class="feature-card__text">
            Ucz się bez przeszkód dzięki prostemu i intuicyjnemu interfejsowi na każdym urządzeniu.
          </p>
          <div class="feature-card__details">
            <p>
              Zaprojektowaliśmy Examly tak, aby nic nie odciągało Twojej uwagi od tego,
              co najważniejsze – efektywnej nauki.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- 
      =====================================================================
      Sekcja 3: Exams Section
      ---------------------------------------------------------------------
      Cel: Bezpośrednie skierowanie użytkownika do dostępnych egzaminów.
           Używa wizualnych kart do reprezentowania każdej kwalifikacji.
           Identyfikator `id="exams-section"` służy jako kotwica dla linków.
      =====================================================================
    -->
    <section class="exams-section" id="egzaminy">
      <h2 class="section-title">Rozwiąż test z swojej kwalifikacji już teraz</h2>
      <div class="exams-grid">
        <a href="<?= url('inf03-test') ?>" class="exam-card">
          <div class="exam-card__background" style="background-image: url('https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=2070&auto=format&fit=crop');"></div>
          <div class="exam-card__overlay"></div>
          <div class="exam-card__content">
            <h3 class="exam-card__title">INF.03 (dawniej E.14)</h3>
            <p class="exam-card__subtitle">Programowanie webowe</p>
          </div>
        </a>
        <!-- Karta nieaktywna, z modyfikatorem BEM `--disabled` -->
        <a href="#" class="exam-card exam-card--disabled">
          <div class="exam-card__background" style="background-image: url('https://images.unsplash.com/photo-1593720213428-28a5b9e94613?q=80&w=2070&auto=format&fit=crop');"></div>
          <div class="exam-card__overlay"></div>
          <div class="exam-card__content">
            <h3 class="exam-card__title">INF.02 (dawniej EE.08)</h3>
            <p class="exam-card__subtitle">
              Sprzęt, systemy i sieci<br>
              <span class="exam-card__soon">Wkrótce!</span>
            </p>
          </div>
        </a>
      </div>
    </section>

    <!-- 
      =====================================================================
      Sekcja 4: Exam Info Section (FAQ)
      ---------------------------------------------------------------------
      Cel: Odpowiedź na najczęściej zadawane pytania dotyczące egzaminu,
           budowanie zaufania i dostarczanie wartościowej treści.
      =====================================================================
    -->
    <section class="exam-info-section">
      <h2 class="section-title">Podstawowe informacje o egzaminie zawodowym</h2>
      <div class="info-grid">
        <div class="info-block">
          <h3 class="info-block__question">Z czego składa się egzamin?</h3>
          <p class="info-block__answer">
            Egzamin podzielony jest na dwie kluczowe części: pisemną i praktyczną. Etap pisemny to test
            wyboru trwający <strong>60 minut</strong>, składający się z 40 pytań z jedną prawidłową odpowiedzią.
            Etap praktyczny to zadanie do wykonania przy komputerze,
            na które przeznaczone jest <strong>240 minut</strong>.
          </p>
        </div>
        <div class="info-block">
          <h3 class="info-block__question">Jakie są progi zdawalności?</h3>
          <div class="info-block__answer">
            <p>Aby uzyskać kwalifikację zawodową, musisz osiągnąć dwa progi punktowe:</p>
            <ul class="info-block__list">
              <li>Z części pisemnej: minimum <strong>50%</strong> punktów.</li>
              <li>Z części praktycznej: minimum <strong>75%</strong> punktów.</li>
            </ul>
          </div>
        </div>
        <div class="info-block">
          <h3 class="info-block__question">Co w przypadku niepowodzenia?</h3>
          <p class="info-block__answer">
            Warunkiem zdania całego egzaminu jest zaliczenie obu jego części.
            Jeśli nie powiedzie Ci się tylko w jednej z nich,
            możesz przystąpić do <strong>poprawki wyłącznie tej części</strong>.
            Oblanie obu etapów wiąże się z koniecznością ponownego zdawania całego egzaminu.
          </p>
        </div>
        <div class="info-block">
          <h3 class="info-block__question">Czy muszę podchodzić do egzaminu?</h3>
          <p class="info-block__answer">
            Dla uczniów technikum w nowej formule (od 2019 r., kwalifikacje INF.02 i INF.03),
            <strong>podejście do egzaminu jest obowiązkowe</strong> w celu ukończenia szkoły.
            Pamiętaj jednak, że jego wynik (nawet 0%) <strong>nie wpływa na promocję</strong>
            do następnej klasy ani na ukończenie szkoły.
          </p>
        </div>
      </div>
    </section>

    <!-- 
      =====================================================================
      Sekcja 5: Topics Section
      ---------------------------------------------------------------------
      Cel: Umożliwienie użytkownikom nauki z konkretnych działów tematycznych,
           co stanowi alternatywną ścieżkę nawigacji.
      =====================================================================
    -->
    <section class="topics-section">
      <h2 class="section-title">Ucz się z konkretnych działów</h2>
      <div class="topics-wrapper">
        <div class="topics-group">
          <h3 class="topics-group__subheading">
            <i class="topics-group__icon fas fa-laptop-code"></i>
            <span>Kwalifikacja INF.03</span>
          </h3>
          <div class="topics-grid">
            <a href="<?= url('inf03-personalizowany-test?subject[]=1') ?>" class="topic-card">HTML</a>
            <a href="<?= url('inf03-personalizowany-test?subject[]=2') ?>" class="topic-card">CSS</a>
            <a href="<?= url('inf03-personalizowany-test?subject[]=3') ?>" class="topic-card">JavaScript</a>
            <a href="<?= url('inf03-personalizowany-test?subject[]=4') ?>" class="topic-card">PHP</a>
            <a href="<?= url('inf03-personalizowany-test?subject[]=5') ?>" class="topic-card">SQL</a>
            <a href="<?= url('inf03-personalizowany-test?subject[]=6') ?>" class="topic-card">Teoria</a>
          </div>
        </div>

        <div class="topics-group">
          <h3 class="topics-group__subheading">
            <i class="topics-group__icon fas fa-cogs"></i>
            <span>Kwalifikacja INF.02</span>
            <span class="soon-badge">Wkrótce!</span>
          </h3>
          <div class="topics-grid">
            <a href="#" class="topic-card topic-card--disabled">Sprzęt komputerowy</a>
            <a href="#" class="topic-card topic-card--disabled">Systemy operacyjne</a>
            <a href="#" class="topic-card topic-card--disabled">Sieci komputerowe</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Dołączenie reużywalnego komponentu stopki -->
  <?php include 'partials/footer.php'; ?>
</body>

</html>