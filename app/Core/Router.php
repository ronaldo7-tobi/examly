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

        // --- NOWA LOGIKA OBSŁUGI API ---
        // Sprawdzamy, czy URI zaczyna się od "api/"
        if (strpos($uri, 'api/') === 0) {
            // Usuwamy "api/" z początku, aby uzyskać nazwę akcji
            $apiAction = substr($uri, 4); // 4 to długość "api/"
            
            // Tworzymy instancję ApiController
            $apiController = new ApiController();

            // Konwertujemy nazwę akcji z URL (np. get-question) na nazwę metody (getQuestion)
            $methodName = lcfirst(str_replace('-', '', ucwords($apiAction, '-')));

            if (method_exists($apiController, $methodName)) {
                // Wywołujemy odpowiednią metodę w ApiController
                $apiController->$methodName();
            } else {
                // Jeśli metoda nie istnieje, zwracamy błąd 404 w formacie JSON
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'API endpoint not found.']);
            }
            // Ważne: kończymy wykonanie skryptu, aby nie przechodzić do starego switcha
            return; 
        }

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
                $testController = new TestController();
                $testController->handleRequest('one_question');
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