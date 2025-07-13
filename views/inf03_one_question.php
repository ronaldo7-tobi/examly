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
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/questions.css">
    <link rel="stylesheet" href="../public/css/choice_form.css">

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <!-- Formularz wyboru tematów pozostaje, ale dajemy mu ID -->
    <form id="topic-form">
        <fieldset>
            <legend>Wybierz z jakiej części materiału chcesz otrzymać pytanie</legend>
            
            <!-- Nowa, ulepszona struktura z <label> opakowującym input i span -->
            <label class="topic-label">
                <input type="checkbox" name="subject[]" value="inf03">
                <span>Cały materiał INF.03</span>
            </label>
            <label class="topic-label">
                <input type="checkbox" name="subject[]" value="HTML">
                <span>HTML</span>
            </label>
            <label class="topic-label">
                <input type="checkbox" name="subject[]" value="CSS">
                <span>CSS</span>
            </label>
            <label class="topic-label">
                <input type="checkbox" name="subject[]" value="JS">
                <span>JS</span>
            </label>
            <label class="topic-label">
                <input type="checkbox" name="subject[]" value="PHP">
                <span>PHP</span>
            </label>
            <label class="topic-label">
                <input type="checkbox" name="subject[]" value="SQL">
                <span>SQL</span>
            </label>
            <label class="topic-label">
                <input type="checkbox" name="subject[]" value="Teoria">
                <span>Inne pytania teoretyczne</span>
            </label>
        </fieldset>
        <!-- Dodajemy te same klasy, co do przycisków w quizie dla spójności -->
        <button type="submit" class="quiz-button quiz-button--primary">Rozpocznij naukę!</button>
    </form>

    <!-- Kontener, w którym będziemy dynamicznie wyświetlać quiz -->
    <div id="quiz-container" style="margin-top: 20px;"></div>

    <script type="module" src="/examly/public/js/quiz.js"></script>

    <?php include 'partials//footer.php'; ?>
</body>
</html>