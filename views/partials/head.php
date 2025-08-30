<?php

/**
 * ========================================================================
 * Komponent: Nagłówek HTML (<head>)
 * ========================================================================
 *
 * @description Ten komponent generuje całą sekcję <head> dla każdej
 * podstrony. Jest zaprojektowany tak, aby być elastycznym –
 * przyjmuje zmienne z widoku, który go dołącza, ale posiada
 * również solidne wartości domyślne dla kluczowych tagów SEO.
 *
 * @variables
 * - $pageTitle (string): Tytuł strony.
 * - $pageDescription (string): Opis dla meta tagu description.
 * - $canonicalUrl (string): Kanoniczny URL strony.
 * - (Opcjonalnie) Można dodać więcej zmiennych dla tagów OG, etc.
 */
?>

<head>
  <!-- ================================================================== -->
  <!-- 1. Podstawowa konfiguracja i metadane -->
  <!-- ================================================================== -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <!-- 
    @property theme-color
    @description Ustawia kolor paska narzędzi przeglądarki na urządzeniach mobilnych,
                 co poprawia spójność wizualną marki i postrzeganie strony.
  -->
  <meta name="theme-color" content="#7928CA">

  <!-- ================================================================== -->
  <!-- 2. Kluczowe Tagi SEO (Tytuł, Opis, Kanoniczny URL) -->
  <!-- ================================================================== -->
  <title><?= htmlspecialchars($pageTitle ?? 'Examly - Egzaminy Zawodowe INF.03') ?></title>

  <!-- 
    @property description
    @description Kluczowy dla SEO. To jest tekst, który Google najczęściej
                 pokazuje pod tytułem strony w wynikach wyszukiwania.
                 Powinien być zwięzły (ok. 155 znaków) i zachęcający.
  -->
  <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'Examly to platforma edukacyjna z testami i materiałami do egzaminów zawodowych. Przygotuj się skutecznie do egzaminu INF.03.') ?>">

  <!-- 
    @property canonical
    @description Absolutnie kluczowy dla unikania problemu duplikacji treści.
                 Wskazuje wyszukiwarkom, który URL jest "oryginalną" wersją
                 danej strony, nawet jeśli jest dostępna pod wieloma adresami.
  -->
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl ?? 'https://www.examly.pl/') ?>">

  <!-- 
    @property author, robots
    @description Informacje dla robotów wyszukiwarek. 'index, follow' to
                 standardowe ustawienie, które pozwala na indeksowanie
                 strony i podążanie za linkami.
  -->
  <meta name="author" content="Examly.pl">
  <?php
  // Ustaw domyślne wartości
  $index_directive = 'index';
  $follow_directive = 'follow';

  // Sprawdź, czy strona ma być wyłączona z indeksowania
  if (isset($noIndex) && $noIndex === true) {
      $index_directive = 'noindex';
  }

  // Sprawdź, czy roboty nie mają podążać za linkami
  if (isset($noFollow) && $noFollow === true) {
      $follow_directive = 'nofollow';
  }
  ?>
  <meta name="robots" content="<?= $index_directive ?>, <?= $follow_directive ?>">

  <!-- ================================================================== -->
  <!-- 3. Podglądy w mediach społecznościowych (Open Graph & Twitter) -->
  <!-- ================================================================== -->
  <!-- Protokół Open Graph (Facebook, LinkedIn, Pinterest, etc.) -->
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Examly - Egzaminy Zawodowe INF.03') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription ?? 'Zdobądź wiedzę i pewność przed egzaminem zawodowym.') ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://www.examly.pl/">
  <meta property="og:image" content="https://www.examly.pl/images/social-preview.jpg">
  <meta property="og:site_name" content="Examly">

  <!-- Twitter Cards -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle ?? 'Examly - Egzaminy Zawodowe INF.03') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription ?? 'Sprawdź darmowe testy i materiały edukacyjne.') ?>">
  <meta name="twitter:image" content="https://www.examly.pl/images/social-preview.jpg">

  <!-- ================================================================== -->
  <!-- 4. Ikony i zasoby (Assets) -->
  <!-- ================================================================== -->
  <link rel="icon" href="<?= url('favicon.ico') ?>" type="image/x-icon">

  <!-- 
    @property apple-touch-icon
    @description Ikona wyświetlana na ekranie głównym urządzeń Apple (iPhone, iPad),
                 gdy użytkownik doda skrót do Twojej strony. Ważne dla brandingu.
  -->
  <link rel="apple-touch-icon" href="<?= url('apple-touch-icon.png') ?>"> <!-- Upewnij się, że ten plik istnieje -->

  <link rel="stylesheet" href="<?= url('scss/main.css') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- ================================================================== -->
  <!-- 5. Uporządkowane dane (Structured Data - Schema.org) - ZAAWANSOWANE SEO -->
  <!-- ================================================================== -->
  <!-- 
    @description To jest "język", którym mówisz do Google, czym dokładnie
                 jest Twoja strona. Pomaga to w uzyskaniu tzw. "rich snippets"
                 (np. gwiazdki, FAQ) w wynikach wyszukiwania.
                 Poniżej przykład dla strony edukacyjnej.
  -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Examly",
      "url": "https://www.examly.pl/",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://www.examly.pl/search?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
  </script>

  <script>
    (function() {
      try {
        // Sprawdź zapisany motyw w localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
          document.documentElement.classList.add('dark');
        } else if (savedTheme === 'light') {
          document.documentElement.classList.remove('dark');
        } else {
          // Jeśli brak zapisanego motywu, sprawdź preferencje systemowe
          if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
          }
        }
      } catch (e) {
        // W razie problemów z localStorage, nic nie rób
      }
    })();
  </script>

  <!-- ================================================================== -->
  <!-- 6. Opcjonalne skrypty JavaScript dla konkretnych widoków -->
  <!-- ================================================================== -->
  <?php if (isset($extraScripts) && is_array($extraScripts)): ?>
    <?php foreach ($extraScripts as $scriptUrl): ?>
      <script src="<?= htmlspecialchars($scriptUrl) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</head>