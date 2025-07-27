<?php
// Ta część PHP pozostaje bez zmian
$messages = $messages ?? [];  
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Ikona dla zakładki i urządzeń -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon"> <!-- Wstaw poprawny URL do obrazka -->

    <!-- Canonical link (dla uniknięcia duplikacji treści) -->
    <link rel="canonical" href="https://www.examly.pl/">

    <!-- Tytuł strony -->
    <title>Examly - weryfikacja E-mail</title>

    <!-- Link do styli -->
    <link rel="stylesheet" href="../public/scss/main.css"> 

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

<main>
        <div class="info-card--centered-fullscreen"> 
            <div class="info-card">
                <div class="info-card__icon info-card__icon--info">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                    
                <h1 class="info-card__title">Sprawdź swoją skrzynkę e-mail</h1>
                    
                <p class="info-card__message">
                    Aby zakończyć rejestrację, kliknij w link aktywacyjny, który wysłaliśmy na Twój adres e-mail.
                </p>

                <?php
                if (isset($flashMessage) && is_array($flashMessage)):
                ?>
                    <div class="alert alert--<?= htmlspecialchars($flashMessage['type']) ?>" role="alert">
                        <?= htmlspecialchars($flashMessage['text']) ?>
                    </div>
                <?php endif; ?>

                <div class="info-card__actions">
                    <form method="GET" action="/examly/public/verify_email">
                        <input type="hidden" name="send" value="true">
                        <button 
                            type="submit"
                            id="resendButton" 
                            class="btn btn--primary btn--full-width"
                            <?= ($remaining > 0) ? 'disabled' : '' ?>
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

    <script>
        // Ta część JavaScript pozostaje bez zmian
        let countdown = <?= $remaining ?>;
        const button = document.getElementById('resendButton');
        const countdownSpan = document.getElementById('countdown');

        if (countdown > 0) {
            // W przypadku błędu przy parsowaniu, upewnijmy się, że countdownSpan istnieje
            if (countdownSpan) {
                countdownSpan.textContent = countdown;
            }
            const timer = setInterval(() => {
                countdown--;
                if (countdownSpan) {
                    countdownSpan.textContent = countdown;
                }
                if (countdown <= 0) {
                    clearInterval(timer);
                    button.disabled = false;
                    // Aktualizujemy innerHTML, aby usunąć span z odliczaniem
                    button.innerHTML = 'Wyślij ponownie e-mail';
                }
            }, 1000);
        }

        button.addEventListener('click', (e) => {
            // Zapobiegamy wysłaniu formularza, jeśli chcemy to zrobić przez JS
            e.preventDefault(); 
            
            button.disabled = true;
            button.textContent = 'Wysyłanie...';
            window.location.href = '/verify_email?send=true';
        });
    </script>
</body>
</html>