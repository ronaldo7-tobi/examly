<?php 

class Exam
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Zapisuje wyniki egzaminu oraz powiązane z nim tematy w ramach jednej transakcji.
     *
     * Krok po kroku:
     * 1. Rozpoczyna transakcję.
     * 2. Zapisuje główne dane egzaminu w tabeli `user_exams`.
     * 3. Pobiera ID nowo utworzonego egzaminu.
     * 4. W pętli zapisuje każdy temat (jego ID) w tabeli pośredniczącej `user_exam_topics`,
     * łącząc go z zapisanym egzaminem.
     * 5. Jeśli wszystkie operacje się powiodą, zatwierdza transakcję.
     * 6. W przypadku jakiegokolwiek błędu, wycofuje wszystkie zmiany.
     *
     * @param array<string, mixed> $examData Dane egzaminu (user_id, is_full_exam, itp.).
     * @param array<int> $topicIds Tablica numerycznych ID tematów, które wchodziły w skład egzaminu.
     * @return bool True w przypadku sukcesu, false w przypadku błędu.
     */
    public function saveExamWithTopics(array $examData, array $topicIds): bool
    {
        // 1. Rozpocznij transakcję
        if (!$this->db->beginTransaction()) {
            return false;
        }

        try {
            // 2. Zapisz główny rekord egzaminu
            $sqlExam = "INSERT INTO user_exams(
                            user_id, 
                            date_taken, 
                            is_full_exam, 
                            correct_answers, 
                            total_questions, 
                            score_percent,
                            duration_seconds
                        ) VALUES (?, NOW(), ?, ?, ?, ?, ?)";
            
            $paramsExam = [
                $examData['user_id'],
                $examData['is_full_exam'],
                $examData['correct_answers'],
                $examData['total_questions'],
                $examData['score_percent'],
                $examData['duration_seconds']
            ];

            if (!$this->db->execute($sqlExam, $paramsExam)) {
                throw new Exception("Nie udało się zapisać egzaminu.");
            }
            
            // 3. Pobierz ID nowo wstawionego egzaminu
            $examId = $this->db->lastInsertId();
            if (!$examId) {
                throw new Exception("Nie udało się pobrać ID ostatniego egzaminu.");
            }

            // 4. Zapisz tematy w tabeli pośredniczącej
            if (!empty($topicIds)) {
                $sqlTopics = "INSERT INTO user_exam_topics(user_exam_id, topic_id) VALUES (?, ?)";
                foreach ($topicIds as $topicId) {
                    if (!$this->db->execute($sqlTopics, [$examId, $topicId])) {
                        // Jeśli którekolwiek wiązanie się nie powiedzie, rzuć wyjątek
                        throw new Exception("Nie udało się zapisać tematu o ID: $topicId");
                    }
                }
            }
            
            // 5. Jeśli wszystko jest w porządku, zatwierdź transakcję
            return $this->db->commit();

        } catch (Exception $e) {
            // 6. W razie błędu, wycofaj wszystkie zmiany
            error_log("Błąd zapisu egzaminu: " . $e->getMessage());
            $this->db->rollBack();
            return false;
        }
    }
}