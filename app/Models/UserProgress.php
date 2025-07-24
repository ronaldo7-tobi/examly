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
        // Ustawiamy poprawny format daty raz, na początku funkcji
        $currentTime = date('Y-m-d H:i:s');

        if ($this->checkIfProgressExist($userId, $questionId)) {
            
            // Budujemy zapytanie dynamicznie, aby uniknąć powtarzania kodu
            $fieldToIncrement = ($result === 1) ? 'correct_attempts' : 'wrong_attempts';
            
            $sql = "UPDATE user_progress 
                    SET {$fieldToIncrement} = {$fieldToIncrement} + 1,
                        last_attempt = :curTime,
                        last_result = :result 
                    WHERE user_id = :user_id AND question_id = :question_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':curTime' => $currentTime,
                ':result' => $result,
                ':user_id' => $userId,
                ':question_id' => $questionId
            ]);

        } else {
            // --- DODAWANIE NOWEGO REKORDU ---
            
            $correct = ($result === 1) ? 1 : 0;
            $wrong = ($result === 1) ? 0 : 1;

            // POPRAWIONE ZAPYTANIE: Dodano brakujące pole `last_attempt`
            $sql = "INSERT INTO user_progress 
                        (user_id, question_id, correct_attempts, wrong_attempts, last_result, last_attempt)
                    VALUES 
                        (:user_id, :question_id, :correct, :wrong, :result, :curTime)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':question_id' => $questionId,
                ':correct' => $correct,
                ':wrong' => $wrong,
                ':result' => $result,
                ':curTime' => $currentTime // Używamy poprawnie sformatowanej daty
            ]);
        }
    }
}
?>
