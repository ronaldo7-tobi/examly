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
   * Masowa aktualizacja postępów po zakończeniu testu.
   */
  public function updateBatchProgress(int $userId, array $answersDetails): void
  {
    if (empty($answersDetails)) return;

    foreach ($answersDetails as $detail) {
      $qvId = (int)$detail['question_version_id'];
      $ansId = (int)$detail['answer_id'];

      // Pobranie question_id i is_correct
      $sqlInfo = "SELECT qv.question_id, a.is_correct 
                  FROM question_versions qv
                  JOIN answers a ON a.question_version_id = qv.id
                  WHERE qv.id = ? AND a.id = ? LIMIT 1";

      $info = $this->db->fetch($sqlInfo, [$qvId, $ansId]);

      if ($info) {
        $this->saveProgress(
          $userId,
          (int)$info['question_id'],
          (int)$info['is_correct']
        );
      }
    }
  }

  /**
   * Zapisuje postęp dla pojedynczego pytania (UPSERT) z uwzględnieniem last_result.
   */
  public function saveProgress(int $userId, int $questionId, int $isCorrect): bool
  {
    $sql = "INSERT INTO user_progress 
              (user_id, question_id, attempts_count, correct_count, last_result, last_attempt_at)
            VALUES 
              (?, ?, 1, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
              attempts_count = attempts_count + 1,
              correct_count = correct_count + VALUES(correct_count),
              last_result = VALUES(last_result)";

    // $isCorrect trafia do correct_count (jako increment 0 lub 1) oraz jako stan last_result
    return $this->db->execute($sql, [$userId, $questionId, $isCorrect, $isCorrect]);
  }
}
