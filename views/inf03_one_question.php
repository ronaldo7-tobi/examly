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

    <!-- Link do ikonek -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Dodajemy proste style dla feedbacku -->
    <style>
        .answer-feedback {
            padding: 5px;
            border-radius: 5px;
            margin: 2px 0;
            display: inline-block;
        }
        .correct {
            background-color: #d4edda; /* Zielony */
            border: 1px solid #c3e6cb;
        }
        .incorrect {
            background-color: #f8d7da; /* Czerwony */
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    <!-- Formularz wyboru tematów pozostaje, ale dajemy mu ID -->
    <form id="topic-form">
        <fieldset>
            <legend>Wybierz z jakiej części materiału chcesz otrzymać pytanie</legend>
            <input type="checkbox" name="subject[]" value="inf03"> Cały materiał INF.03
            <input type="checkbox" name="subject[]" value="HTML"> HTML
            <input type="checkbox" name="subject[]" value="CSS"> CSS
            <input type="checkbox" name="subject[]" value="JS"> JS
            <input type="checkbox" name="subject[]" value="PHP"> PHP
            <input type="checkbox" name="subject[]" value="SQL"> SQL
            <input type="checkbox" name="subject[]" value="Teoria"> Inne pytania teoretyczne
        </fieldset>
        <button type="submit">Zatwierdź</button>
    </form>

    <!-- Kontener, w którym będziemy dynamicznie wyświetlać quiz -->
    <div id="quiz-container" style="margin-top: 20px;"></div>

    <!-- Tutaj cała magia AJAX -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const topicForm = document.getElementById('topic-form');
        const quizContainer = document.getElementById('quiz-container');
        let currentSubjects = []; // Zmienna do przechowywania wybranych tematów

        // 1. Obsługa formularza z tematami
        topicForm.addEventListener('submit', function(event) {
            event.preventDefault(); // ZATRZYMUJEMY domyślne przeładowanie strony!
            
            const formData = new FormData(topicForm);
            currentSubjects = formData.getAll('subject[]'); // Zapisujemy tematy do późniejszego użytku

            if (currentSubjects.length === 0) {
                alert('Proszę wybrać przynajmniej jeden temat.');
                return;
            }

            // Ukrywamy formularz z tematami po zatwierdzeniu
            topicForm.style.display = 'none';
            
            fetchQuestion();
        });

        // Funkcja do pobierania i wyświetlania pytania
        function fetchQuestion() {
            quizContainer.innerHTML = '<p>Ładowanie pytania...</p>';

            const formData = new FormData();
            formData.append('action', 'get_question');
            currentSubjects.forEach(subject => formData.append('subjects[]', subject));

            fetch('../app/Services/api.php', { // <-- WAŻNE: Podmień na poprawną ścieżkę!
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderQuestion(data.question, data.answers);
                } else {
                    quizContainer.innerHTML = `<p style="color: red;">Błąd: ${data.message}</p>`;
                    topicForm.style.display = 'block'; // Pokaż z powrotem formularz, jeśli wystąpił błąd
                }
            })
            .catch(error => {
                console.error('Błąd sieci:', error);
                quizContainer.innerHTML = '<p style="color: red;">Wystąpił błąd komunikacji z serwerem.</p>';
            });
        }

        // Funkcja do renderowania pytania na stronie
        function renderQuestion(question, answers) {
            let answersHTML = '';
            answers.forEach(answer => {
                // Używamy diva jako "opakowania" dla feedbacku
                answersHTML += `
                    <div>
                        <label class="answer-feedback">
                            <input type="radio" name="answer" value="${answer.id}" required>
                            ${escapeHTML(answer.content)}
                        </label>
                    </div>
                `;
            });

            quizContainer.innerHTML = `
                <section id="question-block">
                    <p><strong>${escapeHTML(question.content)}</strong></p>
                    <form id="answer-form">
                        ${answersHTML}
                        <input type="hidden" name="question_id" value="${question.id}">
                        <button type="submit">Sprawdź</button>
                    </form>
                </section>
            `;

            // Dodajemy nasłuchiwanie na nowo utworzony formularz odpowiedzi
            document.getElementById('answer-form').addEventListener('submit', handleAnswerCheck);
        }

        // 2. Obsługa formularza z odpowiedzią
        function handleAnswerCheck(event) {
            event.preventDefault();

            const answerForm = event.target;
            const formData = new FormData(answerForm);
            formData.append('action', 'check_answer');
            // Musimy ręcznie dodać ID wybranej odpowiedzi, bo FormData nie łapie value z radio buttonów
            const userAnswerId = answerForm.querySelector('input[name="answer"]:checked').value;
            formData.append('answer_id', userAnswerId);

            fetch('../app/Services/api.php', { // <-- WAŻNE: Podmień na poprawną ścieżkę!
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFeedback(data.is_correct, data.correct_answer_id);
                } else {
                    alert(`Błąd sprawdzania odpowiedzi: ${data.message}`);
                }
            });
        }

        // Funkcja do pokazywania feedbacku (kolorowanie i przycisk "Następne")
        function showFeedback(isCorrect, correctAnswerId) {
            const answerForm = document.getElementById('answer-form');
            const radioButtons = answerForm.querySelectorAll('input[name="answer"]');
            const userAnswerId = answerForm.querySelector('input[name="answer"]:checked').value;

            radioButtons.forEach(radio => {
                const label = radio.parentElement; // odwołujemy się do <label class="answer-feedback">

                // Gdy odpowiedź jest niepoprawna
                if (!isCorrect && radio.value == userAnswerId) {
                    label.classList.add('incorrect');
                }
                // Zaznaczamy poprawną odpowiedź na zielono (zawsze)
                if (radio.value == correctAnswerId) {
                    label.classList.add('correct');
                }
                
                // Wyłączamy wszystkie radio, aby uniemożliwić zmianę odpowiedzi
                radio.disabled = true;
            });

            // Zamieniamy przycisk "Sprawdź" na "Następne"
            const checkButton = answerForm.querySelector('button[type="submit"]');
            checkButton.remove();

            const nextButton = document.createElement('button');
            nextButton.type = 'button'; // Ważne, by nie wysyłał formularza!
            nextButton.textContent = 'Następne';
            nextButton.addEventListener('click', fetchQuestion); // Po kliknięciu pobieramy nowe pytanie

            answerForm.appendChild(nextButton);
        }

        // Pomocnicza funkcja do unikania XSS
        function escapeHTML(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    });
    </script>

    <?php include 'partials//footer.php'; ?>
</body>
</html>