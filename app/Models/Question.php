<?php

/**
 * Model Pytania (Question).
 *
 * Klasa odpowiedzialna za definiowanie zapytań SQL dotyczących pytań.
 * Operuje na numerycznych ID (kluczach obcych) w celu filtrowania i pobierania danych,
 * co jest zgodne ze znormalizowaną strukturą bazy danych. Deleguje wykonanie
 * zapytań i obsługę błędów do centralnej klasy Database.
 *
 * @version 1.1.0
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
     * Pobiera losowe pytania z określonych kategorii i typu egzaminu.
     *
     * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
     * @param int $limit Maksymalna liczba pytań do pobrania.
     * @param int $examTypeId Numeryczne ID typu egzaminu.
     * @return array Tablica z danymi pytań.
     */
    public function getQuestions(array $subjectIds, int $limit, int $examTypeId): array
    {
        if (empty($subjectIds)) return [];

        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
        $sql = "SELECT * FROM questions 
                WHERE topic_id IN ($placeholders) AND exam_type_id = ? 
                ORDER BY RAND() LIMIT " . (int)$limit;
        
        $params = array_merge($subjectIds, [$examTypeId]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera losowe pytania, na które użytkownik jeszcze nie odpowiadał ("Nieodkryte").
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
     * @param int $limit Limit pytań do pobrania.
     * @param int $examTypeId Numeryczne ID typu egzaminu.
     * @return array Tablica z "nieodkrytymi" pytaniami.
     */
    public function getUndiscoveredQuestions(int $userId, array $subjectIds, int $limit, int $examTypeId): array
    {
        if (empty($subjectIds)) return [];

        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
        $sql = "SELECT q.* FROM questions q
                LEFT JOIN user_progress up ON q.id = up.question_id AND up.user_id = ? 
                WHERE q.topic_id IN ($placeholders) AND q.exam_type_id = ? AND up.question_id IS NULL
                ORDER BY RAND() LIMIT " . (int)$limit;

        $params = array_merge([$userId], $subjectIds, [$examTypeId]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera pytania, z którymi użytkownik radzi sobie najsłabiej (skuteczność <= 60%).
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
     * @param int $limit Limit pytań do pobrania.
     * @param int $examTypeId Numeryczne ID typu egzaminu.
     * @return array Tablica z pytaniami o najniższej skuteczności.
     */
    public function getLowerAccuracyQuestions(int $userId, array $subjectIds, int $limit, int $examTypeId): array
    {
        if (empty($subjectIds)) return [];
        
        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
        $sql = "SELECT q.*, (up.correct_attempts * 100.0 / (up.correct_attempts + up.wrong_attempts)) as accuracy
                FROM questions q
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.topic_id IN ($placeholders) AND q.exam_type_id = ?
                AND (up.correct_attempts + up.wrong_attempts) > 0
                HAVING accuracy <= 60
                ORDER BY accuracy, RAND() LIMIT " . (int)$limit;

        $params = array_merge([$userId], $subjectIds, [$examTypeId]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera pytania, które były rozwiązywane przez użytkownika najdawniej.
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
     * @param int $limit Limit pytań do pobrania.
     * @param int $examTypeId Numeryczne ID typu egzaminu.
     * @return array Tablica z najdawniej powtarzanymi pytaniami.
     */
    public function getQuestionsRepeatedAtTheLatest(int $userId, array $subjectIds, int $limit, int $examTypeId): array
    {
        if (empty($subjectIds)) return [];
        
        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
        $sql = "SELECT q.* FROM questions q
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.topic_id IN ($placeholders) AND q.exam_type_id = ?
                ORDER BY up.last_attempt ASC LIMIT " . (int)$limit;

        $params = array_merge([$userId], $subjectIds, [$examTypeId]);

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Pobiera pytania, na które użytkownik ostatnio odpowiedział błędnie.
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
     * @param int $limit Limit pytań do pobrania.
     * @param int $examTypeId Numeryczne ID typu egzaminu.
     * @return array Tablica z ostatnimi błędnie rozwiązanymi pytaniami.
     */
    public function getLastMistakes(int $userId, array $subjectIds, int $limit, int $examTypeId): array
    {
        if (empty($subjectIds)) return [];

        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
        $sql = "SELECT q.* FROM questions q
                INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
                WHERE q.topic_id IN ($placeholders) AND q.exam_type_id = ? AND up.last_result = 0
                ORDER BY RAND() LIMIT " . (int)$limit;

        $params = array_merge([$userId], $subjectIds, [$examTypeId]);
        
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

    /**
     * Pobiera ID typu egzaminu na podstawie jego kodu.
     *
     * Wyszukuje w tabeli `exam_types` wiersz pasujący do podanego kodu
     * i zwraca jego numeryczne ID.
     *
     * @param string $code Kod egzaminu do wyszukania (np. 'INF.03').
     * @return int|null Zwraca numeryczne ID egzaminu lub `null`, jeśli nie znaleziono.
     */
    public function getExamTypeIdByCode(string $code): ?int
    {
        $sql = "SELECT id FROM exam_types WHERE code = ? LIMIT 1";
        $result = $this->db->fetch($sql, [$code]);
        // Jeśli fetch() zwróci tablicę, pobierz z niej 'id'. 
        // W przeciwnym razie (gdy zwróci false), operator ?? zwróci null.
        return $result['id'] ?? null;
    }

    /**
     * Pobiera unikalne ID wszystkich tematów powiązanych z danym typem egzaminu.
     *
     * @param int $examTypeId Numeryczne ID typu egzaminu.
     * @return array<int> Tablica z unikalnymi ID tematów.
     */
    public function getTopicIdsByExamType(int $examTypeId): array
    {
        $sql = "SELECT DISTINCT topic_id FROM questions WHERE exam_type_id = ?";
        $results = $this->db->fetchAll($sql, [$examTypeId]);
        // Zwraca czystą, jednowymiarową tablicę samych ID, np. [1, 2, 3, 4, 5, 6]
        return array_column($results, 'topic_id');
    }
}