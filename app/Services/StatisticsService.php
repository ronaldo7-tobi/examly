<?php 

namespace App\Services;
use App\Core\Database;

class StatisticsService 
{

  private Database $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function getUserProgressData(int $userId): array
  {
    $sql = 'SELECT question_id, correct_attempts, wrong_attempts, last_attempt, last_result
            FROM user_progress
            WHERE user_id = ?';
    $result = $this->db->fetchAll($sql, [$userId]);
    return $result;
  }

  public function getUserExamsData(int $userId): array
  {
    $sql = 'SELECT id, date_taken, is_full_exam, correct_answers, total_questions, score_percent, duration_seconds
            FROM user_exams
            WHERE user_id = ?
            ORDER BY date_taken DESC';
    $result = $this->db->fetchAll($sql, [$userId]);

    // Przypisanie egzaminu do tematów (jeśli dotyczy)
    foreach ($result as $exam) {
      if (!$exam['is_full_exam']) {
        $topicsSql = 'SELECT t.name
                      FROM topics t
                      JOIN user_exam_topics et ON t.id = et.topic_id
                      WHERE et.exam_id = ?';
        $topics = $this->db->fetchAll($topicsSql, [$exam['id']]);
        $exam['topics'] = array_map(fn($t) => $t['name'], $topics);
      } else {
        $exam['topics'] = ['Pełny egzamin'];
      }
    }
    return $result;
  }
}