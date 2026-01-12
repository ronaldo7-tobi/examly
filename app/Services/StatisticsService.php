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

	/**
	 * Pobiera szczegółowe zestawienie pytań i odpowiedzi dla konkretnego podejścia.
	 * Wykorzystuje nową tabelę attempt_answers i wersjonowanie treści.
	 */
	public function getAttemptDetails(int $attemptId, int $userId): array
	{
		// Sprawdzamy uprawnienia i pobieramy dane w jednym zapytaniu
		$sql = "SELECT 
                    qv.question_text, 
                    qv.explanation, 
                    qv.image_path,
                    ans_user.answer_text as user_answer,
                    ans_user.is_correct as is_user_correct,
                    ans_correct.answer_text as correct_answer
                FROM attempt_answers aa
                JOIN attempts a ON aa.attempt_id = a.id
                JOIN question_versions qv ON aa.question_version_id = qv.id
                JOIN answers ans_user ON aa.answer_id = ans_user.id
                JOIN answers ans_correct ON ans_correct.question_version_id = qv.id AND ans_correct.is_correct = 1
                WHERE aa.attempt_id = ? AND a.user_id = ?";

		return $this->db->fetchAll($sql, [$attemptId, $userId]);
	}

	/**
	 * Pobiera listę podejść (historycznych egzaminów).
	 */
	public function getUserExamsData(int $userId): array
	{
		$sql = "SELECT 
                    id, completed_at as date_taken, test_type, 
                    correct_count as correct_answers, total_questions, 
                    ROUND((correct_count / total_questions) * 100, 2) as score_percent,
                    TIMESTAMPDIFF(SECOND, started_at, completed_at) as duration_seconds
                FROM attempts 
                WHERE user_id = ? 
                ORDER BY completed_at DESC";

		$attempts = $this->db->fetchAll($sql, [$userId]);

		foreach ($attempts as &$attempt) {
			$attempt['is_full_exam'] = ($attempt['test_type'] === 'full_exam') ? 1 : 0;

			if ($attempt['is_full_exam']) {
				$attempt['topics'] = ['Pełny egzamin'];
			} else {
				$topicsSql = "SELECT t.name FROM topics t 
                              JOIN attempt_topics atop ON t.id = atop.topic_id 
                              WHERE atop.attempt_id = ?";
				$topics = $this->db->fetchAll($topicsSql, [$attempt['id']]);
				$attempt['topics'] = array_column($topics, 'name');
			}
		}
		return $attempts;
	}
}
