<!-- 
  =======================================================================
  Komponent: Kontener na powiadomienia (Toast)
  -----------------------------------------------------------------------
  Przeznaczenie: Globalny kontener do dynamicznego renderowania 
                 powiadomień typu "toast" (np. o sukcesie, błędzie).
                 Umieszczony poza główną strukturą stopki, aby zapewnić 
                 poprawne pozycjonowanie i separację logiki.
  =======================================================================
-->
<div id="toast-container" class="toast-container"></div>

<!-- 
  =======================================================================
  Komponent: Główna stopka strony (Footer)
  -----------------------------------------------------------------------
  Opis: Centralny element stopki, zawierający kluczowe linki, dane 
        kontaktowe i informacje o prawach autorskich. Struktura oparta 
        na metodologii BEM (np. .footer__section) dla zachowania 
        przejrzystości i unikania konfliktów w stylach.
  =======================================================================
-->
<footer class="footer">
  <!-- Główny kontener grupujący sekcje stopki. Używany do centrowania i zarządzania układem (np. przez Flexbox lub Grid). -->
  <div class="footer__content">
    
    <!-- Sekcja 1: Podstawowa nawigacja serwisu -->
    <div class="footer__section">
      <h4 class="footer__heading">Examly</h4>
      <ul class="footer__list">
        <li class="footer__item"><a href="<?= url('/') ?>" class="footer__link">Strona główna</a></li>
        <li class="footer__item"><a href="<?= url('rejestracja') ?>" class="footer__link">Rejestracja</a></li>
        <li class="footer__item"><a href="<?= url('logowanie') ?>" class="footer__link">Logowanie</a></li>
        <li class="footer__item"><a href="<?= url('statystyki') ?>" class="footer__link">Statystyki</a></li>
      </ul>
    </div>

    <!-- Sekcja 2: Linki do materiałów edukacyjnych -->
    <div class="footer__section">
      <h4 class="footer__heading">Materiały</h4>
      <ul class="footer__list">
        <li class="footer__item"><a href="<?= url('kurs-inf03') ?>" class="footer__link">Kurs INF.03</a></li>
        <li class="footer__item"><a href="<?= url('inf03-jedno-pytanie') ?>" class="footer__link">Jedno pytanie</a></li>
        <li class="footer__item"><a href="<?= url('inf03-test') ?>" class="footer__link">Egzamin próbny</a></li>
      </ul>
    </div>

    <!-- Sekcja 3: Dane kontaktowe i media społecznościowe -->
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
          <a href="tel:+48784803409" class="footer__link">+48 784 803 409</a>
        </li>
      </ul>
      <!-- Kontener z linkami do profili w mediach społecznościowych -->
      <div class="footer__socials">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>

  <!-- 
    Dolny pasek stopki (Copyright)
    Przeznaczenie: Oddzielna sekcja na informacje o prawach autorskich.
                   Wydzielona dla ułatwienia stylowania (np. inne tło,
                   pełna szerokość). Zawiera suwak do zmiany motywu aplikacji.
  -->
  <div class="footer__bottom">
    <p>&copy; <?= date('Y') ?> Examly. Wszelkie prawa zastrzeżone.</p>
    <div class="centered">
      <div class="theme-switcher">
        <input type="checkbox" id="theme-toggle-checkbox">
        <label for="theme-toggle-checkbox" class="theme-toggle-label" aria-label="Zmień motyw">
          <i class="fas fa-moon"></i>
          <i class="fas fa-sun"></i>
          <span class="ball"></span>
        </label>
      </div>
    </div>
  </div>
</footer>
<!-- Koniec Komponentu Stopki -->

<!-- Skrypt do zmiany motywu strony -->
<script type="module" src="<?= url('js/components/ThemeSwitcher.js') ?>"></script>