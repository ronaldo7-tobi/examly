<?php 
/**
 * Klasa odpowiadająca za logikę wyświetlania widoku logowania.
 */
class LoginController
{
    /**
     * Instancja klasy AuthController, która zarządza rejestrowaniem i logowaniem się użytkowników.
     *
     * @var AuthController
     */
    private AuthController $auth;

    // Konstruktor, incijalizuje instancję AuthController.
    public function __construct()
    {
        $this->auth = new AuthController();
    }

    /**
     * Zarządza widokiem logowania.
     * 
     * @return void
     */
    public function handleRequest(): void
    {
        $errors = [];
        $formData = [];
        $result = false;

        if (isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $_POST;
            $result = $this->auth->login($formData);

            if ($result === true) {
                session_regenerate_id(true);
                $_SESSION['user'] = $this->auth->getLoggedUser();
                unset($_SESSION['verify_user_id']);
                unset($_SESSION['verify_user_email']);
                unset($_SESSION['flash_error']);
                unset($_SESSION['flash_success']);
                header('Location: /examly/public/');
                exit;
            } else {
                $errors = $result['errors'];
            }
        }

        include __DIR__ . '/../../views/login.php';
    }
}

?>