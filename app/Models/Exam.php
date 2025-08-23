<?php

/**
 * Model Egzaminu (Exam).
 *
 * Klasa ta odpowiada za operacje na danych związanych z historią egzaminów
 * zdawanych przez użytkowników. Zarządza zapisami w tabeli `user_exams`
 * oraz obsługuje relację wiele-do-wielu z tematami (`topics`) poprzez
 * tabelę pośredniczącą `user_exam_topics`.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class Exam
{
  /**
   * Instancja centralnego obiektu do obsługi bazy danych (Singleton).
   * @var Database
   */
  private Database $db;

  /**
   * Konstruktor modelu Exam.
   *
   * W momencie tworzenia obiektu modelu, pobiera on gotową do użycia
   * instancję połączenia z bazą danych.
   */
  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Zapisuje wyniki egzaminu oraz powiązane tematy w ramach transakcji.
   *
   * Użycie transakcji jest tutaj konieczne. Gwarantuje ona, że albo
   * wszystkie operacje (zapis egzaminu I zapis wszystkich jego tematów)
   * zostaną wykonane poprawnie, albo żadna z nich nie zostanie zapisana
   * na stałe. Chroni to bazę danych przed niespójnym stanem, np. sytuacją,
   * w której zapisany zostałby egzamin bez powiązanych z nim tematów.
   *
   * @param array<string, mixed> $examData Dane egzaminu do zapisania w tabeli `user_exams`.
   * @param array<int> $topicIds Tablica numerycznych ID tematów do zapisania
   * w tabeli `user_exam_topics`.
   *
   * @return bool `true` jeśli cała transakcja (zapis egzaminu i tematów)
   * się powiodła, `false` w przypadku jakiegokolwiek błędu.
   */
  public function saveExamWithTopics(array $examData, array $topicIds): bool
  {
    // Krok 1: Rozpocznij transakcję. Od tego momentu wszystkie operacje
    // są wykonywane "w pamięci" i czekają na zatwierdzenie.
    if (!$this->db->beginTransaction()) {
      return false;
    }

    try {
      // Krok 2: Wstawienie głównego rekordu do tabeli `user_exams`.
      $sqlExam = "INSERT INTO user_exams(
                                user_id, date_taken, is_full_exam, correct_answers, 
                                total_questions, score_percent, duration_seconds
                              ) VALUES (?, NOW(), ?, ?, ?, ?, ?)";

      $paramsExam = [
        $examData['user_id'],
        $examData['is_full_exam'],
        $examData['correct_answers'],
        $examData['total_questions'],
        $examData['score_percent'],
        $examData['duration_seconds'],
      ];

      if (!$this->db->execute($sqlExam, $paramsExam)) {
        // Rzucenie wyjątku powoduje przejście do bloku catch i wycofanie transakcji.
        throw new Exception('Nie udało się zapisać egzaminu.');
      }

      // Krok 3: Pobierz ID nowo wstawionego egzaminu.
      $examId = $this->db->lastInsertId();
      if (!$examId) {
        throw new Exception('Nie udało się pobrać ID ostatniego egzaminu.');
      }

      // Krok 4: Zapisz wszystkie powiązane tematy w tabeli pośredniczącej.
      if (!empty($topicIds)) {
        $sqlTopics = 'INSERT INTO user_exam_topics(user_exam_id, topic_id) VALUES (?, ?)';
        foreach ($topicIds as $topicId) {
          if (!$this->db->execute($sqlTopics, [$examId, $topicId])) {
            throw new Exception("Nie udało się zapisać powiązania z tematem o ID: $topicId");
          }
        }
      }

      // Krok 5: Jeśli wszystkie powyższe operacje się powiodły, zatwierdź transakcję.
      // Dopiero w tym momencie zmiany są trwale zapisywane w bazie danych.
      return $this->db->commit();
    } catch (Exception $e) {
      // Krok 6: W przypadku wystąpienia jakiegokolwiek błędu w bloku `try`,
      // wycofaj transakcję, anulując wszystkie dotychczasowe zmiany.
      error_log('Błąd transakcji podczas zapisu egzaminu: ' . $e->getMessage());
      $this->db->rollBack();
      return false;
    }
  }
}
