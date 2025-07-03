<?php
/**
 * Klasa Router obsługuje routing aplikacji.
 */
class Router 
{
    /**
     * Obsługuje żądanie użytkownika i ładuje odpowiedni widok.
     *
     * @return void
     */
    public function handleRequest(): void
    {
        $basePath = '/examly/public/';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace($basePath, '', $uri); // usuń prefix
        $uri = trim($uri, '/');

        switch ($uri) {
            case '':
                require_once __DIR__ . '/../../views/home.php';
                break;

            case 'login':
                $logController = new LoginController();
                $logController->handleRequest();
                break;

            case 'register':
                $regController = new RegisterController();
                $regController->handleRequest();
                break;
            
            case 'verify':
                require_once __DIR__ . '/../../public/verify.php';
                break;

            case 'verify_email':
                $regController = new RegisterController();
                if (isset($_GET['resend']) && $_GET['resend'] === 'true') {
                    $messages = $regController->sendVerificationEmail();
                } else {
                    $messages = [];
                }
                $regController->showVerificationPage($messages);
                break;

            case 'logout':
                require_once __DIR__ . '/../../public/logout.php';
                break;
            
            case 'inf03_one_question':
                require_once __DIR__ . '/../../views/inf03_one_question.php';
                break;
            
            case 'inf03_personalized_test':
                require_once __DIR__ . '/../../views/inf03_personalized_test.php';
                break;
            
            case 'inf03_test':
                require_once __DIR__ . '/../../views/inf03_test.php';
                break;

            case 'inf03_course':
                require_once __DIR__ . '/../../views/inf03_course.php';
                break;
            
            case 'statistics':
                require_once __DIR__ . '/../../views/statistics.php';
                break;

            default:
                http_response_code(404);
                echo "Strona nie istnieje.";
                break;
        }
    }
}
?>