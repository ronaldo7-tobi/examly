<?php
$pageTitle = 'Ustaw Nowe Hasło | Examly';
$noIndex = true;
?>
<!DOCTYPE html>
<html lang="pl">
<?php include 'partials/head.php'; ?>

<body class="auth-page-background">
  <?php include 'partials/navbar.php'; ?>
  <main>
    <form method="POST" class="form-card">
      <div class="form-card__header">
        <h1 class="form-card__title">Ustaw nowe hasło</h1>
        <p class="form-card__subtitle">Wprowadź swoje nowe, bezpieczne hasło.</p>
      </div>

      <?php if (isset($_SESSION['flash_message'])):
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
      ?>
        <div class="alert alert--<?= htmlspecialchars($flash['type']) ?>">
          <?php if (isset($flash['errors'])): ?>
            <ul class="alert__list">
              <?php foreach ($flash['errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <?= htmlspecialchars($flash['text']) ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

      <div class="form-card__group">
        <label for="otp_code" class="form-card__label">Kod bezpieczeństwa</label>
        <input type="text" id="otp_code" name="otp_code" class="form-card__input" placeholder="Wpisz 6-cyfrowy kod" required>
      </div>

      <div class="form-card__group">
        <label for="new_password" class="form-card__label">Nowe hasło</label>
        <div class="password-wrapper">
          <input type="password" id="new_password" name="new_password" class="form-card__input"
            placeholder="Hasło" required>
          <i class="fas fa-eye password-toggle-icon"></i>
        </div>
        <p class="form-card__input-hint">Minimum 8 znaków, w tym wielka i mała litera, cyfra oraz znak specjalny.</p>
      </div>
      <div class="form-card__group">
        <label for="confirm_new_password" class="form-card__label">Powtórz nowe hasło</label>
        <div class="password-wrapper">
          <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-card__input"
            placeholder="Powtórz nowe hasło" required>
          <i class="fas fa-eye password-toggle-icon"></i>
        </div>
      </div>

      <div class="form-card__action-container">
        <button type="submit" class="btn btn--primary">Zapisz nowe hasło</button>
      </div>
    </form>
  </main>
  <?php include 'partials/footer.php'; ?>

  <script type="module" src="<?= url('js/components/show-password-toggle.js') ?>"></script>
</body>

</html>