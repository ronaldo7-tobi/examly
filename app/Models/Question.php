<?php

namespace App\Models;

use App\Core\Database;

/**
 * Model Pytania (Question) - Wersja 2.0 (Wersjonowanie)
 * Obsługuje nową strukturę: questions -> question_versions -> answers
 */
class Question
{
  private Database $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  private function getBaseSelectionSql(): string
  {
    return "SELECT 
                    q.id as question_id, 
                    qv.id as question_version_id, 
                    q.topic_id, 
                    qv.question_text, 
                    qv.image_path, 
                    qv.explanation 
                FROM questions q
                JOIN question_versions qv ON q.id = qv.question_id";
  }

  // --- POPRAWIONE METODY ---

  public function getQuestions(array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) return [];

    $sql = $this->getBaseSelectionSql() . "
                WHERE q.is_active = 1 AND qv.is_active = 1
                AND q.topic_id IN ($placeholders) 
                AND q.exam_type_id = ? 
                ORDER BY RAND() LIMIT " . (int)$limit;

    return $this->db->fetchAll($sql, array_merge($subjectIds, [$examTypeId]));
  }

  public function getUndiscoveredQuestions(int $userId, array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) return [];

    // JOIN dodany PRZED WHERE
    $sql = $this->getBaseSelectionSql() . "
                LEFT JOIN user_progress up ON q.id = up.question_id AND up.user_id = ? 
                WHERE q.is_active = 1 AND qv.is_active = 1
                AND up.question_id IS NULL
                AND q.topic_id IN ($placeholders) 
                AND q.exam_type_id = ?
                ORDER BY RAND() LIMIT " . (int)$limit;

    return $this->db->fetchAll($sql, array_merge([$userId], $subjectIds, [$examTypeId]));
  }

  public function getLowerAccuracyQuestions(int $userId, array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) return [];

    $sql = $this->getBaseSelectionSql() . "
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.is_active = 1 AND qv.is_active = 1
                AND q.topic_id IN ($placeholders) AND q.exam_type_id = ?
                AND up.attempts_count > 0
                AND (up.correct_count * 100.0 / up.attempts_count) <= 60
                ORDER BY (up.correct_count * 100.0 / up.attempts_count) ASC, RAND() 
                LIMIT " . (int)$limit;

    return $this->db->fetchAll($sql, array_merge([$userId], $subjectIds, [$examTypeId]));
  }

  public function getQuestionsRepeatedAtTheLatest(int $userId, array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) return [];

    $sql = $this->getBaseSelectionSql() . "
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.is_active = 1 AND qv.is_active = 1
                AND q.topic_id IN ($placeholders) AND q.exam_type_id = ?
                ORDER BY up.updated_at ASC LIMIT " . (int)$limit;

    return $this->db->fetchAll($sql, array_merge([$userId], $subjectIds, [$examTypeId]));
  }

  public function getLastMistakes(int $userId, array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) return [];

    $sql = $this->getBaseSelectionSql() . "
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.is_active = 1 AND qv.is_active = 1
                AND q.topic_id IN ($placeholders) 
                AND q.exam_type_id = ? 
                AND up.last_result = 0
                ORDER BY RAND() LIMIT " . (int)$limit;

    return $this->db->fetchAll($sql, array_merge([$userId], $subjectIds, [$examTypeId]));
  }

  /**
   * Pobiera bazowe ID pytania na podstawie ID wersji.
   * * @param int $versionId
   * @return int|null
   */
  public function getQuestionIdByVersionId(int $versionId): ?int
  {
    $sql = "SELECT question_id FROM question_versions WHERE id = ? LIMIT 1";
    $result = $this->db->fetch($sql, [$versionId]);
    return $result ? (int)$result['question_id'] : null;
  }

  public function getQuestionById(int $id): array|false
  {
    // Tutaj też musimy dodać WHERE ręcznie
    $sql = $this->getBaseSelectionSql() . " WHERE q.is_active = 1 AND qv.is_active = 1 AND q.id = ? LIMIT 1";
    return $this->db->fetch($sql, [$id]);
  }

  public function getExamTypeIdByCode(string $code): ?int
  {
    $sql = 'SELECT id FROM exam_types WHERE code = ? AND is_active = 1 LIMIT 1';
    $result = $this->db->fetch($sql, [$code]);
    return $result['id'] ?? null;
  }

  public function getTopicIdsByExamType(int $examTypeId): array
  {
    $sql = 'SELECT DISTINCT topic_id FROM questions WHERE exam_type_id = ? AND is_active = 1';
    $results = $this->db->fetchAll($sql, [$examTypeId]);
    return array_column($results, 'topic_id');
  }

  private function buildInClausePlaceholders(array $ids): ?string
  {
    if (empty($ids)) return null;
    return implode(',', array_fill(0, count($ids), '?'));
  }
}
