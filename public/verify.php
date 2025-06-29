<?php
$token = $_GET['token'] ?? null;

if (!$token) {
    die('Brak tokenu weryfikacyjnego.');
}

$tokenService = new TokenService();
$userModel = new UserModel();

// Pobierz rekord tokenu
$tokenRecord = $tokenService->getTokenRecord($token);

if (!$tokenRecord) {
    die('Nieprawidłowy lub wygasły token.');
}

// Sprawdź, czy token wygasł
if (strtotime($tokenRecord['expires_at']) < time()) {
    die('Token wygasł.');
}

// Oznacz użytkownika jako zweryfikowanego
$userModel->verifyUser($tokenRecord['user_id']);

// Usuń token (opcjonalnie, ale zalecane)
$tokenService->deleteToken($token);

// Informacja zwrotna
echo 'Adres e-mail został pomyślnie zweryfikowany. Możesz się teraz zalogować.';
?>