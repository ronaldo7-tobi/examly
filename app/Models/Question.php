<?php
class Question
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getQuestions(array $subjects, int $limit, string $examType): array
    {
        if (empty($subjects)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($subjects), '?'));

        // Upewniamy się, że limit jest liczbą całkowitą
        $limit = (int)$limit;

        $sql = "SELECT * FROM questions 
                WHERE subject IN ($placeholders) 
                AND exam_type = ? 
                ORDER BY RAND() 
                LIMIT $limit"; // W przypadku LIMIT, wklejenie zrzutowanej na int wartości jest bezpieczne.

        $params = array_merge($subjects, [$examType]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Pobiera losowe pytania z wybranych kategorii, na które użytkownik jeszcze nie odpowiadał.
     *
     * @param int    $userId   ID zalogowanego użytkownika.
     * @param array  $subjects Tablica z nazwami kategorii (np. ['HTML', 'CSS']).
     * @param int    $limit    Limit pytań do pobrania.
     * @param string $examType Typ egzaminu (np. 'INF.03').
     * @return array Tablica z pytaniami.
     */
    public function getUndiscoveredQuestions(int $userId, array $subjects, int $limit, string $examType): array
    {
        if (empty($subjects)) {
            return [];
        }

        // Tworzymy placeholdery dla klauzuli IN (...)
        $subjectPlaceholders = implode(',', array_fill(0, count($subjects), '?'));
        $limit = (int)$limit;

        /*
        * POPRAWIONE ZAPYTANIE:
        * Używamy teraz tylko placeholderów '?' zamiast mieszać je z nazwanymi (:userId).
        * Kolejność parametrów musi być teraz taka sama, jak w tablicy $params.
        */
        $sql = "SELECT q.*
                FROM questions q
                LEFT JOIN user_progress up ON q.id = up.question_id AND up.user_id = ? 
                WHERE q.subject IN ($subjectPlaceholders)
                AND q.exam_type = ?
                AND up.question_id IS NULL
                ORDER BY RAND()
                LIMIT $limit";

        // Łączymy wszystkie parametry w JEDNEJ tablicy w odpowiedniej kolejności
        $params = array_merge([$userId], $subjects, [$examType]);

        try {
            $stmt = $this->db->prepare($sql);
            // PDO samo zajmie się prawidłowym bindowaniem typów (string/int)
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Dobra praktyka: logowanie błędu, aby ułatwić debugowanie w przyszłości
            // Na razie zwrócimy pustą tablicę, aby uniknąć "wykrzaczenia" aplikacji
            error_log("Błąd zapytania w getUndiscoveredQuestions: " . $e->getMessage());
            return [];
        }
    }

    public function getQuestionById(int $id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM questions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>