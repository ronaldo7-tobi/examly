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
    <title>Examly - INF.03 jedno pytanie</title>

    <!-- Link do styli -->
    <link rel="stylesheet" href="../public/scss/main.css"> 

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <!-- Formularz wyboru tematów -->
    <div class="quiz-page-layout" id="quiz-single-question" data-exam-code="<?= htmlspecialchars($examCode) ?>">
        <aside class="quiz-page-layout__sidebar">
            <form id="topic-form" class="topic-selector">
                <h2 class="topic-selector__legend">Wybierz, z jakiej części materiału chcesz otrzymać pytanie</h2>

                <fieldset class="topic-selector__fieldset">
                    <legend class="topic-selector__sub-legend">Główne kategorie</legend>
                    <div class="topic-selector__options">
                        <label class="topic-selector__label">
                            <input type="checkbox" value="inf03" id="select-all-inf03">
                            <span>Cały materiał INF.03</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="subject[]" value="1" class="topic-checkbox">
                            <span>HTML</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="subject[]" value="2" class="topic-checkbox">
                            <span>CSS</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="subject[]" value="3" class="topic-checkbox">
                            <span>JS</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="subject[]" value="4" class="topic-checkbox">
                            <span>PHP</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="subject[]" value="5" class="topic-checkbox">
                            <span>SQL</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="subject[]" value="6" class="topic-checkbox">
                            <span>Inne pytania teoretyczne</span>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="topic-selector__fieldset">
                    <legend class="topic-selector__sub-legend">Inteligentna nauka</legend>
                    <div class="topic-selector__options">
                        <label class="topic-selector__label">
                            <input type="checkbox" name="premium_option" value="toDiscover" class="premium-checkbox">
                            <span>Nieodkryte pytania</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="premium_option" value="toImprove" class="premium-checkbox">
                            <span>Pytania, które gorzej Ci idą</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="premium_option" value="toRemind" class="premium-checkbox">
                            <span>Pytania najdawniej powtórzone</span>
                        </label>
                        <label class="topic-selector__label">
                            <input type="checkbox" name="premium_option" value="lastMistakes" class="premium-checkbox">
                            <span>Ostatnio błędne</span>
                        </label>
                    </div>
                </fieldset>
                
                <button type="submit" class="btn btn--primary">Rozpocznij naukę!</button>
            </form>
        </aside>
        <main class="quiz-page-layout__main-content">
            <div id="quiz-container" style="margin-top: 20px;">
                <p class="quiz-placeholder">Wybierz przynajmniej jeden temat, aby rozpocząć naukę.</p>
            </div>
        </main>
    </div>
    <script type="module" src="/examly/public/js/components/topic-form-enhancer.js"></script>
    <script type="module" src="/examly/public/js/features/one-question/index.js"></script>
    <?php include 'partials//footer.php'; ?>
</body>
</html>