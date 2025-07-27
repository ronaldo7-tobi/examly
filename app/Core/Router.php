<?php

/**
 * Klasa Router obsługuje routing aplikacji.
 * Mapuje przychodzące żądania URI na odpowiednie akcje w kontrolerach.
 * 
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class Router 
{
    /**
     * Główna metoda routera, która analizuje URI i wywołuje odpowiedni kontroler.
     * @return void
     */
    public function handleRequest(): void
    {
        $basePath = '/examly/public/';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace($basePath, '', $uri);
        $uri = trim($uri, '/');

        // --- Sekcja API ---
        if (strpos($uri, 'api/') === 0) {
            $this->handleApiRequest(substr($uri, 4));
            return;
        }

        // --- Sekcja Stron WWW ---
        $this->handleWebRequest($uri);
    }

    /**
     * Obsługuje żądania skierowane do API.
     * @param string $apiAction Akcja do wykonania.
     */
    private function handleApiRequest(string $apiAction): void
    {
        $apiController = new ApiController();
        $methodName = lcfirst(str_replace('-', '', ucwords($apiAction, '-')));

        if (method_exists($apiController, $methodName)) {
            $apiController->$methodName();
        } else {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'API endpoint not found.']);
        }
    }

    /**
     * Obsługuje żądania skierowane do stron WWW.
     * @param string $uri Czysty URI bez basePath.
     */
    private function handleWebRequest(string $uri): void
    {
        switch ($uri) {
            case '':
                (new HomeController())->show();
                break;

            case 'login':
                (new LoginController())->handleRequest();
                break;

            case 'register':
                (new RegisterController())->handleRequest();
                break;

            case 'verify_email':
                $controller = new RegisterController();
                if (isset($_GET['send']) && $_GET['send'] === 'true') {
                    $controller->handleSendVerificationEmail();
                } else {
                    $controller->showVerificationPage();
                }
                break;

            case 'logout':
                (new UserController())->logout();
                break;
            
            case 'inf03_one_question':
                (new QuizPageController())->showOneQuestionPage();
                break;
            
            case 'inf03_personalized_test':
                (new QuizPageController())->showPersonalizedTestPage();
                break;
            
            case 'inf03_test':
                (new QuizPageController())->showTestPage();
                break;

            case 'inf03_course':
                (new QuizPageController())->showCoursePage();
                break;
            
            case 'statistics':
                (new UserController())->showStatistics();
                break;

            default:
                http_response_code(404);
                // W przyszłości można stworzyć ErrorController
                // (new ErrorController())->show404();
                echo "Strona o adresie '$uri' nie istnieje.";
                break;
        }
    }
}