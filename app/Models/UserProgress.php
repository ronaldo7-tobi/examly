<?php

/**
 * Model Postępów Użytkownika (UserProgress).
 *
 * Klasa ta zarządza zapisywaniem i aktualizowaniem indywidualnych postępów
 * każdego użytkownika w nauce, pytanie po pytaniu. Dane zbierane przez ten
 * model są fundamentem dla personalizowanych trybów nauki (np. "Pytania
 * do poprawy", "Nieodkryte") realizowanych w modelu `Question`.
 *
 * Kluczowym elementem jest wykorzystanie zoptymalizowanego zapytania "UPSERT",
 * które wykonuje operację wstawienia lub aktualizacji w jednym, atomowym
 * zapytaniu do bazy danych, co jest niezwykle wydajne.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class UserProgress
{
  /**
   * Instancja centralnego obiektu do obsługi bazy danych (Singleton).
   * @var Database
   */
  private Database $db;

  /**
   * Konstruktor modelu UserProgress.
   *
   * Pobiera instancję połączenia z bazą danych, która będzie używana
   * we wszystkich metodach tego modelu.
   */
  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Zapisuje lub aktualizuje postęp użytkownika dla pojedynczego pytania.
   *
   * Ta metoda implementuje wzorzec "UPSERT" (`INSERT ... ON DUPLICATE KEY UPDATE`).
   * Działa to w następujący sposób:
   * 1. Baza danych próbuje wstawić (`INSERT`) nowy wiersz z postępem.
   * 2. Jeśli wiersz dla tej pary `user_id` i `question_id` (która jest kluczem unikalnym)
   *    już istnieje, zamiast zwracać błąd, baza wykonuje sekcję `UPDATE`.
   * 3. W sekcji `UPDATE` inkrementujemy odpowiednie liczniki i aktualizujemy
   *    wynik oraz datę ostatniej próby.
   *
   * To podejście jest znacznie wydajniejsze niż standardowe "SELECT, potem INSERT lub UPDATE",
   * ponieważ wymaga tylko jednego zapytania do bazy danych.
   *
   * @param int $userId     ID zalogowanego użytkownika.
   * @param int $questionId ID pytania, na które odpowiedział.
   * @param int $result     Wynik odpowiedzi (`1` dla poprawnej, `0` dla błędnej).
   *
   * @return bool `true` w przypadku powodzenia operacji, `false` w przypadku błędu.
   */
  public function saveProgressForQuestion(int $userId, int $questionId, int $result): bool
  {
    // Krok 1: Przygotuj wartości do inkrementacji w zależności od wyniku.
    // Jeśli odpowiedź jest poprawna, zwiększamy 'correct_attempts' o 1.
    // Jeśli błędna, zwiększamy 'wrong_attempts' o 1.
    $correctIncrement = $result === 1 ? 1 : 0;
    $wrongIncrement = $result === 1 ? 0 : 1;

    // Krok 2: Zdefiniuj zapytanie SQL "UPSERT".
    $sql = "
            INSERT INTO user_progress 
              (user_id, question_id, correct_attempts, wrong_attempts, last_result, last_attempt)
            VALUES 
              (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
              correct_attempts = correct_attempts + VALUES(correct_attempts),
              wrong_attempts = wrong_attempts + VALUES(wrong_attempts),
              last_result = VALUES(last_result),
              last_attempt = NOW()
          ";

    // Krok 3: Przygotuj parametry dla zapytania.
    $params = [$userId, $questionId, $correctIncrement, $wrongIncrement, $result];

    // Krok 4: Wykonaj zapytanie.
    return $this->db->execute($sql, $params);
  }
}
