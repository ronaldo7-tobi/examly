<!-- 
  =======================================================================
  Komponent: Główny pasek nawigacyjny (main-nav)
  -----------------------------------------------------------------------
  Opis: Centralny element nawigacyjny strony, widoczny na górze każdej 
        podstrony. Zawiera logo, główne linki nawigacyjne (w tym menu
        rozwijane) oraz dynamicznie renderowane przyciski akcji 
        (logowanie/rejestracja lub profil użytkownika) w zależności 
        od stanu zalogowania.
  Struktura: Używa metodologii BEM (np. .main-nav__item, .dropdown__menu).
  =======================================================================
-->
<nav class="main-nav">
  <!-- Sekcja 1: Logo i link do strony głównej -->
  <div class="main-nav__logo">
    <a href="<?= url() ?>" aria-label="Strona główna Examly">
      <img src="/path/to/your/logo.png" alt="Logo Examly" class="logo">
    </a>
  </div>

  <!-- Sekcja 2: Główna lista linków nawigacyjnych -->
  <ul class="main-nav__links">
    
    <!-- Element nawigacji z menu rozwijanym (dropdown) -->
    <li class="main-nav__item dropdown">
      <a href="<?= url('#egzaminy') ?>" class="main-nav__link">
        <i class="main-nav__icon fas fa-laptop-code"></i>
        <span>INF.03</span>
      </a>
      <!-- Kontener menu rozwijanego; jego widoczność jest kontrolowana przez CSS/JS -->
      <ul class="dropdown__menu">
        <li class="dropdown__item">
          <a href="<?= url('inf03-jedno-pytanie') ?>" class="dropdown__link">
            <i class="dropdown__icon fas fa-question-circle"></i> Jedno Pytanie
          </a>
        </li>
        <li class="dropdown__item">
          <a href="<?= url('inf03-personalizowany-test') ?>" class="dropdown__link">
            <i class="dropdown__icon fas fa-sliders-h"></i> Spersonalizowany Test
          </a>
        </li>
        <li class="dropdown__item">
          <a href="<?= url('inf03-test') ?>" class="dropdown__link">
            <i class="dropdown__icon fas fa-file-alt"></i> Egzamin próbny
          </a>
        </li>
      </ul>
    </li>

    <!-- Kolejny element nawigacji z menu rozwijanym -->
    <li class="main-nav__item dropdown">
      <a href="<?= url('kursy') ?>" class="main-nav__link">
        <i class="main-nav__icon fas fa-graduation-cap"></i>
        <span>Kursy</span>
      </a>
      <!-- Modyfikator BEM `--right` może służyć do wyrównania tego menu do prawej krawędzi rodzica -->
      <ul class="dropdown__menu dropdown__menu--right">
        <li class="dropdown__item">
          <a href="<?= url('kurs-inf03') ?>" class="dropdown__link">
            <i class="dropdown__icon fas fa-chalkboard-teacher"></i> Kurs INF.03
          </a>
        </li>
      </ul>
    </li>

    <!-- 
      =====================================================================
      Blok warunkowy PHP: Dynamiczne renderowanie linków
      ---------------------------------------------------------------------
      Cel: Wyświetla odpowiednie przyciski w zależności od tego, czy 
           użytkownik jest zalogowany. Zmienna $isUserLoggedIn jest 
           ustawiana w logice serwera.
      =====================================================================
    -->
    <?php if (!$isUserLoggedIn): ?>
      <!-- Wariant dla użytkownika NIEZALOGOWANEGO -->
      <li class="main-nav__item">
        <a href="<?= url('rejestracja') ?>" class="nav-button nav-button--register">
          <i class="main-nav__icon fas fa-user-plus"></i>
          <span>Zarejestruj się</span>
        </a>
      </li>
      <li class="main-nav__item">
        <a href="<?= url('logowanie') ?>" class="nav-button nav-button--login">
          <i class="main-nav__icon fas fa-sign-in-alt"></i>
          <span>Zaloguj się</span>
        </a>
      </li>
    <?php else: ?>
      <!-- Wariant dla użytkownika ZALOGOWANEGO -->
      <li class="main-nav__item dropdown">
        <a href="<?= url('statystyki') ?>" class="main-nav__link" aria-label="Profil użytkownika">
          <img src="<?= url('images/user.png') ?>" alt="Avatar użytkownika" class="main-nav__user-avatar">
        </a>
        <ul class="dropdown__menu dropdown__menu--right">
          <li class="dropdown__item">
            <a href="<?= url('statystyki') ?>" class="dropdown__link">
              <i class="dropdown__icon fas fa-chart-bar"></i> Statystyki
            </a>
          </li>
          <li class="dropdown__item">
            <a href="<?= url('ustawienia') ?>" class="dropdown__link">
              <i class="dropdown__icon fas fa-cog"></i> Ustawienia
            </a>
          </li>
          <li class="dropdown__item">
            <a href="<?= url('wyloguj') ?>" class="dropdown__link">
              <i class="dropdown__icon fas fa-sign-out-alt"></i> Wyloguj się
            </a>
          </li>
        </ul>
      </li>
    <?php endif; ?>
  </ul>
</nav>
<!-- /Koniec Komponentu Nawigacji -->

<!-- 
  =======================================================================
  Skrypt: Inicjalizacja stanu aplikacji po stronie klienta
  -----------------------------------------------------------------------
  Cel: Przekazuje kluczowe dane z PHP (backend) do JavaScript (frontend).
       Utworzenie globalnego obiektu `window.examlyAppState` pozwala 
       na łatwy i spójny dostęp do stanu zalogowania użytkownika 
       w różnych skryptach JS bez potrzeby dodatkowych zapytań AJAX.
  =======================================================================
-->
<script>
  window.examlyAppState = {
    isUserLoggedIn: <?= isset($_SESSION['user']) && $_SESSION['user'] instanceof User ? 'true' : 'false' ?>,
    baseUrl: '<?= BASE_URL ?>'
  };
</script>