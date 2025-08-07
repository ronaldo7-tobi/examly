<?php

/**
 * Klasa Router - centralny punkt aplikacji kierujący ruchem (Front Controller).
 *
 * Odpowiada za analizę przychodzącego adresu URI i mapowanie go na odpowiednią
 * akcję w kontrolerze. Posiada dwa oddzielne mechanizmy routingu:
 * 1. Dynamiczny, oparty na wyrażeniach regularnych, dla ścieżek API (np. /api/question/{id}).
 * 2. Statyczny, oparty na instrukcji switch, dla stron widoków (np. /login).
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */
class Router
{
    /**
     * Tablica przechowująca zarejestrowane, dynamiczne ścieżki API.
     *
     * Każdy element to tablica asocjacyjna o strukturze:
     * `['method' => 'GET', 'path' => '#^regex$#', 'handler' => 'Controller@method']`
     *
     * @var array<int, array<string, string>>
     */
    private array $apiRoutes = [];

    /**
     * Główna, publiczna metoda routera, która przetwarza żądanie.
     *
     * Analizuje URI, usuwa z niego bazową ścieżkę projektu, a następnie
     * deleguje obsługę do odpowiedniej metody w zależności od tego,
     * czy żądanie jest skierowane do API, czy do strony WWW.
     *
     * @return void Metoda nie zwraca wartości; jej efektem jest wywołanie akcji kontrolera, która generuje odpowiedź.
     */
    public function handleRequest(): void
    {
        $basePath = '/examly/public/';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace($basePath, '', $uri);
        $uri = trim($uri, '/');

        // Sprawdzamy, czy URI zaczyna się od 'api/' i delegujemy do odpowiedniej metody.
        if (strpos($uri, 'api/') === 0) {
            $this->handleApiRequest(substr($uri, 4));
            return;
        }

        $this->handleWebRequest($uri);
    }

    /**
     * Rejestruje nową, dynamiczną ścieżkę API i konwertuje ją na wyrażenie regularne.
     *
     * Metoda ta jest sercem dynamicznego routingu. Zamienia proste symbole
     * zastępcze, jak `{examCode}`, na nazwane grupy przechwytujące w wyrażeniu
     * regularnym (np. `(?P<examCode>[a-zA-Z0-9_.-]+)`), co pozwala na
     * łatwe wyodrębnienie parametrów z adresu URI.
     *
     * @param string $method  Metoda HTTP (np. 'GET', 'POST').
     * @param string $path    Ścieżka z opcjonalnymi parametrami (np. 'question/{examCode}').
     * @param string $handler Nazwa kontrolera i metody oddzielona znakiem '@' (np. 'ApiController@getQuestion').
     * @return void
     */
    private function addApiRoute(string $method, string $path, string $handler): void
    {
        // Konwersja ścieżki na wyrażenie regularne (RegEx) z nazwaną grupą przechwytującą.
        $pathRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_.-]+)', $path);
        $this->apiRoutes[] = [
            'method' => $method,
            'path' => '#^' . $pathRegex . '$#',
            'handler' => $handler
        ];
    }

    /**
     * Przetwarza żądania skierowane do API.
     *
     * Najpierw rejestruje wszystkie dostępne endpointy API. Następnie iteruje po nich,
     * szukając pierwszej ścieżki, która pasuje zarówno do metody HTTP, jak i do wzorca
     * URI żądania. Po znalezieniu dopasowania, tworzy instancję kontrolera i wywołuje
     * jego metodę, przekazując jej parametry wyodrębnione z adresu URL.
     *
     * @param string $apiUri Fragment adresu URI następujący po prefiksie 'api/'.
     * @return void
     */
    private function handleApiRequest(string $apiUri): void
    {
        // Centralne miejsce rejestracji wszystkich ścieżek API.
        $this->addApiRoute('GET', 'question/{examCode}', 'ApiController@getQuestion');
        $this->addApiRoute('GET', 'test/personalized/{examCode}', 'ApiController@getPersonalizedTest');
        $this->addApiRoute('GET', 'test/full/{examCode}', 'ApiController@getFullTest');
        $this->addApiRoute('POST', 'check-answer', 'ApiController@checkAnswer');
        $this->addApiRoute('POST', 'save-test-result', 'ApiController@saveTestResult');
        $this->addApiRoute('POST', 'save-progress-bulk', 'ApiController@saveProgressBulk');

        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $apiUri = trim($apiUri, '/');

        foreach ($this->apiRoutes as $route) {
            // Krok 1: Sprawdź, czy metoda HTTP się zgadza.
            // Krok 2: Sprawdź, czy URI pasuje do wzorca RegEx.
            if ($route['method'] === $requestMethod && preg_match($route['path'], $apiUri, $matches)) {
                
                // Dzielimy 'Kontroler@metoda' na dwie części.
                [$controllerName, $methodName] = explode('@', $route['handler']);
                
                $controller = new $controllerName();
                
                // Zbieramy nazwane parametry "złapane" z adresu URL (np. ['examCode' => 'INF.03']).
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Wywołujemy metodę kontrolera, przekazując jej parametry.
                $controller->$methodName($params);
                return; // Kończymy, gdy znajdziemy pasującą ścieżkę.
            }
        }

        // Jeśli żadna ścieżka nie pasowała, zwróć błąd 404 w formacie JSON.
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API endpoint not found.']);
    }

    /**
     * Przetwarza żądania skierowane do statycznych stron WWW.
     *
     * Używa prostej instrukcji `switch` do mapowania dokładnych adresów URI
     * na konkretne akcje w kontrolerach, które są odpowiedzialne za
     * renderowanie widoków HTML.
     *
     * @param string $uri "Czysty" URI, bez ścieżki bazowej i ukośników na krańcach.
     * @return void
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
                // W przyszłości można tu wywołać dedykowany ErrorController
                // (new ErrorController())->show404();
                echo "Strona o adresie '$uri' nie istnieje.";
                break;
        }
    }
}