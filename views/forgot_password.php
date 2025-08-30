<?php
$pageTitle = 'Resetowanie Hasła | Examly';
$noIndex = true;
?>
<!DOCTYPE html>
<html lang="pl">
<?php include 'partials/head.php'; ?>

<body class="auth-page-background">
  <?php include 'partials/navbar.php'; ?>
  <main>
    <form method="POST" action="<?= url('reset-hasla') ?>" class="form-card">
      <div class="form-card__header">
        <h1 class="form-card__title">Zapomniałeś hasła?</h1>
        <p class="form-card__subtitle">Podaj swój adres e-mail, a wyślemy Ci link do zresetowania hasła.</p>
      </div>

      <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert--<?= htmlspecialchars($_SESSION['flash_message']['type']) ?>">
          <?= htmlspecialchars($_SESSION['flash_message']['text']) ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['password_reset_code'])): ?>
        <div class="alert alert--success">
          <strong>Twój kod bezpieczeństwa to: 
            <?= htmlspecialchars(substr($_SESSION['password_reset_code'], 0, 3) . '-' . substr($_SESSION['password_reset_code'], 3, 3)); ?>
          </strong>
          <p style="font-size: 0.9em; margin-top: 0.5rem;">Użyj go na stronie resetowania hasła, na którą link wysłaliśmy Ci w e-mailu.</p>
        </div>
        <?php unset($_SESSION['password_reset_code']); ?>
      <?php endif; ?>

      <div class="form-card__group">
        <label for="email" class="form-card__label">Adres e-mail</label>
        <input type="email" id="email" name="email" class="form-card__input" placeholder="Twój adres e-mail" required>
      </div>

      <div class="form-card__action-container">
        <button
          type="submit"
          id="submitBtn"
          class="btn btn--primary"
          data-text="Wyślij link"
          data-remaining="<?= $remaining ?? 0 ?>"
          <?= ($remaining ?? 0) > 0 ? 'disabled' : '' ?>>
          <?php if (($remaining ?? 0) > 0): ?>
            Wyślij ponownie za <span id="countdown"><?= $remaining ?></span>s
          <?php else: ?>
            Wyślij link
          <?php endif; ?>
        </button>
      </div>
    </form>
  </main>
  <?php include 'partials/footer.php'; ?>

  <script type="module">
    import CountdownButton from '<?= url('js/components/CountdownButton.js') ?>';
    new CountdownButton('submitBtn');
  </script>
</body>

</html>