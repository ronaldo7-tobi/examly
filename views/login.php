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
    <link rel="stylesheet" href="../public/css/form_pages.css">
    <link rel="stylesheet" href="../public/css/button.css">
    <link rel="stylesheet" href="../public/css/style.css">

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <header>
        <h1>Formularz logowania</h1>
    </header>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <fieldset>
        <legend>Logowanie</legend>
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '')?>"> <br> <br>
        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password">
        </fieldset>

        <button type="submit">Zaloguj się</button>
        <p>Powrót do rejestracji: <a href="register">Powrót</a></p>
        <p>Nie pamiętasz hasła? <a href="#">Resetuj hasło</a></p>
    </form>
    <?php include 'partials/footer.php'; ?>
</body>
</html>