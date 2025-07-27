<?php

/**
 * Model Postępów Użytkownika (UserProgress).
 *
 * Odpowiada za zapisywanie i aktualizowanie postępów użytkownika w nauce.
 * Wykorzystuje zoptymalizowane zapytanie "upsert" (INSERT ... ON DUPLICATE KEY UPDATE)
 * do wydajnej obsługi danych w jednym zapytaniu do bazy.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class UserProgress
{
    /**
     * Instancja naszej klasy do obsługi bazy danych.
     * @var Database
     */
    private Database $db;

    /**
     * Konstruktor klasy UserProgress.
     * Pobiera instancję klasy Database.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Zapisuje lub aktualizuje postęp użytkownika dla danego pytania.
     *
     * Używa jednej operacji "upsert". Jeśli wpis dla pary user_id i question_id
     * jeszcze nie istnieje, tworzy go. Jeśli istnieje, aktualizuje liczniki
     * prób, ostatni wynik i datę ostatniej próby.
     *
     * @param int $userId ID zalogowanego użytkownika.
     * @param int $questionId ID pytania, na które odpowiedział.
     * @param int $result Wynik odpowiedzi (1 dla poprawnej, 0 dla błędnej).
     * @return bool Zwraca true w przypadku powodzenia operacji, false w przypadku błędu.
     */
    public function saveProgressForQuestion(int $userId, int $questionId, int $result): bool
    {
        // Przygotowujemy wartości do inkrementacji w zapytaniu SQL.
        // Jeśli odpowiedź jest poprawna, zwiększamy 'correct_attempts' o 1, a 'wrong_attempts' o 0. I na odwrót.
        $correctIncrement = ($result === 1) ? 1 : 0;
        $wrongIncrement = ($result === 1) ? 0 : 1;

        $sql = "
            INSERT INTO user_progress 
                (user_id, question_id, correct_attempts, wrong_attempts, last_result, last_attempt)
            VALUES 
                (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                correct_attempts = correct_attempts + VALUES(correct_attempts),
                wrong_attempts = wrong_attempts + VALUES(wrong_attempts),
                last_result = VALUES(last_result),
                last_attempt = NOW()
        ";
        
        $params = [
            $userId,
            $questionId,
            $correctIncrement,
            $wrongIncrement,
            $result
        ];

        // Używamy metody execute(), ponieważ jest to operacja zapisu, która nie zwraca wierszy.
        return $this->db->execute($sql, $params);
    }
}