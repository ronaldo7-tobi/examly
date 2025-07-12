<?php
class UserProgress
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function checkIfProgressExist($userId, $questionId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_progress
                                    WHERE user_id = :user_id 
                                    AND question_id = :question_id");
        $stmt->execute([
            ':user_id' => $userId,
            ':question_id' => $questionId
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function saveProgressForQuestion(int $userId, int $questionId, int $result): void
    {   
        if ($this->checkIfProgressExist($userId, $questionId)) {
            // Jeśli rekord już istnieje – aktualizujemy odpowiednie pola
            if ($result === 1) {
                $stmt = $this->db->prepare("UPDATE user_progress 
                                            SET correct_attempts = correct_attempts + 1,
                                                last_attempt = :curTime,
                                                last_result = :result
                                            WHERE user_id = :user_id AND question_id = :question_id");
            } else {
                $stmt = $this->db->prepare("UPDATE user_progress 
                                            SET wrong_attempts = wrong_attempts + 1,
                                                last_attempt = :curTime,
                                                last_result = :result 
                                            WHERE user_id = :user_id AND question_id = :question_id");
            }

            $stmt->execute([
                ':curTime' => time(),
                ':result' => $result,
                ':user_id' => $userId,
                ':question_id' => $questionId
            ]);

        } else {
            // Jeśli brak rekordu – dodajemy nowy
            if ($result === 1) {
                $correct = 1;
                $wrong = 0;
            } else {
                $correct = 0;
                $wrong = 1;
            }

            $stmt = $this->db->prepare("INSERT INTO user_progress (user_id, question_id,
                                         correct_attempts, wrong_attempts, last_result)
                                        VALUES (:user_id, :question_id, :correct, :wrong, :result)");

            $stmt->execute([
                ':user_id' => $userId,
                ':question_id' => $questionId,
                ':correct' => $correct,
                ':wrong' => $wrong,
                ':result' => $result
            ]);
        }
    }
}
?>
