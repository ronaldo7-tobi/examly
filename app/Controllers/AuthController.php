<?php

/**
 * Kontroler Uwierzytelniania (Auth).
 *
 * Klasa-serwis, która orkiestruje procesy rejestracji i logowania.
 * Odpowiada za walidację danych wejściowych, komunikację z modelem użytkownika
 * oraz zarządzanie sesją użytkownika.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class AuthController
{ 
    /**
     * Instancja modelu użytkownika.
     * @var UserModel
     */
    private UserModel $userModel;

    /**
     * Obiekt aktualnie zalogowanego użytkownika.
     * @var User|null
     */
    private ?User $loggedUser = null;

    /**
     * Konstruktor, inicjalizuje instancję UserModel.
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Rejestruje nowego użytkownika po walidacji danych z formularza.
     *
     * @param array<string, string> $formData Dane z formularza rejestracji.
     * @return array Wynik operacji: `['success' => bool, 'errors' => array]`.
     */
    public function register(array $formData): array
    {
        $errors = [];

        $firstName = trim($formData['first_name'] ?? '');
        $lastName = trim($formData['last_name'] ?? '');
        $email = trim($formData['email'] ?? '');
        $password = $formData['password'] ?? '';
        $confirmPassword = $formData['confirm_password'] ?? '';
        
        // Walidacja danych...
        if (mb_strlen($firstName) < 2 || !preg_match('/^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]{1,}$/u', $firstName)) {
            $errors[] = 'Imię musi zaczynać się z dużej litery i mieć co najmniej 2 znaki.';
        }
        if (mb_strlen($lastName) < 2 || !preg_match('/^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]{1,}$/u', $lastName)) {
            $errors[] = 'Nazwisko musi zaczynać się z dużej litery i mieć co najmniej 2 znaki.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Podano niepoprawny format adresu e-mail.';
        } elseif ($this->userModel->checkEmail($email)) {
            $errors[] = 'Konto z podanym adresem e-mail już istnieje w systemie.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Hasło musi mieć co najmniej 6 znaków.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Podane hasła nie są identyczne.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Rejestracja użytkownika przez model
        $success = $this->userModel->register([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'password'   => $password
        ]);
        
        if ($success) {
            // Zapisz ID nowego użytkownika do sesji na potrzeby procesu weryfikacji
            $userId = $this->userModel->getLastInsertId();
            $_SESSION['verify_user_id'] = $userId;
            return ['success' => true, 'errors' => []];
        }

        // Jeśli rejestracja w modelu się nie udała (np. błąd bazy danych)
        return ['success' => false, 'errors' => ['Rejestracja nie powiodła się z powodu błędu serwera.']];
    }

    /**
     * Loguje użytkownika na podstawie danych z formularza.
     *
     * @param array<string, string> $formData Dane formularza: 'email' i 'password'.
     * @return array Wynik operacji: `['success' => bool, 'errors' => array]`.
     */
    public function login(array $formData): array
    { 
        $email = $formData['email'] ?? '';
        $password = $formData['password'] ?? '';

        if (empty($email) || empty($password)) {
            return ['success' => false, 'errors' => ['Pola e-mail i hasło są wymagane.']];
        }

        // Model zwraca obiekt User lub null
        $user = $this->userModel->login($formData);

        if ($user instanceof User) {
            // Dodatkowy warunek: sprawdź, czy konto jest zweryfikowane
            if (!$user->isVerified()) {
                return ['success' => false, 'errors' => ['Konto nie zostało jeszcze zweryfikowane. Sprawdź swoją skrzynkę e-mail.']];
            }
            
            // Logowanie pomyślne: ustaw użytkownika w sesji
            $this->loggedUser = $user;
            $_SESSION['user'] = $this->loggedUser;
            
            return ['success' => true];
        }

        // Jeśli $user jest null, oznacza to błędne dane logowania
        return ['success' => false, 'errors' => ['Niepoprawny e-mail lub hasło.']];
    }
}