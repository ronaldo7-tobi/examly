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
            exit("Brak tematów pytań.");
        }

        // Zamień tablicę tematów na listę wartości SQL w cudzysłowie
        $subjectStr = implode(',', array_map(function($s) {
            return "'" . addslashes($s) . "'";
        }, $subjects));

        $stmt = $this->db->prepare("SELECT * FROM questions 
                                    WHERE subject IN ($subjectStr) 
                                    AND exam_type = :exam_type 
                                    ORDER BY RAND() 
                                    LIMIT $limit");
        $stmt->execute([':exam_type' => $examType]);

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