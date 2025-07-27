<?php

/**
 * Model Odpowiedzi (Answer).
 *
 * Klasa odpowiedzialna za definiowanie zapytań SQL dotyczących odpowiedzi na pytania.
 * Deleguje wykonanie zapytań i obsługę błędów do centralnej klasy Database.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class Answer
{
    /**
     * Instancja naszej klasy do obsługi bazy danych.
     * @var Database
     */
    private Database $db;

    /**
     * Konstruktor klasy Answer.
     * Pobiera instancję klasy Database.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Pobiera wszystkie odpowiedzi przypisane do danego pytania.
     *
     * Zwraca tablicę odpowiedzi w losowej kolejności.
     *
     * @param int $questionId ID pytania, dla którego mają zostać pobrane odpowiedzi.
     * @return array Tablica asocjacyjna z odpowiedziami.
     */
    public function getAnswersToQuestion(int $questionId): array
    {
        $sql = "SELECT id, content FROM answers WHERE question_id = ? ORDER BY RAND()";
        
        return $this->db->fetchAll($sql, [$questionId]);
    }

    /**
     * Pobiera poprawną odpowiedź dla danego pytania.
     *
     * @param int $questionId ID pytania, dla którego szukana jest poprawna odpowiedź.
     * @return array|false Zwraca tablicę asocjacyjną z ID poprawnej odpowiedzi lub `false`.
     */
    public function getCorrectAnswerForQuestion(int $questionId): array|false
    {
        $sql = "SELECT id FROM answers WHERE question_id = ? AND is_correct = 1";
        
        return $this->db->fetch($sql, [$questionId]);
    }
}