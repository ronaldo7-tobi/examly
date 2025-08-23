<?php
/**
 * Front controller — punkt wejściowy aplikacji
 */

// Wczytanie autoloadera klas
require_once __DIR__ . '/../app/Core/autoload.php';

// Odpalenie sesji
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Utworzenie obiektu Router'a.
(new Router())->handleRequest();
