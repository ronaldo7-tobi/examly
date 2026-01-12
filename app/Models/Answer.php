<?php

namespace App\Models;

use App\Core\Database;

/**
 * Model Odpowiedzi (Answer).
 * Zarządza pobieraniem opcji odpowiedzi dla konkretnych wersji pytań.
 */
class Answer
{
  private Database $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Pobiera wszystkie odpowiedzi przypisane do konkretnej wersji pytania.
   * * @param int $questionVersionId ID wersji pytania (z tabeli question_versions).
   * @return array Tablica odpowiedzi z kolumnami: id, answer_text, is_correct.
   */
  public function getAnswersByVersionId(int $questionVersionId): array
  {
    // Zmiana: Filtrujemy po question_version_id i sortujemy wg answer_order
    $sql = "SELECT id, answer_text, is_correct 
            FROM answers 
            WHERE question_version_id = ? AND is_active = 1
            ORDER BY RAND();";

    return $this->db->fetchAll($sql, [$questionVersionId]);
  }

  /**
   * Pobiera poprawną odpowiedź dla konkretnej wersji pytania.
   * * @param int $versionId ID z tabeli question_versions.
   * @return array|false
   */
  public function getCorrectAnswerForVersion(int $versionId): array|false
  {
    $sql = "SELECT id, answer_text 
            FROM answers 
            WHERE question_version_id = ? AND is_correct = 1 AND is_active = 1 
            LIMIT 1";

    return $this->db->fetch($sql, [$versionId]);
  }

  /**
   * Pobiera odpowiedzi dla wielu wersji pytań naraz (optymalizacja dla testów).
   * * @param array<int> $versionIds Tablica ID wersji pytań.
   * @return array Odpowiedzi pogrupowane po question_version_id.
   */
  public function getAnswersForBatch(array $versionIds): array
  {
    if (empty($versionIds)) return [];

    $placeholders = implode(',', array_fill(0, count($versionIds), '?'));
    $sql = "SELECT id, question_version_id, answer_text, is_correct 
            FROM answers 
            WHERE question_version_id IN ($placeholders) AND is_active = 1
            ORDER BY question_version_id, RAND();";

    $results = $this->db->fetchAll($sql, $versionIds);

    // Grupowanie wyników w tablicę asocjacyjną dla łatwiejszego dostępu w kontrolerze
    $grouped = [];
    foreach ($results as $row) {
      $grouped[$row['question_version_id']][] = $row;
    }

    return $grouped;
  }
}
