<?php
/**
 * Front controller — punkt wejściowy aplikacji.
 */

// Wczytanie pliku konfiguracyjnego.
require_once __DIR__ . '/../config.php';

// Wczytanie autoloadera klas.
require_once __DIR__ . '/../app/Core/autoload.php';

// Wczytanie globalnych funkcji pomocniczych.
require_once __DIR__ . '/../app/Core/helpers.php';

// Odpalenie sesji.
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Utworzenie obiektu Router'a.
(new Router())->handleRequest();
