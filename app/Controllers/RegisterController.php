<?php

/**
 * Kontroler Rejestracji i Weryfikacji.
 *
 * Zarządza całym przepływem rejestracji nowego użytkownika, od wyświetlenia
 * formularza, przez jego walidację, aż po proces weryfikacji adresu e-mail.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class RegisterController extends BaseController
{ 
    /**
     * Serwis uwierzytelniania, używany do logiki rejestracji.
     * @var AuthController
     */
    private AuthController $auth;

    /**
     * Konstruktor, który inicjalizuje serwis AuthController
     * i uruchamia logikę nadrzędnego BaseController.
     */
    public function __construct()
    {
        parent::__construct();
        $this->auth = new AuthController();
    }

    /**
     * Obsługuje stronę rejestracji (/register).
     *
     * Wyświetla formularz rejestracji. Dla żądań POST, przekazuje dane
     * do serwisu AuthController w celu walidacji i rejestracji.
     * W przypadku sukcesu przekierowuje na stronę weryfikacji e-mail.
     * W razie błędów, ponownie renderuje formularz z komunikatami.
     *
     * @return void
     */
    public function handleRequest(): void
    {
        if ($this->isUserLoggedIn) {
            header('Location: /');
            exit;
        }

        $errors = [];
        $formData = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $_POST;
            $result = $this->auth->register($formData);

            if ($result['success']) {
                // Przekieruj i dodaj parametr, aby zainicjować wysyłkę e-maila
                header('Location: /verify_email?send=true');
                exit;
            } else {
                $errors = $result['errors'];
            }
        }
        
        $this->renderView('register', [
            'errors' => $errors,
            'formData' => $formData
        ]);
    }

    /**
     * Wyświetla stronę z informacją o potrzebie weryfikacji e-mail (/verify_email).
     *
     * Strona ta pobiera i wyświetla jednorazowe komunikaty (flash messages)
     * z sesji, informujące np. o statusie wysyłki e-maila.
     *
     * @return void
     */
    public function showVerificationPage(): void
    {
        // Sprawdź, czy użytkownik w sesji jest już zweryfikowany.
        if (isset($_SESSION['verify_user_id'])) {
            $userModel = new UserModel();
            $user = $userModel->getUserById($_SESSION['verify_user_id']);

            // Jeśli użytkownik istnieje i jest zweryfikowany, przenieś go na stronę logowania.
            if ($user && $user->isVerified()) {
                unset($_SESSION['verify_user_id']); // Wyczyść sesję
                unset($_SESSION['email_sent']);

                $_SESSION['flash_message'] = ['type' => 'info', 'text' => 'Twoje konto jest już aktywne. Możesz się zalogować.'];
                header("Location: /login");
                exit;
            }
        }
        
        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        $this->renderView('verify_email', [
            'flashMessage' => $flashMessage
        ]);
    }

    /**
     * Obsługuje akcję wysłania (lub ponownego wysłania) e-maila weryfikacyjnego.
     *
     * Metoda zawiera logikę zabezpieczającą (np. limit czasowy ponownej wysyłki)
     * i po wykonaniu akcji zawsze przekierowuje z powrotem na stronę weryfikacji,
     * ustawiając w sesji odpowiedni komunikat (flash message).
     *
     * @return void
     */
    public function handleSendVerificationEmail(): void
    {
        if (!isset($_SESSION['verify_user_id'])) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Brak użytkownika do weryfikacji. Sesja mogła wygasnąć.'];
            header("Location: /login");
            exit;
        }

        // Zabezpieczenie przed spamem: limit 60 sekund na ponowną wysyłkę.
        if (isset($_SESSION['email_sent']) && time() - $_SESSION['email_sent'] < 60) {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Możesz wysłać kolejny e-mail dopiero po upływie minuty.'];
            header('Location: /verify_email');
            exit;
        }

        $userModel = new UserModel();
        $user = $userModel->getUserById($_SESSION['verify_user_id']);

        // Jeśli użytkownik nie istnieje lub jest już zweryfikowany, zakończ proces.
        if (!$user || $user->isVerified()) {
            unset($_SESSION['verify_user_id']);
            $_SESSION['flash_message'] = ['type' => 'info', 'text' => 'Twoje konto jest już aktywne lub wystąpił błąd. Możesz się zalogować.'];
            header("Location: /login");
            exit;
        }

        // Generowanie tokenu i wysyłka e-maila
        $tokenService = new TokenService();
        $token = $tokenService->generateToken($user->getId(), 'email_verify');
        $verifyLink = "https://examly.sprzatanieleszno.pl/verify?token=$token";
        
        $body = "<p>Witaj {$user->getFullName()},</p>"
              . "<p>Kliknij poniższy link, aby zweryfikować swój adres e-mail:</p>"
              . "<p><a href='$verifyLink'>$verifyLink</a></p>";

        $mailer = new Mailer();
        if ($mailer->send($user->getEmail(), "Weryfikacja adresu e-mail", $body)) {
            $_SESSION['email_sent'] = time();
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Nowa wiadomość weryfikacyjna została wysłana na adres {$user->getEmail()}."];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Wystąpił błąd podczas wysyłania e-maila. Spróbuj ponownie.'];
        }

        // Zawsze przekieruj z powrotem na stronę weryfikacji, aby wyświetlić komunikat.
        header('Location: /verify_email');
        exit;
    }
}