<?php
class Answer
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAnswersToQuestion(int $questionId): array
    {
        $stmt = $this->db->prepare("SELECT id, content FROM answers WHERE question_id = :question_id");
        $stmt->execute([':question_id' => $questionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
