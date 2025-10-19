<?php
/**
 * Plik konfiguracyjny aplikacji.
 * Definiuje globalne stałe używane w całej aplikacji.
 */

// Wczytanie globalnych funkcji pomocniczych.
require_once __DIR__ . '/app/Core/helpers.php';

// Zdefiniuj główny adres URL aplikacji.
// WAŻNE: Zmień 'http://localhost/examly/public' na adres Twojej strony na hostingu.
// Upewnij się, że na końcu NIE MA ukośnika (/).
define('BASE_URL', 'http://localhost/examly/public');

// Opcjonalnie: zdefiniuj ścieżkę na serwerze do głównego katalogu aplikacji.
// Przydatne do includowania plików w PHP.
define('BASE_PATH', __DIR__);