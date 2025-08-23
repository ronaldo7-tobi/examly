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
   * @return void
   */
  public function showOneQuestionPage()
  {
    // Przekazujemy kod egzaminu do widoku pojedynczego pytania
    $this->renderView('inf03_one_question', ['examCode' => 'INF.03']);
  }

  /**
   * Wyświetla stronę interfejsu do generowania spersonalizowanego testu.
   *
   * @return void
   */
  public function showPersonalizedTestPage(): void
  {
    $this->renderView('inf03_personalized_test', ['examCode' => 'INF.03']);
  }

  /**
   * Wyświetla stronę pełnego testu egzaminacyjnego.
   *
   * @return void
   */
  public function showTestPage()
  {
    // Przekazujemy kod egzaminu do widoku pełnego testu
    $this->renderView('inf03_test', ['examCode' => 'INF.03']);
  }

  /**
   * Wyświetla główną stronę kursu.
   *
   * @return void
   */
  public function showCoursePage(): void
  {
    $this->renderView('inf03_course');
  }
}
