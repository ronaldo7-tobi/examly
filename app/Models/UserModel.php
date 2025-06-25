<?php
/**
 * Klasa UserModel odpowiedzialna za operacje na danych użytkowników w bazie danych.
 * 
 * Zawiera metody do rejestracji i logowania.
 */
class UserModel
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
     * Sprawdza istnienie adresu email w systemie.
     * 
     * @param $email Email użytkownika.
     * @return bool Zwraca true, jeśli e-mail istnieje, w przeciwnym razie false.
     */
    public function checkEmail(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() !== false;
    }

    /**
     * Rejestruje nowego użytkownika w bazie danych.
     *
     * @param array $data Tablica danych użytkownika, zawierająca klucze:
     *                    'first_name', 'last_name', 'email', 'password'.
     * @return bool Zwraca true, jeśli rejestracja powiodła się, lub false w przeciwnym razie.
     */
    public function register(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, email, password_hash)
            VALUES (:first_name, :last_name, :email, :password_hash)");

        return $stmt->execute([
            ':first_name'    => $data['first_name'],
            ':last_name'     => $data['last_name'],
            ':email'         => $data['email'],
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Próbuje zalogować użytkownika na podstawie podanego emaila i hasła.
     *
     * @param array $data Tablica z kluczami 'email' oraz 'password'.
     * @return User|false Zwraca obiekt User, jeśli dane są poprawne, lub false w przypadku błędu.
     */
    public function login(array $data): User|false
    {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password_hash, is_verified, role FROM users 
                                    WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        $user = $stmt->fetch();

        if ($user && password_verify($data['password'], $user['password_hash'])) {
            unset($user['password_hash']);
            return new User($user);
        }

        return false;
    }
}
?>
