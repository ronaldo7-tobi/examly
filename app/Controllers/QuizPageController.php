<?php 
require_once __DIR__ . '/BaseController.php';

/**
 * Kontroler Stron Związanych z Quizami.
 *
 * Grupuje akcje odpowiedzialne za wyświetlanie różnych widoków
 * związanych z materiałami egzaminacyjnymi INF.03.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class QuizPageController extends BaseController
{
    /**
     * Wyświetla stronę quizu w trybie "jedno pytanie".
     * @return void
     */
    public function showOneQuestionPage()
    {
        // Przekazujemy kod egzaminu do widoku pojedynczego pytania
        $this->renderView('inf03_one_question', ['examCode' => 'INF.03']);
    }

    /**
     * Wyświetla stronę spersonalizowanego testu.
     * @return void
     */
    public function showPersonalizedTestPage(): void
    {
        $this->renderView('inf03_personalized_test');
    }

    public function showTestPage()
    {
        // Przekazujemy kod egzaminu do widoku pełnego testu
        $this->renderView('inf03_test', ['examCode' => 'INF.03']);
    }

    /**
     * Wyświetla stronę kursu.
     * @return void
     */
    public function showCoursePage(): void
    {
        $this->renderView('inf03_course');
    }
}