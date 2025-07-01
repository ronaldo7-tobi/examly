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
    <link rel="stylesheet" href="../public/css/form_pages.css">
    <link rel="stylesheet" href="../public/css/button.css">
    <link rel="stylesheet" href="../public/css/style.css">

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <header>
        <h1>Rejestracja</h1>
    </header>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <fieldset>
            <legend>Dane osobowe</legend>
            <label for="first_name">Imię:</label><br>
            <input type="text" id="first_name" name="first_name" 
                value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>"><br>
            <label for="last_name">Nazwisko:</label><br>
            <input type="text" id="last_name" name="last_name" 
                value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>"><br>
            <label for="email">E-mail:</label><br>
            <input type="email" id="email" name="email" 
                value="<?= htmlspecialchars($formData['email'] ?? '') ?>"><br>
        </fieldset>

        <fieldset>
            <legend>Ustaw hasło</legend>
            <label for="password">Hasło:</label><br>
            <input type="password" id="password" name="password"><br>
            <label for="confirm_password">Potwierdź hasło:</label><br>
            <input type="password" id="confirm_password" name="confirm_password"><br><br>
        </fieldset>

        <button type="submit">Zarejestruj się</button>
        <p>Masz już konto? <a href="login">Zaloguj się</a></p>
    </form>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
