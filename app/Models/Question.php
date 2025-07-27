<?php

/**
 * Model Pytania (Question).
 *
 * Klasa odpowiedzialna za definiowanie zapytań SQL dotyczących pytań.
 * Deleguje wykonanie zapytań i obsługę błędów do centralnej klasy Database.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class Question
{
    /**
     * Instancja naszej klasy do obsługi bazy danych.
     * @var Database
     */
    private Database $db;

    /**
     * Konstruktor klasy Question.
     * Pobiera instancję klasy Database.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Pobiera losowe pytania z określonych kategorii.
     *
     * @param array<string> $subjects Tablica z nazwami kategorii.
     * @param int $limit Maksymalna liczba pytań do pobrania.
     * @param string $examType Typ egzaminu (np. 'INF.03').
     * @return array Tablica z danymi pytań.
     */
    public function getQuestions(array $subjects, int $limit, string $examType): array
    {
        if (empty($subjects)) return [];

        $placeholders = implode(',', array_fill(0, count($subjects), '?'));
        $sql = "SELECT * FROM questions 
                WHERE subject IN ($placeholders) AND exam_type = ? 
                ORDER BY RAND() LIMIT " . (int)$limit;
        
        $params = array_merge($subjects, [$examType]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera losowe pytania, na które użytkownik jeszcze nie odpowiadał ("Nieodkryte").
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param array<string> $subjects Tablica z nazwami kategorii.
     * @param int $limit Limit pytań do pobrania.
     * @param string $examType Typ egzaminu.
     * @return array Tablica z "nieodkrytymi" pytaniami.
     */
    public function getUndiscoveredQuestions(int $userId, array $subjects, int $limit, string $examType): array
    {
        if (empty($subjects)) return [];

        $placeholders = implode(',', array_fill(0, count($subjects), '?'));
        $sql = "SELECT q.* FROM questions q
                LEFT JOIN user_progress up ON q.id = up.question_id AND up.user_id = ? 
                WHERE q.subject IN ($placeholders) AND q.exam_type = ? AND up.question_id IS NULL
                ORDER BY RAND() LIMIT " . (int)$limit;

        $params = array_merge([$userId], $subjects, [$examType]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera pytania, z którymi użytkownik radzi sobie najsłabiej (skuteczność <= 60%).
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param array<string> $subjects Tablica z nazwami kategorii.
     * @param int $limit Limit pytań do pobrania.
     * @param string $examType Typ egzaminu.
     * @return array Tablica z pytaniami o najniższej skuteczności.
     */
    public function getLowerAccuracyQuestions(int $userId, array $subjects, int $limit, string $examType): array
    {
        if (empty($subjects)) return [];
        
        $placeholders = implode(',', array_fill(0, count($subjects), '?'));
        $sql = "SELECT q.*, (up.correct_attempts * 100.0 / (up.correct_attempts + up.wrong_attempts)) as accuracy
                FROM questions q
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.subject IN ($placeholders) AND q.exam_type = ?
                AND (up.correct_attempts + up.wrong_attempts) > 0
                HAVING accuracy <= 60
                ORDER BY accuracy, RAND() LIMIT " . (int)$limit;

        $params = array_merge([$userId], $subjects, [$examType]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera pytania, które były rozwiązywane przez użytkownika najdawniej.
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param array<string> $subjects Tablica z nazwami kategorii.
     * @param int $limit Limit pytań do pobrania.
     * @param string $examType Typ egzaminu.
     * @return array Tablica z najdawniej powtarzanymi pytaniami.
     */
    public function getQuestionsRepeatedAtTheLatest(int $userId, array $subjects, int $limit, string $examType): array
    {
        if (empty($subjects)) return [];
        
        $placeholders = implode(',', array_fill(0, count($subjects), '?'));
        $sql = "SELECT q.* FROM questions q
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.subject IN ($placeholders) AND q.exam_type = ?
                ORDER BY up.last_attempt ASC LIMIT " . (int)$limit;

        $params = array_merge([$userId], $subjects, [$examType]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera pytania, na które użytkownik ostatnio odpowiedział błędnie.
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param array<string> $subjects Tablica z nazwami kategorii.
     * @param int $limit Limit pytań do pobrania.
     * @param string $examType Typ egzaminu.
     * @return array Tablica z ostatnimi błędnie rozwiązanymi pytaniami.
     */
    public function getLastMistakes(int $userId, array $subjects, int $limit, string $examType): array
    {
        if (empty($subjects)) return [];

        $placeholders = implode(',', array_fill(0, count($subjects), '?'));
        $sql = "SELECT q.* FROM questions q
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.subject IN ($placeholders) AND q.exam_type = ? AND up.last_result = 0
                ORDER BY RAND() LIMIT " . (int)$limit;

        $params = array_merge([$userId], $subjects, [$examType]);
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera jedno, konkretne pytanie na podstawie jego ID.
     *
     * @param int $id ID szukanego pytania.
     * @return array|false Tablica z danymi pytania lub `false`, jeśli pytanie o danym ID nie istnieje.
     */
    public function getQuestionById(int $id): array|false
    {
        $sql = "SELECT * FROM questions WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
}