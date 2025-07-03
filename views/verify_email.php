<?php
session_start();  // uruchomienie sesji
// przygotowanie tablicy wiadomości
$messages = $messages ?? [];  

// obliczenie pozostałego czasu (w sekundach) do ponownego wysłania
$remaining = 0;
if (isset($_SESSION['email_sent'])) {
    $elapsed = time() - $_SESSION['email_sent'];
    $remaining = max(0, 60 - $elapsed);
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weryfikacja adresu e-mail</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="../public/css/form_pages.css">
    <link rel="stylesheet" href="../public/css/button.css">
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/verify_email.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <header>
        <h1>Weryfikacja adresu e-mail</h1>
    </header>

    <main class="verification-wrapper">
        <div class="verify_email-box">
            <?php foreach ($messages as $msg): ?>
                <div class="message">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endforeach; ?>

            <button
                id="resendButton"
                class="resend-btn"
                <?= $remaining > 0 ? 'disabled' : '' ?>
            >
                <?= $remaining > 0
                    ? "Wyślij ponownie e-mail za <span id=\"countdown\">{$remaining}</span>s"
                    : '<span id="countdown"></span>Wyślij ponownie e-mail' ?>
            </button>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>

    <script>
        // przekazujemy PHP‑owy remaining do JS
        let countdown = <?= $remaining ?>;
        const button = document.getElementById('resendButton');
        const countdownSpan = document.getElementById('countdown');

        if (countdown > 0) {
            countdownSpan.textContent = countdown;
            const timer = setInterval(() => {
                countdown--;
                countdownSpan.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(timer);
                    button.disabled = false;
                    button.innerHTML = 'Wyślij ponownie e-mail';
                }
            }, 1000);
        }

        button.addEventListener('click', () => {
            button.disabled = true;
            button.textContent = 'Wysyłanie...';
            // użycie właściwej ścieżki – zakładam, że URL to /verify_email
            window.location.href = '/verify_email?resend=true';
        });
    </script>
</body>
</html>
