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
    <div class="quiz-page-layout">
        <aside class="quiz-page-layout__sidebar">
            <!-- Formularz wyboru tematów trafia tutaj -->
            <form id="topic-form" class="topic-selector">
                <!-- Używamy semantycznego nagłówka z klasą BEM -->
                <h2 class="topic-selector__legend">Wybierz, z jakiej części materiału chcesz otrzymać pytanie</h2>
                <!-- Używamy <div> z klasą BEM -->
                <div class="topic-selector__options">
                    <!-- Każdy tag to osobny element .topic-selector__label -->
                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="inf03">
                        <span>Cały materiał INF.03</span>
                    </label>
                    
                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="HTML">
                        <span>HTML</span>
                    </label>
                    
                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="CSS">
                        <span>CSS</span>
                    </label>
                    
                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="JS">
                        <span>JS</span>
                    </label>
                    
                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="PHP">
                        <span>PHP</span>
                    </label>
                    
                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="SQL">
                        <span>SQL</span>
                    </label>
                    
                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="Teoria">
                        <span>Inne pytania teoretyczne</span>
                    </label>

                    <p>Opcje premium: </p>

                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="toDiscover" class="premium">
                        <span>Nieodkryte pytania</span>
                    </label>

                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="toImprove" class="premium">
                        <span>Pytania, które gorzej Ci idą</span>
                    </label>

                    <label class="topic-selector__label">
                        <input type="checkbox" name="subject[]" value="toRemind" class="premium">
                        <span>Pytania najdawniej powtórzone</span>
                    </label>
                </div>
                <!-- Przycisk używa standardowych klas .btn z biblioteki komponentów -->
                <button type="submit" class="btn btn--primary">Rozpocznij naukę!</button>
            </form>
        </aside>
        <main class="quiz-page-layout__main-content">
            <!-- Kontener, w którym dynamicznie wyświetla się quiz -->
            <div id="quiz-container" style="margin-top: 20px;">
                <p class="quiz-placeholder">Wybierz przynajmniej jeden temat, aby rozpocząć naukę.</p>
            </div>
        </main>
    </div>

    <script>
        // 1. Poprawnie przekaż stan zalogowania z PHP do JS
        const isUserLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;

        // 2. Znajdź wszystkie checkboxy premium
        const premiumCheckboxes = document.querySelectorAll('.premium');

        // 3. Jeśli użytkownik nie jest zalogowany, wyłącz je
        if (!isUserLoggedIn) {
            premiumCheckboxes.forEach(checkbox => {
                checkbox.disabled = true;
                // Dodajmy też tooltip, aby wyjaśnić, dlaczego opcja jest nieaktywna
                const label = checkbox.closest('.topic-selector__label');
                if (label) {
                    label.title = 'Ta opcja jest dostępna tylko dla zalogowanych użytkowników.';
                    label.style.cursor = 'not-allowed'; // Zmień kursor, aby wizualnie wskazać nieaktywność
                }
            });
        }

        // 4. Logika do obsługi pojedynczego wyboru opcji premium
        premiumCheckboxes.forEach(checkboxToListen => {
            checkboxToListen.addEventListener('change', (e) => {
                // Jeśli ten checkbox został właśnie zaznaczony...
                if (e.target.checked) {
                    // ...przejdź przez wszystkie inne checkboxy premium...
                    premiumCheckboxes.forEach(otherCheckbox => {
                        // ...i jeśli to nie jest ten sam checkbox, odznacz go.
                        if (otherCheckbox !== e.target) {
                            otherCheckbox.checked = false;
                        }
                    });
                }
            });
        });
    </script>

    <script type="module" src="/examly/public/js/quiz.js"></script>

    <?php include 'partials//footer.php'; ?>
</body>
</html>