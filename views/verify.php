<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <!-- Tytuł strony -->
    <title>Weryfikacja E-mail</title>

    <!-- Link do styli -->
    <link rel="stylesheet" href="../public/scss/main.css"> 
</head>
<body>
    <main>
        <div class="info-card--centered-fullscreen">
            <div class="info-card">

                <?php if ($status === 'success'): ?>

                    <!-- WARIANT: SUKCES -->
                    <div class="info-card__icon info-card__icon--success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1 class="info-card__title">Weryfikacja zakończona!</h1>
                    <p class="info-card__message"><?= htmlspecialchars($message) ?></p>
                    <div class="info-card__actions">
                        <a href="/login" class="btn btn--primary btn--full-width">Przejdź do logowania</a>
                    </div>
                
                <?php else: ?>

                    <!-- WARIANT: BŁĄD -->
                    <div class="info-card__icon info-card__icon--error">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h1 class="info-card__title">Wystąpił błąd</h1>
                    <p class="info-card__message"><?= htmlspecialchars($message) ?></p>
                    <div class="info-card__actions">
                        <a href="/" class="btn btn--secondary btn--full-width">Wróć na stronę główną</a>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </main>
</body>
</html>
