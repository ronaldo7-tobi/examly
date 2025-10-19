<?php

/**
 * Konfiguracja komponentu head
 */
$noIndex = true;
$noFollow = true;
$pageTitle = 'Załóż Darmowe Konto i Śledź Postępy | Examly';
$pageDescription = 'Dołącz do Examly i przygotuj się do egzaminu zawodowego. Załóż darmowe konto, aby śledzić postępy i korzystać z inteligentnych trybów nauki.';
$canonicalUrl = 'https://www.examly.pl/rejestracja';

/**
 * ========================================================================
 * Plik Widoku: Rejestracja Użytkownika
 * ========================================================================
 *
 * @description Renderuje formularz rejestracji nowego użytkownika.
 * Widok ten jest odpowiedzialny za zbieranie danych, wyświetlanie
 * błędów walidacji (np. hasła się nie zgadzają, e-mail jest zajęty)
 * oraz ponowne wypełnianie pól formularza w przypadku błędu,
 * aby poprawić doświadczenie użytkownika.
 *
 * @dependencies 
 * - partials/head.php (head)
 * - partials/navbar.php (Nawigacja)
 * - partials/footer.php (Stopka)
 * - main.css (Główne style)
 * - Font Awesome (Ikony)
 *
 * @state_variables 
 * - $errors (array): Tablica z błędami walidacji formularza.
 * - $formData (array): Tablica z danymi przesłanymi przez
 *   użytkownika, używana do ponownego wypełnienia pól.
 */
?>
<!DOCTYPE html>
<html lang="pl">
<!-- Dołączenie reużywalnego komponentu head -->
<?php include 'partials/head.php'; ?>

<body class="auth-page-background">
  <!-- Dołączenie reużywalnego komponentu head -->
  <?php include 'partials/navbar.php'; ?>

  <main>
    <!-- 
      =====================================================================
      Komponent: Formularz Rejestracji
      ---------------------------------------------------------------------
      Opis: Główny formularz umożliwiający użytkownikowi utworzenie
            nowego konta w serwisie. Dane są wysyłane metodą POST.
      =====================================================================
    -->
    <form method="POST" class="form-card">
      <div class="form-card__header">
        <h1 class="form-card__title">Stwórz nowe konto</h1>
        <p class="form-card__subtitle">Dołącz do nas i zacznij przygotowania do egzaminu!</p>
      </div>

      <!-- 
        Blok: Wyświetlanie błędów walidacji
        Cel: Jeśli kontroler zwróci błędy, są one tutaj iterowane
             i wyświetlane w widocznym dla użytkownika miejscu.
      -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert--error" role="alert">
          <ul class="alert__list">
            <?php foreach ($errors as $error): ?>
              <li class="alert__item"><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- Grupa: Pole "Imię" -->
      <div class="form-card__group">
        <label for="first_name" class="form-card__label">Imię</label>
        <!-- 
          PHP: Wypełnia pole poprzednio wprowadzoną wartością w przypadku
               błędu, aby użytkownik nie musiał wpisywać danych ponownie.
        -->
        <input type="text" id="first_name" name="first_name" class="form-card__input"
          value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>"
          placeholder="Jan" required>
      </div>

      <!-- Grupa: Pole "Nazwisko" -->
      <div class="form-card__group">
        <label for="last_name" class="form-card__label">Nazwisko</label>
        <input type="text" id="last_name" name="last_name" class="form-card__input"
          value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>"
          placeholder="Kowalski" required>
      </div>

      <!-- Grupa: Pole "Adres e-mail" -->
      <div class="form-card__group">
        <label for="email" class="form-card__label">Adres e-mail</label>
        <input type="email" id="email" name="email" class="form-card__input"
          value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
          placeholder="jankowalski@example.com"
          required>
      </div>

      <!-- Grupa: Pole "Hasło" -->
      <div class="form-card__group">
        <label for="password" class="form-card__label">Stwórz hasło</label>
        <div class="password-wrapper">
          <input type="password" id="password" name="password" class="form-card__input"
            placeholder="Stwórz solidne hasło" required>
          <i class="fas fa-eye password-toggle-icon"></i>
        </div>
        <p class="form-card__input-hint">Minimum 8 znaków, w tym wielka i mała litera, cyfra oraz znak specjalny.</p>
      </div>

      <!-- Grupa: Pole "Potwierdź hasło" -->
      <div class="form-card__group">
        <label for="confirm_password" class="form-card__label">Potwierdź hasło</label>
        <div class="password-wrapper">
          <input type="password" id="confirm_password" name="confirm_password" class="form-card__input"
            placeholder="Powtórz swoje hasło" required>
          <i class="fas fa-eye password-toggle-icon"></i>
        </div>
      </div>

      <!-- Kontener z głównym przyciskiem akcji -->
      <div class="form-card__action-container">
        <button type="submit" class="btn btn--primary btn--full-width">Zarejestruj się</button>
      </div>

      <!-- Stopka formularza z linkiem do logowania -->
      <div class="form-card__footer">
        <p>Masz już konto? <a href="<?= url('logowanie') ?>">Zaloguj się</a></p>
      </div>
    </form>
  </main>

  <?php include 'partials/footer.php'; ?>

  <script type="module" src="<?= url('js/components/show-password-toggle.js') ?>"></script>
</body>

</html>