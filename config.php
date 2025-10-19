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

// Dane uwierzytelniające Google OAuth 2.0
// WAŻNE: W środowisku produkcyjnym przenieś je do zmiennych środowiskowych!
define('GOOGLE_CLIENT_ID', '1027665726499-fgk4im09bbitc67s2b5fth7mdjcui6b4.apps.googleusercontent.com'); // <-- Wklej tutaj swój Client ID
define('GOOGLE_CLIENT_SECRET', '***REMOVED***'); // <-- Wklej tutaj swój Client Secret
define('GOOGLE_REDIRECT_URI', url('auth/google/callback')); // Callback URL