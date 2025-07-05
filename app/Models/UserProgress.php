<?php
class UserProgress
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function checkIfProgressExist($userId, $questionId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_progres 
                                    WHERE user_id = :user_id 
                                    AND question_id = :question_id");
        return $stmt->execute([
            ':user_id' => $userId,
            ':question_id' => $questionId
        ]);
    }
 
    public function saveProgressForQuestion(int $userId, int $questionId, string $result): void
    {   
        if ($this->checkIfProgressExist($userId, $questionId)) {
            // Jeśli rekord już istnieje – aktualizujemy odpowiednie pola
            if ($result === 'success') {
                $stmt = $this->db->prepare("UPDATE user_progress 
                                            SET correct_attempts = correct_attempts + 1, last_attempt = :result 
                                            WHERE user_id = :user_id AND question_id = :question_id");
            } else {
                $stmt = $this->db->prepare("UPDATE user_progress 
                                            SET wrong_attempts = wrong_attempts + 1, last_attempt = :result 
                                            WHERE user_id = :user_id AND question_id = :question_id");
            }

            $stmt->execute([
                ':result' => $result,
                ':user_id' => $userId,
                ':question_id' => $questionId
            ]);

        } else {
            // Jeśli brak rekordu – dodajemy nowy
            $correct = $result === 'success' ? 1 : 0;
            $wrong = $result === 'success' ? 0 : 1;

            $stmt = $this->db->prepare("INSERT INTO user_progress (user_id, question_id,
                                         correct_attempts, wrong_attempts, last_attempt)
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
