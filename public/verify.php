<?php
$token = $_GET['token'] ?? null;

$message = '';
$status = '';

if (!$token) {
    $message = 'Brak tokenu weryfikacyjnego.';
    $status = 'error';
} else {
    $tokenService = new TokenService();
    $userModel = new UserModel();

    $tokenRecord = $tokenService->getTokenRecord($token);

    if (!$tokenRecord) {
        $message = 'Nieprawidłowy lub wygasły token.';
        $status = 'error';
    } elseif (strtotime($tokenRecord['expires_at']) < time()) {
        $message = 'Token wygasł.';
        $status = 'error';
    } else {
        $userModel->verifyUser($tokenRecord['user_id']);
        $tokenService->deleteToken($token);
        $message = 'Adres e-mail został pomyślnie zweryfikowany!';
        $status = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Weryfikacja e-mail</title>
    <link rel="stylesheet" href="../public/css/layout.css">
    <link rel="stylesheet" href="../public/css/button.css">
    <link rel="stylesheet" href="../public/css/verify.css">
</head>
<body>
    <div class="container">
        <div class="icon <?= $status ?>">
            <?= $status === 'success' ? '✅' : '❌' ?>
        </div>
        <div class="message"><?= htmlspecialchars($message) ?></div>

        <?php if ($status === 'success'): ?>
            <a href="login.php" class="button">Zaloguj się</a>
        <?php else: ?>
            <a href="/" class="button">Powrót na stronę</a>
        <?php endif; ?>
    </div>
</body>
</html>
