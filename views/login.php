<?php 
$auth = new AuthController();
$result = null;

session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->login($_POST)) {
        session_regenerate_id(true);
        $_SESSION['user'] = serialize($auth->getLoggedUser());
        header("Location: /examly/public/");
    exit;
} else {
    echo "Błąd logowania";
}
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularz logowania</title>
</head>
<body>
    <form method="POST" action="">
        <fieldset>
        <legend>Logowanie</legend>
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email"> <br> <br>
        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password">
        </fieldset>

        <button type="submit">Zaloguj się</button>
    </form>
</body>
</html>