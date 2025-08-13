<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Ikona dla zakładki i urządzeń -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon"> <!-- Wstaw poprawny URL do obrazka -->

    <!-- Tytuł strony -->
    <title>Formularz rejestracji</title>

    <!-- Linki do styli -->
    <link rel="stylesheet" href="../public/scss/main.css"> 

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-page-background">
    <?php include 'partials/navbar.php'; ?>

    <main>
        <form method="POST" class="form-card">
            <div class="form-card__header">
                <h1 class="form-card__title">Stwórz nowe konto</h1>
                <p class="form-card__subtitle">Dołącz do nas i zacznij przygotowania do egzaminu!</p>
            </div>

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
                <label for="first_name" class="form-card__label">Imię</label>
                <input type="text" id="first_name" name="first_name" class="form-card__input" 
                       value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" placeholder="Jan" required>
            </div>
            
            <div class="form-card__group">
                <label for="last_name" class="form-card__label">Nazwisko</label>
                <input type="text" id="last_name" name="last_name" class="form-card__input" 
                       value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" placeholder="Kowalski" required>
            </div>

            <div class="form-card__group">
                <label for="email" class="form-card__label">Adres e-mail</label>
                <input type="email" id="email" name="email" class="form-card__input" 
                       value="<?= htmlspecialchars($formData['email'] ?? '') ?>" placeholder="jankowalski@example.com" required>
            </div>
          
            <div class="form-card__group">
                <label for="password" class="form-card__label">Hasło</label>
                <input type="password" id="password" name="password" class="form-card__input" placeholder="Wpisz swoje hasło" required>
            </div>
          
            <div class="form-card__group">
                <label for="confirm_password" class="form-card__label">Potwierdź hasło</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-card__input" placeholder="Powtórz swoje hasło" required>
            </div>
            
            <div class="form-card__action-container">
                <button type="submit" class="btn btn--primary btn--full-width">Zarejestruj się</button>
            </div>

            <div class="form-card__footer">
                <p>Masz już konto? <a href="login">Zaloguj się</a></p>
            </div>
        </form>
    </main>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
