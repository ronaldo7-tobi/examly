<!DOCTYPE html>
<html lang="pl">
<head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Opis strony (ważne dla Google i podglądu w wyszukiwarkach) -->
    <meta name="description" content="Examly to najlepsza platforma edukacyjna oferująca testy i materiały 
    do egzaminów zawodowych. Przygotuj się skutecznie do egzaminu E.14 / INF.03.">

    <!-- Słowa kluczowe (mają małe znaczenie SEO, ale można dodać) -->
    <meta name="keywords" content="egzaminy zawodowe, INF.03, E.14, testy online, 
    przygotowanie do egzaminu, examly, egzamin, egzamin zawodowy, programowanie">

    <!-- Autor -->
    <meta name="author" content="Examly.pl">

    <!-- Roboty: indeksowanie i podążanie za linkami -->
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:title" content="Examly - Przygotuj się do egzaminów zawodowych INF.03 / E.14">
    <meta property="og:description" content="Zdobądź wiedzę i pewność przed egzaminem zawodowym. Sprawdź nasze testy, 
    materiały i wsparcie oraz zdaj egzamin bez stresu z świetnym wynikiem.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.examly.pl/">
    <meta property="og:image" content="https://www.examly.pl/images/social-preview.jpg"> <!-- Wstaw poprawny URL do obrazka -->

    <!-- X Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Examly - Egzaminy zawodowe INF.03 / E.14">
    <meta name="twitter:description" content="Sprawdź darmowe testy i materiały edukacyjne przygotowujące do 
    egzaminu zawodowego.">
    <meta name="twitter:image" content="https://www.examly.pl/images/social-preview.jpg"> <!-- Wstaw poprawny URL do obrazka -->

    <!-- Ikona dla zakładki i urządzeń -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon"> <!-- Wstaw poprawny URL do obrazka -->

    <!-- Canonical link (dla uniknięcia duplikacji treści) -->
    <link rel="canonical" href="https://www.examly.pl/">

    <!-- Tytuł strony -->
    <title>Examly - INF.03 test</title>

    <!-- Link do styli -->
    <link rel="stylesheet" href="../public/scss/main.css"> 

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <div class="container">
        <main id="test-container" class="test-container" data-exam-code="<?= htmlspecialchars($examCode) ?>">
            
            <div id="loading-screen" class="test-loading">
                <h2>Trwa przygotowywanie testu...</h2>
                <p>Proszę czekać, losujemy 40 pytań z całej puli.</p>
                <div class="spinner"></div>
            </div>

            <div id="test-view" class="hidden">
                <header class="test-header">
                    <h1 class="test-header__title">Test Całościowy - INF.03</h1>
                    <div class="test-header__meta">
                        <div id="timer" class="timer">60:00</div>
                        <div id="question-counter" class="question-counter">Pytanie 1 / 40</div>
                    </div>
                </header>

                <div id="questions-wrapper" class="questions-wrapper"></div>

                <footer class="test-footer">
                    <button id="finish-test-btn" class="btn btn--danger">Zakończ i sprawdź test</button>
                </footer>
            </div>

            <div id="results-screen" class="results-container hidden">
                <h1 class="results-container__title">Wyniki Testu</h1>
                <div id="score-summary" class="score-summary">
                    <p>Twój wynik: <strong id="score-percent">0%</strong></p>
                    <p>Poprawne odpowiedzi: <strong id="correct-count">0</strong></p>
                    <p>Czas ukończenia: <strong id="duration">00:00</strong></p>
                </div>
                <div id="results-details" class="results-details"></div>
                <div class="results-container__actions">
                    <a href="/examly/public/" class="btn btn--primary">Wróć na stronę główną</a>
                </div>
            </div>

        </main>
    </div>

    <script type="module" src="/examly/public/js/features/test/index.js"></script>
    <?php include 'partials//footer.php'; ?>
</body>
</html>