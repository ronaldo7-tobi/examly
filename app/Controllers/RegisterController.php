<?php
/**
 * Klasa odpowiadająca za logikę widoku rejestracji.
 */
class RegisterController
{   
    /**
     * Instancja klasy AuthController.
     * 
     * @var AuthController
     */
    private AuthController $auth;

    // Konstruktor, inicjalizuje instnację AuthController.
    public function __construct()
    {
        $this->auth = new AuthController();
    }

    /**
     * Zarządza widokiem rejestracji.
     * 
     * @return void
     */
    public function handleRequest(): void
    {
        $errors = [];
        $formData = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $_POST;
            $result = $this->auth->register($formData);

            if ($result['success']) {
                header('Location: verify_email');
                exit;
            } else {
                $errors = $result['errors'];
            }
        }
        include __DIR__ . '/../../views/register.php';
    }

    public function showVerificationPage($tab): void
    {
        require_once __DIR__ . '/../../views/verify_email.php';
    }

    public function sendVerificationEmail(): array
    {
        $errors = null;
        $success = null;

        if (!isset($_SESSION['verify_user_id'])) {
            $_SESSION['flash_error'] = "Brak użytkownika do weryfikacji.";
            header("Location: login");
            exit;
        }

        $userId = $_SESSION['verify_user_id'];
        $userModel = new UserModel();
        $user = $userModel->getUserById($userId);

        if (!$user) {
            $_SESSION['flash_error'] = "Użytkownik nie znaleziony.";
            header("Location: login");
            exit;
        }

        if ($user->isVerified()) {
            $_SESSION['flash_error'] = "Konto jest już zweryfikowane.";
            header("Location: login");
            exit;
        }

        $_SESSION['verify_user_email'] = $user->getEmail();

        $tokenService = new TokenService();
        $token = $tokenService->generateToken($userId, 'email_verify');

        $verifyLink = "https://examly.sprzatanieleszno.pl/verify?token=$token";
        $body = "<p>Witaj {$user->getFullName()}, kliknij link aby zweryfikować swój adres e-mail:</p>
                <p><a href='$verifyLink'>$verifyLink</a></p>";

        $mailer = new Mailer();
        if ($mailer->send($user->getEmail(), "Weryfikacja adresu e-mail", $body)) {
            $success = "Wiadomość została wysłana na adres {$user->getEmail()}. Sprawdź skrzynkę odbiorczą.";
            $_SESSION['email_sent'] = time();
        } else {
            $errors = "Wystąpił błąd podczas wysyłania e-maila.";
        }

        if(!empty($errors)) {
            return [$errors];
        } else {
            return [$success];
        }
    }
}
?>