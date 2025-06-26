<?php 
class LoginController
{
    private AuthController $auth;

    public function __construct()
    {
        $this->auth = new AuthController();
    }

    public function handleRequest(): void
    {
        session_start();

        $errors = [];
        $formData = [];
        $result = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $_POST;
            $result = $this->auth->login($formData);

            if ($result) {
                session_regenerate_id(true);
                $_SESSION['user'] = serialize($this->auth->getLoggedUser());
                header('Location: /examly/public/');
                exit;
            } else {
                $errors[] = "Błąd podczas logowania. Spróbuj ponownie.";
            }
        }

        include __DIR__ . '/../../views/login.php';
    }
}

?>