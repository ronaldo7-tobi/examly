<?php

/**
 * Centralny Autoloader Aplikacji.
 *
 * Ten plik rejestruje funkcję autoloader, która jest fundamentem dynamicznego
 * ładowania klas w aplikacji. Zamiast manualnie dołączać każdy plik z klasą
 * za pomocą `require_once`, ten mechanizm automatycznie lokalizuje i dołącza
 * odpowiedni plik w momencie pierwszego użycia danej klasy.
 *
 * Logika działania:
 * 1. Definiuje listę kluczowych katalogów aplikacji (`Controllers`, `Core`, `Models`, `Services`).
 * 2. Gdy w kodzie pojawia się próba użycia niezaładowanej klasy (np. `new UserModel()`),
 * PHP wywołuje zarejestrowaną tutaj funkcję.
 * 3. Funkcja iteruje po zdefiniowanych ścieżkach, próbując odnaleźć plik o nazwie
 * pasującej do klasy (np. `UserModel.php`).
 * 4. Po znalezieniu pasującego pliku, dołącza go za pomocą `require_once` i kończy działanie.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
spl_autoload_register(function (string $class): void {
  // Krok 1: Zdefiniuj tablicę ścieżek, w których autoloader będzie szukał plików klas.
  $paths = [
    __DIR__ . '/../Controllers/', // Ścieżka do katalogu z kontrolerami.
    __DIR__ . '/', // Ścieżka do katalogu Core (pliki na tym samym poziomie co autoload.php).
    __DIR__ . '/../Models/', // Ścieżka do katalogu z modelami.
    __DIR__ . '/../Services/', // Ścieżka do katalogu z serwisami.
  ];

  // Krok 2: Przejdź przez każdą zdefiniowaną ścieżkę w pętli.
  foreach ($paths as $path) {
    // Krok 3: Skonstruuj pełną, potencjalną ścieżkę do pliku klasy.
    // Przykład: dla klasy 'UserModel' i ścieżki '/../Models/', wynikiem będzie '../Models/UserModel.php'.
    $file = $path . $class . '.php';

    // Krok 4: Sprawdź, czy plik fizycznie istnieje w danej lokalizacji.
    if (file_exists($file)) {
      // Jeśli tak, załaduj go. Używamy require_once, aby mieć pewność,
      // że plik zostanie dołączony tylko jeden raz, nawet przy wielokrotnych wywołaniach.
      require_once $file;

      // Zakończ działanie funkcji, ponieważ klasa została znaleziona i załadowana.
      // Dalsze przeszukiwanie jest niepotrzebne.
      return;
    }
  }

  // Jeśli pętla zakończy działanie bez znalezienia pliku, PHP zgłosi błąd "Class not found".
});
