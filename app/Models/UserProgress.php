<?php

namespace App\Models;

use App\Core\Database;

class UserProgress
{
  private Database $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Masowa aktualizacja postępu po zakończeniu testu.
   */
  public function updateBatchProgress(int $userId, array $answersDetails): void
  {
    if (empty($answersDetails)) return;

    foreach ($answersDetails as $detail) {
      $qvId = (int)$detail['question_version_id'];
      $answerId = (int)$detail['answer_id'];

      // Zoptymalizowane zapytanie rozwiązujące question_id z wersji
      $sql = "INSERT INTO user_progress (user_id, question_id, attempts_count, correct_count, last_result)
                SELECT ?, qv.question_id, 1, a.is_correct, a.is_correct
                FROM question_versions qv
                JOIN answers a ON a.id = ?
                WHERE qv.id = ?
                ON DUPLICATE KEY UPDATE 
                    attempts_count = attempts_count + 1,
                    correct_count = correct_count + VALUES(correct_count),
                    last_result = VALUES(last_result)";

      $this->db->execute($sql, [$userId, $answerId, $qvId]);
    }
  }

  /**
   * Zapisuje postęp dla pojedynczego pytania (Tryb: Jedno pytanie).
   */
  public function saveProgress(int $userId, int $questionId, int $isCorrect): bool
  {
    $sql = "INSERT INTO user_progress 
              (user_id, question_id, attempts_count, correct_count, last_result)
            VALUES 
              (?, ?, 1, ?, ?)
            ON DUPLICATE KEY UPDATE
              attempts_count = attempts_count + 1,
              correct_count = correct_count + VALUES(correct_count),
              last_result = VALUES(last_result)";

    return $this->db->execute($sql, [$userId, $questionId, $isCorrect, $isCorrect]);
  }
}
