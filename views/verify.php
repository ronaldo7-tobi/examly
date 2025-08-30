<?php
/**
 * Konfiguracja komponentu head
 */
$noIndex = true; 
$noFollow = true;
$pageTitle = 'Weryfikacja Konta | Examly';
$pageDescription = 'Proces weryfikacji konta w serwisie Examly i jego wynik.';
$canonicalUrl = 'https://www.examly.pl/weryfikacja';

/**
 * ========================================================================
 * Plik Widoku: Wynik Weryfikacji E-mail
 * ========================================================================
 *
 * @description Renderuje finalną stronę statusu po tym, jak użytkownik
 * kliknie link weryfikacyjny w swojej wiadomości e-mail. Widok ten
 * dynamicznie wyświetla jeden z dwóch wariantów (sukces lub błąd)
 * w zależności od wyniku procesu weryfikacji obsłużonego
 * w kontrolerze.
 *
 * @dependencies
 * - partials/head.php (head)
 * - main.css (Główne style)
 *
 * @state_variables 
 * - $status (string): Wynik weryfikacji przekazany z kontrolera.
 *   Oczekiwana wartość to 'success' lub jakakolwiek inna oznaczająca błąd.
 * - $message (string): Konkretny komunikat do wyświetlenia
 *   użytkownikowi, np. "Twoje konto jest aktywne." lub "Token weryfikacyjny wygasł.".
 */
?>
<!DOCTYPE html>
<html lang="pl">
<!-- Dołączenie reużywalnego komponentu head -->
<?php include 'partials/head.php'; ?>

<body>
  <main>
    <!-- 
      =====================================================================
      Komponent: Karta Informacyjna (Info Card)
      ---------------------------------------------------------------------
      Opis: Wycentrowana karta, która wyświetla wynik operacji.
            Jej treść i wygląd (ikona, kolorystyka) zależą od
            zmiennej $status.
      =====================================================================
    -->
    <div class="info-card--centered-fullscreen">
      <div class="info-card">

        <!-- 
          Blok warunkowy PHP: Renderowanie wariantu sukcesu lub błędu.
          Kontroluje całą zawartość karty informacyjnej.
        -->
        <?php if ($status === 'success'): ?>

          <!-- WARIANT: SUKCES -->
          <div class="info-card__icon info-card__icon--success">
            <i class="fas fa-check-circle"></i>
          </div>
          <h1 class="info-card__title">Weryfikacja zakończona!</h1>
          <p class="info-card__message"><?= htmlspecialchars($message) ?></p>
          <div class="info-card__actions">
            <a href="<?= url('logowanie') ?>" class="btn btn--primary btn--full-width">Przejdź do logowania</a>
          </div>
        
        <?php else: ?>

          <!-- WARIANT: BŁĄD -->
          <div class="info-card__icon info-card__icon--error">
            <i class="fas fa-times-circle"></i>
          </div>
          <h1 class="info-card__title">Wystąpił błąd</h1>
          <p class="info-card__message"><?= htmlspecialchars($message) ?></p>
          <div class="info-card__actions">
            <a href="<?= url('/') ?>" class="btn btn--secondary btn--full-width">Wróć na stronę główną</a>
          </div>

        <?php endif; ?>

      </div>
    </div>
  </main>
</body>
</html>