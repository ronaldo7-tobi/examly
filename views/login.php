<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Ikona dla zakładki i urządzeń -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon"> <!-- Wstaw poprawny URL do obrazka -->

    <!-- Tytuł strony -->
    <title>Formularz logowania</title>

    <!-- Linki do styli -->
    <link rel="stylesheet" href="../public/scss/main.css"> 

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <main>
        <?php 
            // Uproszczona i bezpieczna obsługa wiadomości flash
            if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert--error" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']); ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php elseif (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert--success" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_success']); ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; 
        ?>

        <form method="POST" action="login" class="form-card">
            <div class="form-card__header">
                <h1 class="form-card__title">Witaj ponownie!</h1>
                <p class="form-card__subtitle">Zaloguj się, aby kontynuować naukę.</p>
            </div>
          
            <!-- Wyświetlanie błędów walidacji formularza -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert--error" role="alert">
                    <ul class="alert__list">
                        <?php foreach ($errors as $error): ?>
                            <li class="alert__item"><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-card__group">
                <label for="email" class="form-card__label">Adres e-mail</label>
                <input type="email" id="email" name="email" class="form-card__input" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
            </div>
          
            <div class="form-card__group">
                <label for="password" class="form-card__label">Hasło</label>
                <input type="password" id="password" name="password" class="form-card__input" required>
            </div>

            <!-- Używamy reużywalnego komponentu przycisku -->
            <button type="submit" class="btn btn--primary btn--full-width">Zaloguj się</button>

            <div class="form-card__footer">
                <p>Nie masz konta? <a href="register">Stwórz je teraz</a></p>
                <p><a href="reset-password">Nie pamiętasz hasła?</a></p>
            </div>
        </form>
    </main>
    
    <?php include 'partials/footer.php'; ?>
</body>
</html>