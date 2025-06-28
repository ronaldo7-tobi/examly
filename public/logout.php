<?php
session_start();

// Usunięcie danych użytkownika
unset($_SESSION['user']);

// Opcjonalnie całkowicie zniszcz sesję
session_destroy();

// Przekierowanie
header('Location: /examly/public/login');
exit;
?>