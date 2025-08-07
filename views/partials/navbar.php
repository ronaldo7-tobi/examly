<nav class="main-nav">
    <!-- Sekcja Logo -->
    <div class="main-nav__logo">
        <a href="/examly/public/" aria-label="Strona główna Examly">
            <img src="/path/to/your/logo.png" alt="Logo Examly" class="logo">
        </a>
    </div>

    <!-- Sekcja Linków -->
    <ul class="main-nav__links">
        <!-- Pozycja z Dropdown: INF.03 -->
        <li class="main-nav__item dropdown">
            <a href="#inf03" class="main-nav__link">
                <i class="main-nav__icon fas fa-laptop-code"></i>
                <span>INF.03</span>
            </a>
            <ul class="dropdown__menu">
                <li class="dropdown__item">
                    <a href="inf03_one_question" class="dropdown__link">
                        <i class="dropdown__icon fas fa-question-circle"></i> Jedno Pytanie
                    </a>
                </li>
                <li class="dropdown__item">
                    <a href="inf03_personalized_test" class="dropdown__link">
                        <i class="dropdown__icon fas fa-sliders-h"></i> Spersonalizowany Test
                    </a>
                </li>
                <li class="dropdown__item">
                    <a href="inf03_test" class="dropdown__link">
                        <i class="dropdown__icon fas fa-file-alt"></i> Egzamin próbny
                    </a>
                </li>
            </ul>
        </li>

        <!-- Pozycja z Dropdown: Kursy -->
        <li class="main-nav__item dropdown">
            <a href="courses" class="main-nav__link">
                <i class="main-nav__icon fas fa-graduation-cap"></i>
                <span>Kursy</span>
            </a>
            <ul class="dropdown__menu">
                <li class="dropdown__item">
                    <a href="inf03_course" class="dropdown__link">
                        <i class="dropdown__icon fas fa-chalkboard-teacher"></i> Kurs INF.03
                    </a>
                </li>
            </ul>
        </li>

        <?php if (!$isUserLoggedIn): ?>
            <!-- Linki dla niezalogowanego użytkownika -->
            <li class="main-nav__item">
                <a href="register" class="main-nav__link">
                    <i class="main-nav__icon fas fa-user-plus"></i>
                    <span>Zarejestruj się</span>
                </a>
            </li>
            <li class="main-nav__item">
                <a href="login" class="main-nav__link">
                    <i class="main-nav__icon fas fa-sign-in-alt"></i>
                    <span>Zaloguj się</span>
                </a>
            </li>
        <?php else: ?>
            <!-- Linki dla zalogowanego użytkownika -->
            <li class="main-nav__item dropdown">
                <a href="/statistics" class="main-nav__link" aria-label="Profil użytkownika">
                    <!-- Możesz tu użyć avatara użytkownika z sesji -->
                    <img src="../../public/images/user.png" alt="Avatar użytkownika" class="main-nav__user-avatar">
                </a>
                <ul class="dropdown__menu">
                    <li class="dropdown__item">
                        <a href="statistics" class="dropdown__link">
                            <i class="dropdown__icon fas fa-chart-bar"></i> Statystyki
                        </a>
                    </li>
                    <li class="dropdown__item">
                        <a href="settings" class="dropdown__link">
                            <i class="dropdown__icon fas fa-cog"></i> Ustawienia
                        </a>
                    </li>
                    <li class="dropdown__item">
                        <a href="logout" class="dropdown__link">
                            <i class="dropdown__icon fas fa-sign-out-alt"></i> Wyloguj się
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<script>
    // Globalny stan aplikacji, dostępny dla wszystkich modułów JavaScript na każdej stronie.
    window.examlyAppState = {
        isUserLoggedIn: <?= (isset($_SESSION['user']) && $_SESSION['user'] instanceof User) ? 'true' : 'false' ?>
    };
</script>