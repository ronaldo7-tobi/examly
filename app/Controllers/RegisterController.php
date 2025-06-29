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
                header('Location: login');
                exit;
            } else {
                $errors = $result['errors'];
            }
        }
        include __DIR__ . '/../../views/register.php';
    }
}
?>