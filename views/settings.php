<?php

/**
 * @file settings.php
 * @description
 * Widok ustawień użytkownika w architekturze MVC. 
 * Obsługuje dynamiczne renderowanie formularzy w zależności od dostawcy 
 * tożsamości (Local vs Google) oraz wyświetlanie komunikatów zwrotnych (Flash Messages).
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */

$noIndex = true;
$noFollow = true;
$pageTitle = 'Ustawienia Konta | Examly';
$pageDescription = 'Zarządzaj swoimi preferencjami i danymi konta w serwisie Examly.';
$canonicalUrl = 'https://www.examly.pl/ustawienia';

// Sprawdzenie typu zalogowanego użytkownika dla logiki UI
$isGoogleUser = ($currentUser->getAuthProvider() === 'google');
?>
<!DOCTYPE html>
<html lang="pl">
<?php include 'partials/head.php'; ?>

<body class="auth-page-background">
  <?php include 'partials/navbar.php'; ?>

  <header class="page-header">
    <div class="page-header__content">
      <h1 class="page-header__title"><span class="text-gradient">Ustawienia</span> Konta</h1>
      <p class="page-header__text">Zarządzaj swoim profilem i dbaj o bezpieczeństwo swoich danych w Examly.</p>
    </div>
  </header>

  <main class="container" style="max-width: 800px; margin: 2rem auto 4rem; padding: 0 1rem;">

    <?php
    /**
     * Blok komunikatów Flash.
     * Wyświetla błędy walidacji lub potwierdzenia sukcesu operacji.
     */
    if (isset($_SESSION['flash_message'])):
      $flash = $_SESSION['flash_message'];
      unset($_SESSION['flash_message']);
    ?>
      <div class="alert alert--<?= htmlspecialchars($flash['type']) ?>" role="alert" style="max-width: none; margin-bottom: 2rem;">
        <?php if (isset($flash['errors'])): ?>
          <ul class="alert__list">
            <?php foreach ($flash['errors'] as $error): ?>
              <li class="alert__item"><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <?= htmlspecialchars($flash['text']) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="settings-accordion" id="settings-accordion">

      <div class="settings-card <?= ($activeForm === 'name') ? 'is-open' : '' ?>">
        <div class="settings-card__header">
          <div class="settings-card__title-group">
            <h3 class="settings-card__title">Dane osobowe</h3>
            <p class="settings-card__description">Zaktualizuj swoje imię i nazwisko widoczne w profilu.</p>
          </div>
          <i class="fas fa-chevron-down settings-card__icon"></i>
        </div>
        <div class="settings-card__content">
          <form method="POST" action="<?= url('ustawienia') ?>">
            <input type="hidden" name="form_type" value="change_name">
            <div class="form-card__group">
              <label for="first_name" class="form-card__label">Imię</label>
              <input type="text" id="first_name" name="first_name" class="form-card__input"
                value="<?= htmlspecialchars($currentUser->getFirstName()) ?>" required>
            </div>
            <div class="form-card__group">
              <label for="last_name" class="form-card__label">Nazwisko</label>
              <input type="text" id="last_name" name="last_name" class="form-card__input"
                value="<?= htmlspecialchars($currentUser->getLastName()) ?>" required>
            </div>

            <?php if (!$isGoogleUser): ?>
              <div class="form-card__group">
                <label for="password_for_name" class="form-card__label">Potwierdź aktualnym hasłem</label>
                <div class="password-wrapper">
                  <input type="password" id="password_for_name" name="password" class="form-card__input" placeholder="Wpisz hasło" required>
                  <i class="fas fa-eye password-toggle-icon"></i>
                </div>
              </div>
            <?php endif; ?>

            <button type="submit" class="btn btn--primary">Zaktualizuj profil</button>
          </form>
        </div>
      </div>

      <div class="settings-card <?= ($activeForm === 'email') ? 'is-open' : '' ?>">
        <div class="settings-card__header">
          <div class="settings-card__title-group">
            <h3 class="settings-card__title">Adres e-mail</h3>
            <p class="settings-card__description">Twój aktualny adres: <strong><?= htmlspecialchars($currentUser->getEmail()) ?></strong></p>
          </div>
          <i class="fas fa-chevron-down settings-card__icon"></i>
        </div>
        <div class="settings-card__content">
          <?php if ($isGoogleUser): ?>
            <p class="info-card__message" style="text-align: left; font-size: 0.9rem;">
              Twoje konto jest powiązane z usługą Google. Adres e-mail można zmienić tylko w ustawieniach konta Google.
            </p>
          <?php else: ?>
            <p class="info-card__message" style="text-align: left; font-size: 0.9rem; margin-bottom: 1.5rem;">
              Po zmianie adresu konieczna będzie ponowna weryfikacja konta.
            </p>
            <form method="POST" action="<?= url('ustawienia') ?>">
              <input type="hidden" name="form_type" value="change_email">
              <div class="form-card__group">
                <label for="new_email" class="form-card__label">Nowy adres e-mail</label>
                <input type="email" id="new_email" name="new_email" class="form-card__input" placeholder="np. jan.nowak@gmail.com" required>
              </div>
              <div class="form-card__group">
                <label for="password_for_email" class="form-card__label">Potwierdź hasłem</label>
                <div class="password-wrapper">
                  <input type="password" id="password_for_email" name="password" class="form-card__input" placeholder="Wpisz hasło" required>
                  <i class="fas fa-eye password-toggle-icon"></i>
                </div>
              </div>
              <button type="submit" id="changeEmailBtn" class="btn btn--primary"
                data-remaining="<?= $emailChangeRemaining ?? 0 ?>"
                <?= ($emailChangeRemaining ?? 0) > 0 ? 'disabled' : '' ?>>
                <?= ($emailChangeRemaining ?? 0) > 0 ? "Wyślij ponownie za {$emailChangeRemaining}s" : "Zmień e-mail" ?>
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!$isGoogleUser): ?>
        <div class="settings-card <?= ($activeForm === 'password') ? 'is-open' : '' ?>">
          <div class="settings-card__header">
            <div class="settings-card__title-group">
              <h3 class="settings-card__title">Zmiana hasła</h3>
              <p class="settings-card__description">Zadbaj o bezpieczeństwo swojego konta regularnie zmieniając hasło.</p>
            </div>
            <i class="fas fa-chevron-down settings-card__icon"></i>
          </div>
          <div class="settings-card__content">
            <p>Hasło powinno zawierać:
            <ul>
              <li>Minimum 8 znaków</li>
              <li>Co najmniej jedną wielką literę</li>
              <li>Co najmniej jedną cyfrę</li>
              <li>Co najmniej jeden znak specjalny (np. !@#$%)</li>
            </ul>
            </p>
            <form method="POST" action="<?= url('ustawienia') ?>">
              <input type="hidden" name="form_type" value="change_password">
              <div class="form-card__group">
                <label for="current_password" class="form-card__label">Obecne hasło</label>
                <div class="password-wrapper">
                  <input type="password" id="current_password" name="current_password" class="form-card__input" placeholder="Hasło" required>
                  <i class="fas fa-eye password-toggle-icon"></i>
                </div>
              </div>
              <div class="form-card__group">
                <label for="new_password" class="form-card__label">Nowe hasło</label>
                <div class="password-wrapper">
                  <input type="password" id="new_password" name="new_password" class="form-card__input" placeholder="Nowe hasło" required>
                  <i class="fas fa-eye password-toggle-icon"></i>
                </div>
              </div>
              <div class="form-card__group">
                <label for="confirm_new_password" class="form-card__label">Powtórz nowe hasło</label>
                <div class="password-wrapper">
                  <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-card__input" placeholder="Powtórz nowe hasło" required>
                  <i class="fas fa-eye password-toggle-icon"></i>
                </div>
              </div>
              <button type="submit" class="btn btn--primary">Zmień hasło</button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <div class="settings-card <?= ($activeForm === 'delete') ? 'is-open' : '' ?>">
        <div class="settings-card__header">
          <div class="settings-card__title-group">
            <h3 class="settings-card__title">Zarządzanie kontem</h3>
            <p class="settings-card__description">Opcje związane z trwałym usunięciem konta.</p>
          </div>
          <i class="fas fa-chevron-down settings-card__icon"></i>
        </div>
        <div class="settings-card__content">
          <div class="alert alert--error" style="margin-bottom: 1.5rem; max-width: none;">
            <strong>Uwaga:</strong> Usunięcie konta spowoduje bezpowrotną utratę wszystkich postępów w nauce oraz statystyk egzaminacyjnych.
          </div>
          <form method="POST" action="<?= url('usun-konto') ?>">
            <?php if (!$isGoogleUser): ?>
              <div class="form-card__group">
                <label for="password_for_delete" class="form-card__label">Potwierdź hasłem, aby usunąć konto</label>
                <div class="password-wrapper">
                  <input type="password" id="password_for_delete" name="password" class="form-card__input" placeholder="Wpisz hasło" required>
                  <i class="fas fa-eye password-toggle-icon"></i>
                </div>
              </div>
            <?php else: ?>
              <p class="form-card__group">Potwierdź chęć usunięcia konta klikając poniższy przycisk.</p>
            <?php endif; ?>
            <button type="submit" class="btn" style="background-color: #dc2626; color: white; width: 100%;">
              Usuń konto na zawsze
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <?php include 'partials/footer.php'; ?>

  <script type="module" src="<?= url('js/components/SettingsAccordion.js') ?>"></script>
  <script type="module" src="<?= url('js/components/show-password-toggle.js') ?>"></script>
  <script type="module">
    import CountdownButton from '<?= url('js/components/CountdownButton.js') ?>';
    <?php if (!$isGoogleUser): ?>
      new CountdownButton('changeEmailBtn');
    <?php endif; ?>
  </script>
</body>

</html>