<?php

/**
 * Klasa Router - centralny punkt aplikacji kierujący ruchem (wzorzec Front Controller).
 *
 * Router pełni rolę pojedynczego punktu wejścia dla wszystkich żądań HTTP.
 * Jego zadaniem jest analiza adresu URI i zmapowanie go na odpowiednią akcję
 * w kontrolerze. Taka architektura centralizuje logikę routingu, ułatwiając
 * zarządzanie aplikacją i jej rozbudowę.
 *
 * Zastosowano tu hybrydową strategię routingu:
 * 1. Dynamiczny (RegEx): Dla elastycznych ścieżek API (np. /api/question/{id}).
 *    Pozwala na łatwe wyodrębnianie parametrów wprost z adresu URL.
 * 2. Statyczny (Switch): Dla stałych, znanych ścieżek stron (np. /login).
 *    Jest to rozwiązanie ekstremalnie wydajne dla predefiniowanych adresów.
 *
 * @version 1.3.0
 * @author Tobiasz Szerszeń
 */
class Router
{
  /**
   * Tablica przechowująca zarejestrowane, dynamiczne ścieżki API.
   *
   * @var array<int, array<string, string>>
   */
  private array $apiRoutes = [];

  /**
   * Główna metoda routera, która przetwarza całe żądanie HTTP.
   *
   * Logika działania:
   * 1. Pobiera i "czyści" URI żądania, usuwając z niego bazową ścieżkę projektu.
   * 2. Sprawdza, czy URI wskazuje na zasób API (rozpoczyna się od `api/`).
   * 3. Na podstawie wyniku, deleguje dalsze przetwarzanie do wyspecjalizowanej
   *    metody: `handleApiRequest()` lub `handleWebRequest()`.
   *
   * @return void
   */
  public function handleRequest(): void
  {
    // Krok 1: Przygotowanie "czystego" URI do analizy.
    $basePath = '/examly/public/';
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = str_replace($basePath, '', $uri);
    $uri = trim($uri, '/');

    // Krok 2: Decyzja o strategii routingu na podstawie prefiksu URI.
    if (strpos($uri, 'api/') === 0) {
      // Żądanie do API: usuń prefiks 'api/' i przekaż do handlera API.
      $this->handleApiRequest(substr($uri, 4));
    } else {
      // Żądanie do strony WWW: przekaż pełny, czysty URI do handlera stron.
      $this->handleWebRequest($uri);
    }
  }

  /**
   * Rejestruje i konwertuje ścieżkę API na wyrażenie regularne.
   *
   * To serce dynamicznego routingu. Metoda zamienia przyjazne dla programisty
   * ścieżki z symbolami zastępczymi na potężne wyrażenia regularne z nazwanymi
   * grupami przechwytującymi.
   *
   * Przykład transformacji:
   * - Wejście: `question/{examCode}`
   * - Wyjście: `#^question/(?P<examCode>[a-zA-Z0-9_.-]+)$#`
   *
   * @param string $method Metoda HTTP ('GET', 'POST', etc.).
   * @param string $path Ścieżka z parametrami (np. 'question/{examCode}').
   * @param string $handler Kontroler i metoda ('Controller@method').
   * 
   * @return void
   */
  private function addApiRoute(string $method, string $path, string $handler): void
  {
    $pathRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_.-]+)', $path);
    $this->apiRoutes[] = [
      'method' => $method,
      'path' => '#^' . $pathRegex . '$#',
      'handler' => $handler,
    ];
  }

  /**
   * Przetwarza żądania skierowane do dynamicznego API.
   *
   * @param string $apiUri Fragment adresu URI po prefiksie 'api/'.
   * 
   * @return void
   */
  private function handleApiRequest(string $apiUri): void
  {
    // Krok 1: Centralna rejestracja wszystkich dostępnych endpointów API.
    $this->addApiRoute('GET', 'question/{examCode}', 'ApiController@getQuestion');
    $this->addApiRoute('GET', 'test/personalized/{examCode}', 'ApiController@getPersonalizedTest');
    $this->addApiRoute('GET', 'test/full/{examCode}', 'ApiController@getFullTest');
    $this->addApiRoute('POST', 'check-answer', 'ApiController@checkAnswer');
    $this->addApiRoute('POST', 'save-test-result', 'ApiController@saveTestResult');
    $this->addApiRoute('POST', 'save-progress-bulk', 'ApiController@saveProgressBulk');

    // Krok 2: Przygotowanie danych do dopasowania.
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $apiUri = trim($apiUri, '/');

    // Krok 3: Iteracja przez zarejestrowane ścieżki w poszukiwaniu dopasowania.
    foreach ($this->apiRoutes as $route) {
      // Warunek A: Metoda HTTP musi się zgadzać.
      // Warunek B: URI musi pasować do wzorca RegEx ścieżki.
      if ($route['method'] === $requestMethod && preg_match($route['path'], $apiUri, $matches)) {
        // Krok 4: Dopasowanie znalezione - przygotowanie do wywołania kontrolera.
        [$controllerName, $methodName] = explode('@', $route['handler']);
        $controller = new $controllerName();

        // Wyodrębnij nazwane parametry z URI (np. ['examCode' => 'INF.03']).
        // To sprytny trick: filtruje tablicę $matches, zostawiając tylko
        // te elementy, których klucze są stringami (czyli nasze nazwane grupy).
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        // Krok 5: Wywołaj metodę docelowego kontrolera, przekazując parametry.
        $controller->$methodName($params);
        return; // Zakończ, aby nie przetwarzać dalszych ścieżek.
      }
    }

    // Krok 6: Jeśli pętla zakończyła się bez dopasowania, zwróć błąd 404.
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'API endpoint not found.']);
  }

  /**
   * Przetwarza żądania skierowane do statycznych stron WWW.
   *
   * Używa prostej i ekstremalnie wydajnej instrukcji `switch` do mapowania
   * URI na konkretne akcje w kontrolerach. Jest to idealne rozwiązanie dla
   * z góry znanej, skończonej liczby stron w aplikacji.
   *
   * @param string $uri "Czysty" URI do zmapowania.
   * 
   * @return void
   */
  private function handleWebRequest(string $uri): void
  {
    switch ($uri) {
      case '':
        (new HomeController())->show();
        break;
      // --- Grupa Uwierzytelniania ---
      case 'logowanie':
        (new LoginController())->handleRequest();
        break;
      case 'rejestracja':
        (new RegisterController())->handleRequest();
        break;
      case 'autoryzacja-email':
        $controller = new RegisterController();
        // Sub-routing na podstawie parametru GET dla tej samej ścieżki.
        if (isset($_GET['send']) && $_GET['send'] === 'true') {
          $controller->handleSendVerificationEmail();
        } else {
          $controller->showVerificationPage();
        }
        break;
      case 'weryfikacja':
        (new VerificationController())->handle();
        break;
      case 'wyloguj':
        (new UserController())->logout();
        break;
      // --- Grupa Quizów ---
      case 'inf03-jedno-pytanie':
        (new QuizPageController())->showOneQuestionPage();
        break;
      case 'inf03-personalizowany-test':
        (new QuizPageController())->showPersonalizedTestPage();
        break;
      case 'inf03-test':
        (new QuizPageController())->showTestPage();
        break;
      case 'kursy':
        (new QuizPageController)->showCoursesPage();
        break;
      case 'kurs-inf03':
        (new QuizPageController())->showCoursePage();
        break;
      // --- Grupa Użytkownika ---
      case 'statystyki':
        (new UserController())->showStatistics();
        break;
      // --- Domyślna obsługa błędu ---
      default:
        http_response_code(404);
        // W przyszłości można tu wywołać dedykowany ErrorController.
        // (new ErrorController())->show404();
        echo "Strona o adresie '$uri' nie istnieje.";
        break;
    }
  }
}
