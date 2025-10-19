<?php

namespace App\Controllers;

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
    $this->renderView('inf03_one_question', ['examCode' => 'INF.03']);
  }

  /**
   * Wyświetla stronę interfejsu do generowania spersonalizowanego testu.
   *
   * @return void
   */
  public function showPersonalizedTestPage(): void
  {
    // Przygotuj domyślną tablicę danych dla widoku.
    $data = [
        'examCode' => 'INF.03',
        'selectedSubjectId' => null // Domyślnie brak wybranego tematu
    ];

    // Sprawdź, czy przekazano ID tematu w URL.
    if (isset($_GET['subject']) && is_array($_GET['subject']) && !empty($_GET['subject'][0])) {
        // Jeśli tak, nadpisz wartość w tablicy danych.
        $data['selectedSubjectId'] = (int) $_GET['subject'][0];
    }

    // Wyrenderuj widok tylko raz, przekazując kompletną tablicę danych.
    $this->renderView('inf03_personalized_test', $data);
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
   * Wyświetla stronę kursów.
   */
  public function showCoursesPage(): void
  {
    $this->renderView('courses');
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
