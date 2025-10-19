<?php
/**
 * Front controller — punkt wejściowy aplikacji.
 */

// Wczytanie pliku konfiguracyjnego.
require_once __DIR__ . '/../config.php';

// Wczytanie autoloadera z composera.
require_once __DIR__ . '/../vendor/autoload.php';

// Wczytanie globalnych funkcji pomocniczych.
require_once __DIR__ . '/../app/Core/helpers.php';

use App\Core\Router;

// Odpalenie sesji.
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Utworzenie obiektu Router'a.
(new Router())->handleRequest();
