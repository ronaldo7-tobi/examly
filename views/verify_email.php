<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Ikona dla zakładki i urządzeń -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Tytuł strony -->
    <title>Weryfikacja adresu e-mail</title>

    <!-- Linki do styli -->
    <link rel="stylesheet" href="../public/css/form_pages.css">
    <link rel="stylesheet" href="../public/css/button.css">
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/verify_email.css">

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <header>
        <h1>Weryfikacja adresu e-mail</h1>
    </header>
    <main class="verification-wrapper">
        <div class="verify_email-box">
            <?php if (!empty($tab)) : ?>
                <div class="message">
                    <?= htmlspecialchars($tab[0]) ?>
                </div>
            <?php endif; ?>
            <button id="resendButton" class="resend-btn" disabled>Wyślij ponownie e-mail <span id="countdown">60</span>s</button>
        </div>
    </main>
    <?php include 'partials/footer.php'; ?>

    <script src="../public/js/verification_countdown.js"></script>
</body>
</html>