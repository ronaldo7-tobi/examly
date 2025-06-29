<?php
/**
 * Klasa TokenService odpowiedzialna za obsługę tokenów weryfikacyjnych i resetujących.
 * 
 * Obsługuje generowanie, walidację i usuwanie tokenów z bazy danych.
 */
class TokenService
{
    /**
     * Obiekt PDO do komunikacji z bazą danych.
     * 
     * @var PDO
     */
    private PDO $db;

    /**
     * Konstruktor inicjalizujący połączenie z bazą danych.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generuje nowy token i zapisuje go w bazie danych.
     * 
     * @param int $userId ID użytkownika.
     * @param string $type Typ tokena: email_verify, password_reset, email_reset.
     * @return string Wygenerowany token.
     */
    public function generateToken(int $userId, string $type): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare("INSERT INTO user_tokens (user_id, token, type, expires_at)
            VALUES (:user_id, :token, :type, DATE_ADD(NOW(), INTERVAL 1 HOUR))");

        $stmt->execute([
            ':user_id' => $userId,
            ':token'   => $token,
            ':type'    => $type
        ]);

        return $token;
    }

    /**
     * Sprawdza ważność tokena i zwraca ID użytkownika, jeśli token jest poprawny.
     * 
     * @param string $token Token do sprawdzenia.
     * @param string $type Typ tokena.
     * @return int|null ID użytkownika lub null, jeśli token nieprawidłowy lub wygasł.
     */
    public function validateToken(string $token, string $type): ?int
    {
        $stmt = $this->db->prepare("SELECT user_id FROM user_tokens
            WHERE token = :token AND type = :type AND expires_at > NOW()");
        
        $stmt->execute([
            ':token' => $token,
            ':type'  => $type
        ]);

        $result = $stmt->fetch();
        return $result ? (int)$result['user_id'] : null;
    }

    /**
     * Usuwa token z bazy danych.
     * 
     * @param string $token Token do usunięcia.
     * @return void
     */
    public function deleteToken(string $token): void
    {
        $stmt = $this->db->prepare("DELETE FROM user_tokens WHERE token = :token");
        $stmt->execute([':token' => $token]);
    }

    public function getTokenRecord(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM user_tokens WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $record = $stmt->fetch();
        return $record ?: null;
    }
}
