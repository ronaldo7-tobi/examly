<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$logged = false;
if(isset($_SESSION['user'])) {
    $logged = true;
}
?>
<nav>
    <a href="/examly/public/">
        <img src="logo.png" alt="logo" class="logo">
    </a>
    <ul class="nav-links">
        <li class="dropdown">
            <a href="#inf03" class="nav-link">
                <i class="fas fa-laptop-code"></i> INF.03
            </a>
            <ul class="dropdown-menu">
                <li><a href="inf03_one_question"><i class="fas fa-question-circle"></i> Jedno Pytanie</a></li>
                <li><a href="inf03_personalized_test"><i class="fas fa-sliders-h"></i> Spersonalizowany Test</a></li>
                <li><a href="inf03_test"><i class="fas fa-file-alt"></i> Egzamin próbny</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="#courses">
                <i class="fas fa-graduation-cap"></i> Kursy
            </a>
            <ul class="dropdown-menu">
                <li><a href="inf03_course"><i class="fas fa-chalkboard-teacher"></i> Kurs INF.03</a></li>
            </ul>
        </li>
        <?php if(!$logged): ?>
            <li>
                <a href="register">
                    <i class="fas fa-user"></i> Zarejestruj się / Zaloguj się
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="statistics">
                    <img src="/../../public/images/user.png" alt="user" class="user_image">
                </a>
            </li>
            <li>
                <a href="logout">
                    Wyloguj się
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>