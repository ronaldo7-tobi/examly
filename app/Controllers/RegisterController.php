<?php
class RegisterController
{
    private AuthController $auth;

    public function __construct()
    {
        $this->auth = new AuthController();
    }

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
