<?php

namespace App\Models;

use App\Core\Database;

/**
 * Model Odpowiedzi (Answer).
 *
 * Klasa ta pełni rolę warstwy dostępu do danych dla tabeli `answers`.
 * Jej odpowiedzialnością jest hermetyzacja (zamknięcie) zapytań SQL
 * dotyczących odpowiedzi. Dzięki temu reszta aplikacji (np. kontrolery)
 * nie musi znać struktury bazy danych, a jedynie wywoływać metody tego modelu.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class Answer
{
  /**
   * Instancja centralnego obiektu do obsługi bazy danych (Singleton).
   * @var Database
   */
  private Database $db;

  /**
   * Konstruktor modelu Answer.
   *
   * W momencie tworzenia obiektu modelu, pobiera on gotową do użycia
   * instancję połączenia z bazą danych.
   */
  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Pobiera wszystkie odpowiedzi przypisane do konkretnego pytania.
   *
   * Kluczowym elementem tej metody jest sortowanie wyników w losowej
   * kolejności (`ORDER BY RAND()`). Zapobiega to sytuacji, w której
   * użytkownik uczy się poprawnej odpowiedzi na podstawie jej stałej
   * pozycji, zmuszając go do faktycznego czytania treści.
   *
   * @param int $questionId ID pytania, dla którego odpowiedzi mają zostać pobrane.
   *
   * @return array Tablica tablic asocjacyjnych, gdzie każda
   * reprezentuje jedną odpowiedź. Zwraca pustą tablicę,
   * jeśli pytanie nie ma odpowiedzi lub wystąpił błąd.
   */
  public function getAnswersToQuestion(int $questionId): array
  {
    // Zapytanie SQL wybierające kluczowe dane odpowiedzi dla danego pytania.
    $sql = 'SELECT id, content, is_correct FROM answers WHERE question_id = ? ORDER BY RAND()';

    return $this->db->fetchAll($sql, [$questionId]);
  }

  /**
   * Pobiera JEDYNĄ poprawną odpowiedź dla danego pytania.
   *
   * Ta metoda jest wykorzystywana przez logikę aplikacji (np. w ApiController)
   * do weryfikacji, czy odpowiedź udzielona przez użytkownika jest poprawna.
   * Nie służy do wyświetlania odpowiedzi użytkownikowi.
   *
   * @param int $questionId ID pytania, dla którego szukana jest poprawna odpowiedź.
   *
   * @return array|false Zwraca tablicę asocjacyjną z ID poprawnej odpowiedzi
   * (np. `['id' => 123]`) lub `false`, jeśli pytanie
   * nie ma poprawnej odpowiedzi lub wystąpił błąd.
   */
  public function getCorrectAnswerForQuestion(int $questionId): array|false
  {
    // Zapytanie SQL wybierające tylko ID odpowiedzi oznaczonej jako poprawna.
    $sql = 'SELECT id FROM answers WHERE question_id = ? AND is_correct = 1';

    return $this->db->fetch($sql, [$questionId]);
  }
}
