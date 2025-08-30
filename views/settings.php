<?php
$noIndex = true;
$noFollow = true;
$pageTitle = 'Ustawienia Konta | Examly';
$pageDescription = 'Zarządzaj swoimi preferencjami i danymi konta w serwisie Examly.';
$canonicalUrl = 'https://www.examly.pl/ustawienia';
?>
<!DOCTYPE html>
<html lang="pl">
<!-- Dołączenie reużywalnego komponentu head -->
<?php include 'partials/head.php'; ?>

<body>
  <?php include 'partials/navbar.php'; ?>

  <header class="page-header">
    <div class="page-header__content">
      <h1 class="page-header__title"><span class="text-gradient">Ustawienia</span> Konta</h1>
      <p class="page-header__text">Dostosuj swoje konto do własnych potrzeb i zadbaj o bezpieczeństwo swoich danych.</p>
    </div>
  </header>

  <main class="container" style="max-width: 800px; margin: 2rem auto 4rem; padding: 0 1rem;">

    <?php
    // Blok do obsługi i wyświetlania komunikatów Flash
    if (isset($_SESSION['flash_message'])):
      $flash = $_SESSION['flash_message'];
      unset($_SESSION['flash_message']); // Usuń komunikat po wyświetleniu
    ?>
      <div class="alert alert--<?= htmlspecialchars($flash['type']) ?>" role="alert" style="max-width: none;">
        <?php if (isset($flash['errors'])): // Obsługa listy błędów 
        ?>
          <ul class="alert__list">
            <?php foreach ($flash['errors'] as $error): ?>
              <li class="alert__item"><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: // Obsługa pojedynczego komunikatu 
        ?>
          <?= htmlspecialchars($flash['text']) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="settings-accordion" id="settings-accordion">
      <div class="settings-card <?= ($activeForm === 'name') ? 'is-open' : '' ?>">
        <div class="settings-card__header">
          <div class="settings-card__title-group">
            <h3 class="settings-card__title">Dane osobowe</h3>
            <p class="settings-card__description">Zmień swoje imię i nazwisko.</p>
          </div>
          <i class="fas fa-chevron-down settings-card__icon"></i>
        </div>
        <div class="settings-card__content">
          <form method="POST">
            <input type="hidden" name="form_type" value="change_name">
            <div class="form-card__group">
              <label for="first_name" class="form-card__label">Imię</label>
              <input type="text" id="first_name" name="first_name" class="form-card__input"
                value="<?= htmlspecialchars($currentUser->getName()) ?>" placeholder="Twoje imię" required>
            </div>
            <div class="form-card__group">
              <label for="last_name" class="form-card__label">Nazwisko</label>
              <input type="text" id="last_name" name="last_name" class="form-card__input"
                value="<?= htmlspecialchars($currentUser->getLastName()) ?>" placeholder="Twoje nazwisko" required>
            </div>
            <div class="form-card__group">
              <label for="password_for_name" class="form-card__label">Potwierdź zmianę hasłem</label>
              <input type="password" id="password_for_name" name="password" class="form-card__input"
                placeholder="Hasło" required>
              <p><a href="<?= url('reset-hasla') ?>" class="link">Nie pamiętasz hasła?</a></p>
            </div>
            <button type="submit" class="btn btn--primary">Zapisz dane</button>
          </form>
        </div>
      </div>

      <div class="settings-card <?= ($activeForm === 'email') ? 'is-open' : '' ?>">
        <div class="settings-card__header">
          <div class="settings-card__title-group">
            <h3 class="settings-card__title">Adres e-mail</h3>
            <p class="settings-card__description">Aktualny: <?= htmlspecialchars($currentUser->getEmail()) ?></p>
          </div>
          <i class="fas fa-chevron-down settings-card__icon"></i>
        </div>
        <div class="settings-card__content">
          <p class="info-card__message" style="text-align: left; font-size: 0.9rem; margin-bottom: 1.5rem;">Po zmianie adresu e-mail zostaniesz wylogowany/a, a na nowy adres zostanie wysłany link weryfikacyjny.</p>
          <form method="POST">
            <input type="hidden" name="form_type" value="change_email">
            <div class="form-card__group">
              <label for="new_email" class="form-card__label">Nowy adres e-mail</label>
              <input type="email" id="new_email" name="new_email" class="form-card__input"
                placeholder="Nowy adres e-mail" required>
            </div>
            <div class="form-card__group">
              <label for="password_for_email" class="form-card__label">Potwierdź zmianę hasłem</label>
              <input type="password" id="password_for_email" name="password" class="form-card__input"
                placeholder="Hasło" required>
              <p><a href="<?= url('reset-hasla') ?>" class="link">Nie pamiętasz hasła?</a></p>
            </div>
            <button
              type="submit"
              id="changeEmailBtn"
              class="btn btn--primary"
              data-text="Zmień adres e-mail"
              data-remaining="<?= $emailChangeRemaining ?? 0 ?>"
              <?= ($emailChangeRemaining ?? 0) > 0 ? 'disabled' : '' ?>>
              <?php if (($emailChangeRemaining ?? 0) > 0): ?>
                Wyślij ponownie za <span id="countdown"><?= $emailChangeRemaining ?></span>s
              <?php else: ?>
                Zmień adres e-mail
              <?php endif; ?>
            </button>
          </form>
        </div>
      </div>

      <div class="settings-card <?= ($activeForm === 'password') ? 'is-open' : '' ?>">
        <div class="settings-card__header">
          <div class="settings-card__title-group">
            <h3 class="settings-card__title">Bezpieczeństwo</h3>
            <p class="settings-card__description">Zmień swoje hasło.</p>
          </div>
          <i class="fas fa-chevron-down settings-card__icon"></i>
        </div>
        <div class="settings-card__content">
          <form method="POST">
            <input type="hidden" name="form_type" value="change_password">
            <div class="form-card__group">
              <label for="current_password" class="form-card__label">Aktualne hasło</label>
              <input type="password" id="current_password" name="current_password" class="form-card__input"
                placeholder="Hasło" required>
            </div>
            <div class="form-card__group">
              <label for="new_password" class="form-card__label">Nowe hasło</label>
              <input type="password" id="new_password" name="new_password" class="form-card__input"
                placeholder="Nowe hasło" required>
            </div>
            <div class="form-card__group">
              <label for="confirm_new_password" class="form-card__label">Powtórz nowe hasło</label>
              <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-card__input"
                placeholder="Powtórz nowe hasło" required>
              <p><a href="<?= url('reset-hasla') ?>" class="link">Nie pamiętasz hasła?</a></p>
            </div>
            <button type="submit" class="btn btn--primary">Zmień hasło</button>
          </form>
        </div>
      </div>

      <div class="settings-card <?= ($activeForm === 'delete') ? 'is-open' : '' ?>">
        <div class="settings-card__header">
          <div class="settings-card__title-group">
            <h3 class="settings-card__title">Usuwanie konta</h3>
            <p class="settings-card__description">Trwałe usunięcie konta.</p>
          </div>
          <i class="fas fa-chevron-down settings-card__icon"></i>
        </div>
        <div class="settings-card__content">
          <p class="info-card__message" style="text-align: left; font-size: 0.9rem; margin-bottom: 1.5rem; color: #b91c1c;">
            <strong>Uwaga:</strong> Usunięcie konta jest operacją nieodwracalną. Wszystkie Twoje dane, w tym postępy w nauce, zostaną bezpowrotnie skasowane.
          </p>
          <form method="POST" action="<?= url('usun-konto') ?>">
            <input type="hidden" name="form_type" value="delete_account">
            <div class="form-card__group">
              <label for="password_for_delete" class="form-card__label">Potwierdź hasłem, aby usunąć konto</label>
              <input type="password" id="password_for_delete" name="password" class="form-card__input"
                placeholder="Hasło" required>
              <p><a href="<?= url('reset-hasla') ?>" class="link">Nie pamiętasz hasła?</a></p>
            </div>
            <button type="submit" class="btn btn--primary btn--full-width" style="background-color: #dc2626; border-color: #dc2626;">Usuń konto na zawsze</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <?php include 'partials/footer.php'; ?>
  <script type="module" src="<?= url('js/components/SettingsAccordion.js') ?>"></script>
  <script type="module">
    import CountdownButton from '<?= url('js/components/CountdownButton.js') ?>';
    new CountdownButton('changeEmailBtn');
  </script>
</body>
</html>