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
        $stmt = $this->db->prepare("SELECT id, content FROM answers WHERE question_id = :question_id ORDER BY RAND()");
        $stmt->execute([':question_id' => $questionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCorrectAnswerForQuestion(int $questionId): bool|array
    {
        $stmt = $this->db->prepare("SELECT id FROM answers WHERE question_id = :qid AND is_correct = 1");
        $stmt->execute([':qid' => $questionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
