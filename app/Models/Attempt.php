<?php

namespace App\Models;

use App\Core\Database;
use Exception;

/**
 * Model Podejścia do Egzaminu (Attempt).
 * * Obsługuje tabelę `attempts` oraz relacje:
 * - `attempt_topics` (tematy egzaminu)
 * - `attempt_answers` (udzielone odpowiedzi - kluczowe dla analityki)
 */
class Attempt
{
	private Database $db;

	public function __construct()
	{
		$this->db = Database::getInstance();
	}

	public function createAttempt(array $attemptData, array $topicIds, array $answersDetails): int|false
	{
		if (!$this->db->beginTransaction()) return false;

		try {
			// 1. Zapis nagłówka
			$sqlAttempt = "INSERT INTO attempts (user_id, exam_type_id, test_type, started_at, completed_at, correct_count, total_questions) 
                       VALUES (?, ?, ?, DATE_SUB(NOW(), INTERVAL ? SECOND), NOW(), ?, ?)";

			if (!$this->db->execute($sqlAttempt, [
				$attemptData['user_id'],
				$attemptData['exam_type_id'],
				$attemptData['test_type'],
				$attemptData['duration_seconds'],
				$attemptData['correct_count'],
				$attemptData['total_questions']
			])) throw new \Exception('Błąd zapisu nagłówka.');

			$attemptId = $this->db->lastInsertId();

			// 2. Naprawa: Zapis wszystkich tematów (Multi-insert)
			if (!empty($topicIds)) {
				$topicIds = array_unique(array_map('intval', $topicIds));
				$placeholders = implode(',', array_fill(0, count($topicIds), "($attemptId, ?)"));
				$sqlTopics = "INSERT INTO attempt_topics (attempt_id, topic_id) VALUES $placeholders";

				if (!$this->db->execute($sqlTopics, $topicIds)) {
					throw new \Exception('Błąd zapisu tematów.');
				}
			}

			// 3. Zapis odpowiedzi (Multi-insert dla wydajności)
			if (!empty($answersDetails)) {
				$values = [];
				$params = [];
				foreach ($answersDetails as $ans) {
					$values[] = "(?, ?, ?)";
					array_push($params, $attemptId, $ans['question_version_id'], $ans['answer_id']);
				}
				$sqlAnswers = "INSERT INTO attempt_answers (attempt_id, question_version_id, answer_id) VALUES " . implode(',', $values);
				if (!$this->db->execute($sqlAnswers, $params)) throw new \Exception('Błąd zapisu odpowiedzi.');
			}

			$this->db->commit();
			return (int)$attemptId;
		} catch (\Exception $e) {
			$this->db->rollBack();
			error_log($e->getMessage());
			return false;
		}
	}

	/**
	 * Pobiera statystyki podejść dla użytkownika.
	 */
	public function getUserAttempts(int $userId): array
	{
		// Pobieramy dane złączone z exam_types, żeby wyświetlić kod egzaminu
		$sql = "SELECT a.*, et.code as exam_code, et.name as exam_name
                FROM attempts a
                JOIN exam_types et ON a.exam_type_id = et.id
                WHERE a.user_id = ?
                ORDER BY a.completed_at DESC";

		return $this->db->fetchAll($sql, [$userId]);
	}
}
