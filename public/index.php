<?php
/**
 * Front controller — punkt wejściowy aplikacji
 */

// Wczytanie autoloadera klas
require_once __DIR__ . '/../app/Core/autoload.php';

// Utworzenie obiektu Router'a.
$router = new Router();
$router->handleRequest();
?>