<?php

/**
 * Kontroler Stron Związanych z Quizami.
 *
 * Odpowiada za renderowanie widoków HTML dla różnych trybów nauki
 * i testów w ramach kwalifikacji INF.03. Działa jako pośrednik
 * między routingiem a warstwą prezentacji (szablonami).
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class QuizPageController extends BaseController
{
    /**
     * Wyświetla stronę quizu w trybie "jedno pytanie".
     *
     * Renderuje widok, który umożliwia użytkownikowi rozwiązywanie
     * pojedynczych, losowych pytań. Przekazuje do widoku kod egzaminu 'INF.03',
     * aby logika po stronie klienta (JavaScript) wiedziała,
     * z jakiego zakresu pytań korzystać, odpytując API.
     *
     * @return void Metoda nie zwraca wartości, jej zadaniem jest wyrenderowanie widoku.
     */
    public function showOneQuestionPage()
    {
        // Przekazujemy kod egzaminu do widoku pojedynczego pytania
        $this->renderView('inf03_one_question', ['examCode' => 'INF.03']);
    }

    /**
     * Wyświetla stronę interfejsu do generowania spersonalizowanego testu.
     *
     * Renderuje widok, w którym użytkownik może wybrać konkretne
     * kategorie lub skorzystać z opcji premium (np. "pytania do powtórki"),
     * aby wygenerować test dostosowany do swoich potrzeb.
     *
     * @return void Metoda nie zwraca wartości, jej zadaniem jest wyrenderowanie widoku.
     */
    public function showPersonalizedTestPage(): void
    {
        $this->renderView('inf03_personalized_test', ['examCode' => 'INF.03']);
    }

    /**
     * Wyświetla stronę pełnego testu egzaminacyjnego.
     *
     * Renderuje widok symulujący pełny, 40-pytaniowy arkusz egzaminacyjny.
     *
     * @return void Metoda nie zwraca wartości, jej zadaniem jest wyrenderowanie widoku.
     */
    public function showTestPage()
    {
        // Przekazujemy kod egzaminu do widoku pełnego testu
        $this->renderView('inf03_test', ['examCode' => 'INF.03']);
    }

    /**
     * Wyświetla główną stronę kursu.
     *
     * Renderuje widok, który może zawierać ogólne informacje o kursie,
     * materiały edukacyjne lub nawigację do poszczególnych modułów nauki.
     *
     * @return void Metoda nie zwraca wartości, jej zadaniem jest wyrenderowanie widoku.
     */
    public function showCoursePage(): void
    {
        $this->renderView('inf03_course');
    }
}