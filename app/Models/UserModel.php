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
     * 
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
     * Ustawia status weryfikacji użytkownika na true.
     * 
     * @param $userId Id użytkownika poddanego operacji.
     * 
     * @return bool Zwraca true, jeśli uda się zweryfikować użytkownika lub false w przeciwnym wypadku.
     */
    public function verifyUser(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET is_verified = 1 WHERE id = :id");
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * Próbuje zalogować użytkownika na podstawie podanego emaila i hasła.
     *
     * @param array $data Tablica z kluczami 'email' oraz 'password'.
     * 
     * @return User|array Zwraca obiekt User, jeśli dane są poprawne, lub tablicę błędów podczas logowania.
     */
    public function login(array $data): User|array
    {   
        $errors = [];

        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, password_hash, is_verified, role FROM users 
                                    WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        $user = $stmt->fetch();
        if($user == null) {
            $errors[] = "W systemie nie istnieje konto z podanym adresem e-mail.";
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        if ($user && password_verify($data['password'], $user['password_hash'])) {
            unset($user['password_hash']);
            return new User($user);
        } else {
            $errors[] = "Błędne hasło. Spróbuj ponownie.";
        }

        return [
            'success' => false, 
            'errors' => $errors
        ];
    }

    /**
     * Zwraca ID ostatnio dodanego użytkownika.
     *
     * @return int|null Zwraca ID użytkownika lub null, jeśli nie można go pobrać.
     */
    public function getLastInsertId(): ?int
    {
        $id = $this->db->lastInsertId();
        return $id ? (int) $id : null;
    }

    /**
     * Pobiera użytkownika na podstawie jego ID.
     *
     * @param int $id ID użytkownika.
     * 
     * @return User|null Obiekt użytkownika lub null, jeśli nie znaleziono.
     */
    public function getUserById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, is_verified, role FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        if ($user) {
            return new User($user);
        }

        return null;
    }
}
?>
