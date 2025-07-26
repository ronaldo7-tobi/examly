<?php

/**
 * Model Użytkownika (User).
 *
 * Klasa odpowiedzialna za operacje na danych użytkowników, takie jak rejestracja,
 * logowanie, weryfikacja i pobieranie danych. Wykorzystuje centralną klasę Database.
 *
 * @version 2.0.0
 * @author Tobiasz Szerszeń
 */
class UserModel
{
    /**
     * Instancja naszej klasy do obsługi bazy danych.
     * @var Database
     */
    private Database $db;

    /**
     * Konstruktor klasy UserModel.
     * Pobiera instancję klasy Database.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Sprawdza, czy w bazie danych istnieje użytkownik o podanym adresie email.
     *
     * @param string $email Adres email do sprawdzenia.
     * @return bool Zwraca true, jeśli email istnieje, w przeciwnym razie false.
     */
    public function checkEmail(string $email): bool
    {
        $sql = "SELECT id FROM users WHERE email = ?";
        $result = $this->db->fetch($sql, [$email]);
        
        return $result !== false;
    }

    /**
     * Rejestruje nowego użytkownika w bazie danych.
     * Hasło jest automatycznie hashowane przed zapisem.
     *
     * @param array<string, string> $data Dane użytkownika: 'first_name', 'last_name', 'email', 'password'.
     * @return bool Zwraca true, jeśli rejestracja powiodła się, w przeciwnym razie false.
     */
    public function register(array $data): bool
    {
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash)
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ];

        return $this->db->execute($sql, $params);
    }
    
    /**
     * Zmienia status weryfikacji użytkownika na "zweryfikowany".
     *
     * @param int $userId ID użytkownika do zweryfikowania.
     * @return bool Zwraca true w przypadku sukcesu, false w przypadku błędu.
     */
    public function verifyUser(int $userId): bool
    {
        $sql = "UPDATE users SET is_verified = 1 WHERE id = ?";
        return $this->db->execute($sql, [$userId]);
    }

    /**
     * Weryfikuje dane logowania i zwraca obiekt użytkownika w przypadku sukcesu.
     *
     * @param array<string, string> $data Tablica z danymi: 'email' i 'password'.
     * @return User|null Zwraca obiekt `User` w przypadku pomyślnego logowania, w przeciwnym razie `null`.
     */
    public function login(array $data): ?User
    {
        $sql = "SELECT id, first_name, last_name, email, password_hash, is_verified, role FROM users WHERE email = ?";
        $user_data = $this->db->fetch($sql, [$data['email']]);

        // Jeśli użytkownik istnieje i hasło się zgadza
        if ($user_data && password_verify($data['password'], $user_data['password_hash'])) {
            unset($user_data['password_hash']); // Usuń hash hasła przed utworzeniem obiektu
            return new User($user_data);
        }

        // W każdym innym przypadku (brak użytkownika, błędne hasło) zwracamy null
        return null;
    }

    /**
     * Zwraca ID ostatnio zarejestrowanego użytkownika.
     *
     * @return int|null Zwraca ID użytkownika lub null w przypadku błędu.
     */
    public function getLastInsertId(): ?int
    {
        return $this->db->lastInsertId();
    }

    /**
     * Pobiera dane użytkownika na podstawie jego ID.
     *
     * @param int $id ID szukanego użytkownika.
     * @return User|null Zwraca obiekt `User` lub `null`, jeśli użytkownik nie został znaleziony.
     */
    public function getUserById(int $id): ?User
    {
        $sql = "SELECT id, first_name, last_name, email, is_verified, role FROM users WHERE id = ?";
        $user_data = $this->db->fetch($sql, [$id]);

        return $user_data ? new User($user_data) : null;
    }
}