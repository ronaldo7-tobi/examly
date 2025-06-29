<?php
/**
 * Klasa odpowiadająca za logikę aplikacji dotyczącą logowania i rejestracji.
 */
class AuthController
{   
    /**
     * Instancja klasy UserModel, która zarządza dostępem do danych użytkowników w bazie.
     *
     * @var UserModel
     */
    private UserModel $userModel;

    /**
     * Przechowuje aktualnie zalogowanego użytkownika lub null, jeśli brak zalogowanego użytkownika.
     *
     * @var User|null
     */
    private ?User $loggedUser = null;

    // Konstruktor, inicjalizuje instancję UserModel.
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Rejestruje nowego użytkownika w systemie po walidacji danych formularza.
     *
     * Funkcja najpierw waliduje dane takie jak imię, nazwisko, email oraz hasło.
     * Jeśli walidacja przejdzie pomyślnie, wywołuje metodę rejestracji w UserModel.
     * 
     * @param array $formData Tablica asocjacyjna z danymi z formularza rejestracji:
     *                        - 'first_name' => string Imię użytkownika
     *                        - 'last_name' => string Nazwisko użytkownika
     *                        - 'email' => string Adres e-mail użytkownika
     *                        - 'password' => string Hasło
     *                        - 'confirm_password' => string Potwierdzenie hasła
     * 
     * @return array Zwraca tablicę z wynikiem działania oraz ewentualnymi błędami:
     *               - 'success' => bool Informacja, czy rejestracja zakończyła się sukcesem
     *               - 'errors' => array Lista komunikatów o błędach, jeśli wystąpiły
     */
    public function register(array $formData): array
    {
        $errors = [];

        $firstName = trim($formData['first_name'] ?? '');
        $lastName = trim($formData['last_name'] ?? '');
        $email = trim($formData['email'] ?? '');
        $password = $formData['password'] ?? '';
        $confirmPassword = $formData['confirm_password'] ?? '';
        
        // Walidacja imienia
        if (mb_strlen($firstName) < 2 || !preg_match('/^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]{1,}$/u', $firstName)) {
            $errors[] = 'Imię musi zaczynać się z dużej litery i mieć co najmniej 2 znaki.';
        }
        // Walidacja nazwiska
        if (mb_strlen($lastName) < 2 || !preg_match('/^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]{1,}$/u', $lastName)) {
            $errors[] = 'Nazwisko musi zaczynać się z dużej litery i mieć co najmniej 2 znaki.';
        }
        // Walidacja adresu e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Niepoprawny e-mail.';
        } elseif ($this->userModel->checkEmail($email)) {
            $errors[] = 'Ten e-mail już istnieje.';
        }
        // Walidacja hasła
        if (strlen($password) < 6) {
            $errors[] = 'Hasło musi mieć co najmniej 6 znaków.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Hasła nie są zgodne.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $success = $this->userModel->register([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'password'   => $password
        ]);
        
        // Jeśli udało się zarejestrować, zapisujemy chwilowo do sesji id tego użytkownika, 
        // by móć operować na nim w procesie weryfikacji adresu e-mail.
        if($success) {
            $userId = $this->userModel->getLastInsertId();
            $_SESSION['verify_user_id'] = $userId;
        }

        return [
            'success' => $success,
            'errors' => $success ? [] : ['Rejestracja nie powiodła się.']
        ];
    }

    /**
     * Próbuje zalogować użytkownika na podstawie danych z formularza.
     * 
     * @param array $formData Tablica z danymi formularza.
     * @return bool|array Zwraca true, jeśli logowanie powiodło się (użytkownik znaleziony i hasło poprawne), 
     *              w przeciwnym razie tablica błędów w logowaniu.
     */
    public function login(array $formData): bool|array
    {   
        // Walidacja pól czy nie są puste.
        $email = $formData['email'] ?? '';
        $password = $formData['password'] ?? '';

        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'errors' => ['Email i hasło są wymagane.']
            ];
        }

        $result = $this->userModel->login($formData);

        if ($result instanceof User) {
            $this->loggedUser = $result;
            return true;
        }

        return $result;
    }

    /**
     * Zwraca aktualnie zalogowanego użytkownika.
     * 
     * @return User|null Obiekt zalogowanego użytkownika lub null, jeśli nikt nie jest zalogowany.
     */
    public function getLoggedUser(): ?User
    {
        return $this->loggedUser;
    }
}
?>