<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Formularz rejestracji</title>
</head>
<body>
    <h1>Rejestracja</h1>

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
    </form>
    <p>Masz już konto? <a href="login">Zaloguj się</a></p>
</body>
</html>
