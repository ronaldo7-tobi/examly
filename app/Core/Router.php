<?php

/**
 * Klasa Router obsługuje routing aplikacji.
 * Mapuje przychodzące żądania URI na odpowiednie akcje w kontrolerach.
 * Wersja 1.1.0 wprowadza obsługę dynamicznych parametrów w ścieżkach API
 * (np. /api/question/{examCode}) oraz routing oparty na metodzie HTTP.
 * * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class Router 
{
    /**
     * Przechowuje zdefiniowane ścieżki API.
     * @var array
     */
    private array $apiRoutes = [];

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
     * Rejestruje nową, dynamiczną ścieżkę API.
     * @param string $method Metoda HTTP (np. 'GET', 'POST').
     * @param string $path Ścieżka z opcjonalnymi parametrami (np. 'question/{examCode}').
     * @param string $handler Nazwa kontrolera i metody oddzielona znakiem '@' (np. 'ApiController@getQuestion').
     */
    private function addApiRoute(string $method, string $path, string $handler): void
    {
        // Konwertuje ścieżkę typu /question/{examCode} na wyrażenie regularne (RegEx),
        // które potrafi "złapać" wartość parametru.
        $pathRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_.-]+)', $path);
        $this->apiRoutes[] = [
            'method' => $method,
            'path' => '#^' . $pathRegex . '$#',
            'handler' => $handler
        ];
    }

    /**
     * Obsługuje żądania skierowane do API, dopasowując je do zdefiniowanych, dynamicznych ścieżek.
     * @param string $apiUri Akcja do wykonania (np. 'question/INF.03').
     */
    private function handleApiRequest(string $apiUri): void
    {
        // Rejestrujemy wszystkie ścieżki API w jednym, centralnym miejscu.
        // Dzięki temu łatwo zarządzać endpointami.
        $this->addApiRoute('GET', 'question/{examCode}', 'ApiController@getQuestion');
        $this->addApiRoute('GET', 'test/full/{examCode}', 'ApiController@getFullTest');
        $this->addApiRoute('POST', 'check-answer', 'ApiController@checkAnswer');
        $this->addApiRoute('POST', 'save-test-result', 'ApiController@saveTestResult');
        // W przyszłości tutaj dodasz nowe ścieżki API.

        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $apiUri = trim($apiUri, '/');

        foreach ($this->apiRoutes as $route) {
            // Sprawdzamy, czy metoda HTTP żądania zgadza się ze zdefiniowaną...
            // ...i czy adres URI pasuje do wzorca wyrażenia regularnego.
            if ($route['method'] === $requestMethod && preg_match($route['path'], $apiUri, $matches)) {
                
                // Dzielimy handler 'Kontroler@metoda' na dwie części.
                [$controllerName, $methodName] = explode('@', $route['handler']);
                
                $controller = new $controllerName();
                
                // Zbieramy parametry "złapane" z adresu URL (np. ['examCode' => 'INF.03']).
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Wywołujemy metodę kontrolera, przekazując jej parametry z URL.
                $controller->$methodName($params);
                return; // Kończymy działanie, gdy znajdziemy pasującą ścieżkę.
            }
        }

        // Jeśli pętla się zakończy i nie znajdzie dopasowania, zwracamy błąd 404.
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API endpoint not found.']);
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

            case 'verify':
                (new VerificationController())->handle();
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