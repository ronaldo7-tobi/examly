<?php
$auth = new AuthController();
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->register($_POST);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Formularz logowania</title>
</head>
<body>
    <h1>Rejestracja</h1>

    <form method="post" action="">
        <fieldset>
        <legend>Dane osobowe</legend>
        <label for="first_name">Imię:</label><br>
        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($firstName ?? '') ?>"><br>

        <label for="last_name">Nazwisko:</label><br>
        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($lastName ?? '') ?>"><br>

        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>"><br>
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
    <?php 
    echo '<div class="message">';
    if($result) {
        if($result['success'] == false){
            foreach($result['errors'] as $error) {
                echo '<p>' . $error . '</p>';
            }
        }
        else {
            header('Location: login');
        }
    }
    echo '</div>';
    ?>
</body>
</html>
