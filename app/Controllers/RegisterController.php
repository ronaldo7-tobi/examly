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

        if (isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $_POST;
            $result = $this->auth->register($formData);

            if ($result['success']) {
                header('Location: verify_email?resend=true');
                exit;
            } else {
                $errors = $result['errors'];
            }
        }
        include __DIR__ . '/../../views/register.php';
    }

    /**
     * Wyświetla widok weryfikacji e-mail.
     * 
     * @param array $messages Tablica zawierająca wiadmości z metody sendVerificationEmail().
     * 
     * @return void
     */
    public function showVerificationPage(array $messages = []): void
    {
        if (isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../../views/verify_email.php';
    }

    /**
     * Wysyła wiadomość e-mail z linkiem weryfikacyjnym do zweryfikowania konta użytkownika i zapisuje błędy
     * i komunikaty w otrzymane trakcie procesu działania.
     * 
     * @return array Zwraca tablicę z komunikatem uzyskanym podczas działania metody.
     */
    public function sendVerificationEmail(): array
    {
        if (!isset($_SESSION['verify_user_id'])) {
            $_SESSION['flash_error'] = "Brak użytkownika do weryfikacji.";
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['verify_user_id'];
        $userModel = new UserModel();
        $user = $userModel->getUserById($userId);

        if (!$user) {
            $_SESSION['flash_error'] = "Użytkownik nie znaleziony.";
            header("Location: /login");
            exit;
        }

        if ($user->isVerified()) {
            $_SESSION['flash_success'] = "Konto jest już zweryfikowane.";
            header("Location: /login");
            exit;
        }

        // Generowanie i wysyłka tylko co 60s.
        if (
            isset($_GET['resend']) &&
            $_GET['resend'] === 'true' &&
            (!isset($_SESSION['email_sent']) || time() - $_SESSION['email_sent'] >= 60)
        ) {
            $tokenService = new TokenService();
            $token = $tokenService->generateToken($userId, 'email_verify');

            $verifyLink = "https://examly.sprzatanieleszno.pl/verify?token=$token";
            $body  = "<p>Witaj {$user->getFullName()},</p>"
                   . "<p>Kliknij poniższy link, aby zweryfikować swój adres e-mail:</p>"
                   . "<p><a href='$verifyLink'>$verifyLink</a></p>";

            $mailer = new Mailer();
            if ($mailer->send($user->getEmail(), "Weryfikacja adresu e-mail", $body)) {
                $_SESSION['email_sent'] = time();
                return ["Wiadomość została wysłana na adres {$user->getEmail()}. Sprawdź skrzynkę odbiorczą."];
            } else {
                return ["Wystąpił błąd podczas wysyłania e-maila."];
            }
        }

        return ["Nie możesz jeszcze ponownie wysłać e-maila."];
    }
}
?>