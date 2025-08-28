<?php
/**
 * Konfiguracja komponentu head
 */
$noIndex = true;
$noFollow = true; 
$pageTitle = 'Autoryzacja adresu e-mail | Examly';
$pageDescription = 'Proces weryfikacji konta w serwisie Examly.';
$canonicalUrl = 'https://www.examly.pl/autoryzacja-email';

/**
 * ========================================================================
 * Plik Widoku: Weryfikacja Adresu E-mail
 * ========================================================================
 *
 * @description Wyświetla stronę informacyjną dla użytkownika po rejestracji,
 * instruując go, aby sprawdził swoją skrzynkę e-mail w celu
 * aktywacji konta. Kluczową funkcją tego widoku jest przycisk
 * "Wyślij ponownie", który posiada mechanizm cooldown
 * (odliczania), zarządzany wspólnie przez PHP i JavaScript.
 *
 * @dependencies 
 * - partials/head.php (head)
 * - partials/navbar.php (Nawigacja)
 * - partials/footer.php (Stopka)
 * - main.css (Główne style)
 * - Font Awesome (Ikony)
 * - verification/index.js (Skrypt JS do obsługi odliczania)
 *
 * @state_variables 
 * - $flashMessage (array|null): Jednorazowy komunikat z sesji,
 *   np. o pomyślnym ponownym wysłaniu e-maila.
 * - $remaining (int): Liczba sekund pozostała do możliwości
 *   ponownego wysłania e-maila. Przekazywana z kontrolera.
 */
?>
<!DOCTYPE html>
<html lang="pl">
<!-- Dołączenie reużywalnego komponentu head -->
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <!-- Podstawowe meta tagi i zasoby -->
  <title>Examly - weryfikacja E-mail</title>
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="canonical" href="https://www.examly.pl/">
  <link rel="stylesheet" href="../public/scss/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <!-- Dołączenie reużywalnego komponentu nawigacji -->
  <?php include 'partials/navbar.php'; ?>

  <main>
    <!-- 
      =====================================================================
      Komponent: Karta Informacyjna (Info Card)
      ---------------------------------------------------------------------
      Opis: Wycentrowana karta służąca do wyświetlania ważnych komunikatów
            lub instrukcji dla użytkownika poza standardowym przepływem
            aplikacji.
      =====================================================================
    -->
    <div class="info-card--centered-fullscreen"> 
      <div class="info-card">
        <div class="info-card__icon info-card__icon--info">
          <i class="fas fa-envelope-open-text"></i>
        </div>
        <h1 class="info-card__title">Sprawdź swoją skrzynkę e-mail</h1>
        <p class="info-card__message">
          Aby zakończyć rejestrację, kliknij w link aktywacyjny, który wysłaliśmy na Twój adres e-mail.
        </p>

        <!-- Blok na jednorazowe komunikaty (np. "E-mail wysłano ponownie.") -->
        <?php if (isset($flashMessage) && is_array($flashMessage)): ?>
          <div class="alert alert--<?= htmlspecialchars($flashMessage['type']) ?>" role="alert">
            <?= htmlspecialchars($flashMessage['text']) ?>
          </div>
        <?php endif; ?>

        <!-- 
          Akcje karty: Formularz do ponownego wysłania e-maila
          Opis: Umożliwia użytkownikowi ponowne wysłanie linku aktywacyjnego.
                Przycisk jest nieaktywny, jeśli trwa okres cooldown.
        -->
        <div class="info-card__actions">
          <form method="GET" action="/examly/public/verify_email">
            <input type="hidden" name="send" value="true">
            <!-- 
              Przycisk z logiką cooldown:
              - `data-remaining`: Przechowuje początkowy czas odliczania dla JS.
              - `disabled`: Atrybut jest dodawany przez PHP, jeśli czas odliczania jest > 0.
              - Treść przycisku: Zmienia się w zależności od tego, czy odliczanie trwa.
              - Interaktywność (odliczanie i aktywacja) jest zarządzana przez `verification/index.js`.
            -->
            <button 
              type="submit"
              id="resendButton" 
              class="btn btn--primary btn--full-width"
              data-remaining="<?= $remaining ?>"
              <?= $remaining > 0 ? 'disabled' : '' ?>
            >
              <?php if ($remaining > 0): ?>
                Wyślij ponownie za <span id="countdown"><?= $remaining ?></span>s
              <?php else: ?>
                Wyślij ponownie e-mail
              <?php endif; ?>
            </button>
          </form>
        </div>
        
        <p class="info-card__message" style="margin-top: 1rem; font-size: 0.9rem;">
          Nie widzisz wiadomości? Sprawdź folder SPAM.
        </p>
      </div>
    </div>
  </main>

  <?php include 'partials/footer.php'; ?>

  <!-- Skrypt odpowiedzialny za logikę odliczania na przycisku "Wyślij ponownie" -->
  <script type="module" src="/examly/public/js/features/verification/index.js"></script>
</body>
</html>