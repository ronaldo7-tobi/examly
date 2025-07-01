<?php 
$emailSent = $_SESSION['flash_success'] ?? false;
$lastSentTime = $_SESSION['email_sent_time'] ?? 0;
$canResend = time() - $lastSentTime >= 60;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Ikona dla zakładki i urządzeń -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon"> <!-- Wstaw poprawny URL do obrazka -->

    <!-- Tytuł strony -->
    <title>Weryfikacja adresu e-mail</title>

    <!-- Linki do styli -->
    <link rel="stylesheet" href="../public/css/form_pages.css">
    <link rel="stylesheet" href="../public/css/button.css">
    <link rel="stylesheet" href="../public/css/style.css">

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <header>
        <h1>Weryfikacja adresu e-mail</h1>
    </header>
    <main>
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash flash-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash flash-error"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <p>Wysłaliśmy wiadomość weryfikacyjną na adres: 
            <strong><?= htmlspecialchars($_SESSION['verify_user_email']) ?></strong>
        </p>

        <?php if (!$canResend): ?>
            <p>Możesz ponownie wysłać e-mail za <span id="countdown"><?= 60 - (time() - $lastSentTime) ?></span> sekund.</p>
        <?php else: ?>
            <form action="" method="post">
                <button type="submit" name="resend_email" class="btn">Wyślij ponownie e-mail</button>
            </form>
        <?php endif; ?>
    </main>
    <?php include 'partials/footer.php'; ?>
</body>
</html>