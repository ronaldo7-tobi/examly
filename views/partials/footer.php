<footer class="footer">
    <div class="footer__content">
        <!-- Sekcja: Nawigacja po stronie -->
        <div class="footer__section">
            <h4 class="footer__heading">Examly</h4>
            <ul class="footer__list">
                <li class="footer__item"><a href="/" class="footer__link">Strona główna</a></li>
                <li class="footer__item"><a href="/register" class="footer__link">Rejestracja</a></li>
                <li class="footer__item"><a href="/login" class="footer__link">Logowanie</a></li>
                <li class="footer__item"><a href="/statistics" class="footer__link">Statystyki</a></li>
            </ul>
        </div>

        <!-- Sekcja: Materiały -->
        <div class="footer__section">
            <h4 class="footer__heading">Materiały</h4>
            <ul class="footer__list">
                <li class="footer__item"><a href="/inf03_course" class="footer__link">Kurs INF.03</a></li>
                <li class="footer__item"><a href="/inf03_one_question" class="footer__link">Jedno pytanie</a></li>
                <li class="footer__item"><a href="/inf03_test" class="footer__link">Egzamin próbny</a></li>
            </ul>
        </div>

        <!-- Sekcja: Kontakt -->
        <div class="footer__section">
            <h4 class="footer__heading">Kontakt</h4>
            <ul class="footer__list">
                <li class="footer__item">
                    <i class="footer__icon fas fa-map-marker-alt"></i>
                    ul. Jabłonkowa 28, 64-113 Osieczna
                </li>
                <li class="footer__item">
                    <i class="footer__icon fas fa-envelope"></i>
                    <a href="mailto:kontakt@examly.pl" class="footer__link">kontakt@examly.pl</a>
                </li>
                <li class="footer__item">
                    <i class="footer__icon fas fa-phone"></i>
                    <a href="tel:+48123456789" class="footer__link">+48 784 803 409</a>
                </li>
            </ul>
            <div class="footer__socials">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <!-- Dolna część stopki -->
    <div class="footer__bottom">
        <p>&copy; <?= date("Y") ?> Examly. Wszelkie prawa zastrzeżone.</p>
    </div>
</footer>