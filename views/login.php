<?php

/**
 * Konfiguracja komponentu head
 */
$noIndex = true;
$noFollow = true;
$pageTitle = 'Logowanie do Konta | Examly';
$pageDescription = 'Zaloguj się na swoje konto w Examly, aby uzyskać dostęp do zapisanych wyników, statystyk i kontynuować naukę do egzaminu zawodowego.';
$canonicalUrl = 'https://www.examly.pl/logowanie';

/**
 * ========================================================================
 * Plik Widoku: Logowanie Użytkownika
 * ========================================================================
 *
 * @description Renderuje formularz logowania. Ten widok jest odpowiedzialny
 * za wyświetlanie pól do wprowadzenia danych, obsługę i prezentację
 * błędów walidacji oraz wyświetlanie jednorazowych komunikatów
 * "flash" (np. po pomyślnej rejestracji).
 *
 * @dependencies 
 * - partials/head.php (head)
 * - partials/navbar.php (Nawigacja)          
 * - partials/footer.php (Stopka)            
 * - main.css (Główne style)     
 * - Font Awesome (Ikony)
 *
 * @state_variables 
 * - $_SESSION['flash_message'] (array|null): Jednorazowy komunikat
 *   przechowywany w sesji. Zawiera 'type' (np. 'success')
 *   i 'text'. Jest usuwany po wyświetleniu.         
 * - $errors (array): Tablica z błędami walidacji formularza,
 *   które są wyświetlane bezpośrednio nad polami.            
 * - $formData (array): Tablica z danymi przesłanymi przez
 *   użytkownika, używana do ponownego wypełnienia pól w przypadku błędu.
 */
?>
<!DOCTYPE html>
<html lang="pl">
<!-- Dołączenie reużywalnego komponentu head -->
<?php include 'partials/head.php'; ?>

<body>
  <!-- Dołączenie reużywalnego komponentu head -->
  <?php include 'partials/navbar.php'; ?>

  <main>
    <!-- 
      =====================================================================
      Komponent: Komunikat Flash (Flash Message)
      ---------------------------------------------------------------------
      Cel: Wyświetlanie jednorazowych powiadomień dla użytkownika, np.
           "Rejestracja przebiegła pomyślnie. Możesz się teraz zalogować."
           Komunikat jest odczytywany z sesji i natychmiast usuwany,
           aby nie pojawił się ponownie po odświeżeniu strony.
      =====================================================================
    -->
    <?php if (isset($_SESSION['flash_message']) && is_array($_SESSION['flash_message'])): ?>
      <div class="centered">
        <div class="alert alert--<?= htmlspecialchars($_SESSION['flash_message']['type']) ?>" role="alert">
          <?= htmlspecialchars($_SESSION['flash_message']['text']) ?>
        </div>
      </div>
      <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <!-- 
      =====================================================================
      Komponent: Formularz Logowania
      ---------------------------------------------------------------------
      Opis: Główny formularz umożliwiający użytkownikowi zalogowanie się
            do serwisu. Dane są wysyłane metodą POST.
      =====================================================================
    -->
    <form method="POST" class="form-card">
      <div class="form-card__header">
        <h1 class="form-card__title">Witaj ponownie!</h1>
        <p class="form-card__subtitle">Zaloguj się, aby kontynuować naukę.</p>
      </div>

      <!-- 
        Blok: Wyświetlanie błędów walidacji
        Cel: Jeśli kontroler zwróci błędy (np. pusty e-mail, złe hasło),
             są one tutaj iterowane i wyświetlane w widocznym miejscu.
      -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert--error" role="alert">
          <ul class="alert__list">
            <?php foreach ($errors as $error): ?>
              <li class="alert__item"><?= $error ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- Grupa: Pole e-mail -->
      <div class="form-card__group">
        <label for="email" class="form-card__label">Adres e-mail</label>
        <!-- 
          PHP: Wypełnia pole e-mail poprzednio wprowadzoną wartością
               w przypadku błędu walidacji, aby użytkownik nie musiał
               wpisywać go ponownie.
        -->
        <input type="email" id="email" name="email" class="form-card__input"
          value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
          placeholder="Twój adres e-mail" required>
      </div>

      <!-- Grupa: Pole hasła -->
      <div class="form-card__group">
        <label for="password" class="form-card__label">Hasło</label>
        <div class="password-wrapper">
          <input type="password" id="password" name="password" class="form-card__input"
            placeholder="Twoje hasło" required>
          <i class="fas fa-eye password-toggle-icon"></i>
        </div>
      </div>

      <!-- Kontener z głównym przyciskiem akcji -->
      <div class="form-card__action-container">
        <button type="submit" class="btn btn--primary btn--full-width">Zaloguj się</button>
      </div>

      <div style="text-align: center; margin: 1.5rem 0; color: var(--muted-text);">lub</div>

      <div class="form-card__action-container" style="margin-top: 0; margin-bottom: 2rem;">
          <a href="<?= url('auth/google') ?>" class="btn btn--secondary btn--full-width">
              <i class="fab fa-google" style="margin-right: 0.5rem;"></i> Zaloguj się przez Google
          </a>
      </div>

      <!-- Stopka formularza z dodatkowymi linkami -->
      <div class="form-card__footer">
        <p>Nie masz konta? <a href="<?= url('rejestracja') ?>">Stwórz je teraz</a></p>
        <p><a href="<?= url('reset-hasla') ?>">Nie pamiętasz hasła?</a></p>
      </div>
    </form>
  </main>

  <?php include 'partials/footer.php'; ?>

  <script type="module" src="<?= url('js/components/show-password-toggle.js') ?>"></script>
</body>

</html>