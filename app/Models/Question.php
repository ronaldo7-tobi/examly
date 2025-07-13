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

    public function getQuestionById(int $id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM questions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>