<?php
/**
 * Serwis Tokenów (TokenService).
 *
 * Klasa odpowiedzialna za zarządzanie cyklem życia tokenów jednorazowych
 * (np. do weryfikacji e-maila, resetowania hasła).
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class TokenService
{
    /**
     * Instancja naszej klasy do obsługi bazy danych.
     * @var Database
     */
    private Database $db;

    /**
     * Konstruktor inicjalizujący połączenie z bazą danych.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generuje nowy, unikalny token i zapisuje go w bazie danych.
     *
     * @param int $userId ID użytkownika, dla którego tworzony jest token.
     * @param string $type Typ tokena (np. 'email_verify', 'password_reset').
     * @return string Wygenerowany, 64-znakowy token.
     */
    public function generateToken(int $userId, string $type): string
    {
        $token = bin2hex(random_bytes(32));
        $sql = "INSERT INTO user_tokens (user_id, token, type, expires_at)
                VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))";

        $this->db->execute($sql, [$userId, $token, $type]);

        return $token;
    }

    /**
     * Usuwa wszystkie tokeny danego typu dla określonego użytkownika.
     * Używane np. po pomyślnej weryfikacji, aby usunąć stare tokeny.
     *
     * @param int $userId ID użytkownika.
     * @param string $type Typ tokenów do usunięcia.
     * @return void
     */
    public function deleteTokensForUserByType(int $userId, string $type): void
    {
        $sql = "DELETE FROM user_tokens WHERE user_id = ? AND type = ?";
        $this->db->execute($sql, [$userId, $type]);
    }

    /**
     * Pobiera pełny rekord tokena z bazy danych na podstawie jego wartości.
     *
     * @param string $token Wartość tokena do wyszukania.
     * @return array|null Zwraca tablicę z danymi rekordu tokena lub null, jeśli nie istnieje.
     */
    public function getTokenRecord(string $token): ?array
    {
        $sql = "SELECT * FROM user_tokens WHERE token = ?";
        $result = $this->db->fetch($sql, [$token]);
        
        return $result ?: null; // Zapewnia, że zawsze zwracany jest null zamiast false
    }
}