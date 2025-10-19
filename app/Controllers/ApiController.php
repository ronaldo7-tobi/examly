<?php

namespace App\Controllers;

use App\Models\Question;
use App\Models\Answer;
use App\Models\UserProgress;
use App\Models\Exam;

/**
 * Główny kontroler API do obsługi logiki quizu.
 *
 * Odpowiada za obsługę żądań HTTP przychodzących z aplikacji klienckiej (frontend),
 * komunikację z modelami bazy danych oraz zwracanie danych w standardowym formacie JSON.
 * Jest to centralny punkt logiki biznesowej aplikacji po stronie serwera, zarządzający
 * pobieraniem pytań, weryfikacją odpowiedzi i zapisywaniem postępów użytkownika.
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */
class ApiController
{
  /**
   * @var Question Model do operacji na tabeli pytań.
   */
  private Question $questionModel;

  /**
   * @var Answer Model do operacji na tabeli odpowiedzi.
   */
  private Answer $answerModel;

  /**
   * @var UserProgress Model do śledzenia i zapisywania postępów użytkownika.
   */
  private UserProgress $userProgressModel;

  /**
   * @var Exam Model do zarządzania historią i wynikami egzaminów.
   */
  private Exam $examModel;

  /**
   * Konstruktor klasy ApiController.
   *
   * Inicjalizuje wszystkie niezbędne obiekty modeli, które stanowią warstwę
   * dostępu do danych. Każdy model jest odpowiedzialny za operacje na
   * konkretnej części bazy danych (pytania, odpowiedzi, postępy, egzaminy).
   */
  public function __construct()
  {
    $this->questionModel = new Question();
    $this->answerModel = new Answer();
    $this->userProgressModel = new UserProgress();
    $this->examModel = new Exam();
  }

  /**
   * Endpoint: Pobiera pojedyncze pytanie dla określonego egzaminu i kategorii.
   *
   * Logika działania:
   * 1. Wywołuje prywatną metodę `fetchFilteredQuestions`, przekazując limit ustawiony na 1.
   * 2. Jeśli metoda zwróci błąd (np. walidacji), odpowiedź jest natychmiast przerywana.
   * 3. Jeśli nie znaleziono pytań, zwraca specjalny status `no_questions_left`.
   * 4. Jeśli znaleziono pytanie, pobiera do niego odpowiedzi.
   * 5. Wysyła kompletną odpowiedź JSON zawierającą obiekt pytania i tablicę odpowiedzi.
   *
   * @api
   * @method GET
   * @path /api/question/{examCode}
   *
   * @param array<string, string> $params Parametry z URL, np. ['examCode' => 'INF.03'].
   * @global array<int> $_GET['subject'] Tablica numerycznych ID wybranych kategorii. Wymagana.
   * @global string|null $_GET['premium_option'] Opcjonalny filtr premium.
   *
   * @return void
   */
  public function getQuestion(array $params): void
  {
    $this->ensureGetRequest();

    // Krok 1: Pobranie pytania za pomocą centralnej logiki.
    $result = $this->fetchFilteredQuestions($params, 1);

    // Krok 2: Obsługa błędów lub braku pytań (obsłużone wewnątrz fetchFilteredQuestions).
    if (!$result['success']) {
      // Jeśli wystąpił błąd (np. walidacji), metoda fetch już wysłała odpowiedź.
      return;
    }

    $question = $result['questions'][0] ?? null;

    // Krok 3: Obsługa przypadku, gdy nie znaleziono żadnych pytań.
    if (!$question) {
      $this->sendJsonResponse([
        'success' => true,
        'status' => 'no_questions_left',
        'message' => 'Gratulacje! Brak dostępnych pytań dla wybranych kryteriów.',
      ]);
      return;
    }

    // Krok 4: Pobranie odpowiedzi i wysłanie kompletnych danych.
    $answers = $this->answerModel->getAnswersToQuestion($question['id']);
    $this->sendJsonResponse(['success' => true, 'question' => $question, 'answers' => $answers]);
  }

  /**
   * Endpoint: Pobiera spersonalizowany test o zdefiniowanej liczbie pytań.
   *
   * Logika działania:
   * 1. Waliduje i ustala liczbę pytań do pobrania (limit).
   * 2. Wywołuje prywatną metodę `fetchFilteredQuestions`, przekazując ustalony limit.
   * 3. Jeśli metoda zwróci błąd, odpowiedź jest przerywana.
   * 4. Jeśli zwrócono mniej pytań niż oczekiwano, informuje o braku dostępnych pytań.
   * 5. Dla każdego pobranego pytania dociąga jego odpowiedzi.
   * 6. Wysyła odpowiedź JSON zawierającą tablicę kompletnych obiektów (pytanie + odpowiedzi).
   *
   * @api
   * @method GET
   * @path /api/test/personalized/{examCode}
   *
   * @param array<string, string> $params Parametry z URL.
   * @global int|null $_GET['question_count'] Opcjonalna liczba pytań w teście.
   *
   * @return void
   */
  public function getPersonalizedTest(array $params): void
  {
    $this->ensureGetRequest();

    // Krok 1: Walidacja i ustalenie limitu pytań.
    $limit = isset($_GET['question_count']) ? (int) $_GET['question_count'] : 20;
    // Zabezpieczenie, aby nikt nie próbował pobrać np. 1000 pytań na raz.
    if ($limit < 10) {
      $limit = 10;
    }
    if ($limit > 40) {
      $limit = 40;
    }

    // Krok 2: Pobranie pytań za pomocą centralnej logiki.
    $result = $this->fetchFilteredQuestions($params, $limit);

    // Krok 3: Obsługa błędów.
    if (!$result['success']) {
      return;
    }

    $questions = $result['questions'];

    // Krok 4: Sprawdzenie, czy znaleziono wystarczającą liczbę pytań.
    if (count($questions) < $limit) {
      $this->sendJsonResponse([
        'success' => true,
        'status' => 'no_questions_left',
        'message' => 'Gratulacje! Brak dostępnych pytań dla wybranych kryteriów.',
      ]);
      return;
    }

    // Krok 5: Przygotowanie finalnej struktury danych z odpowiedziami.
    $questionsData = [];
    foreach ($questions as $question) {
      $answers = $this->answerModel->getAnswersToQuestion($question['id']);
      $questionsData[] = ['question' => $question, 'answers' => $answers];
    }

    $this->sendJsonResponse(['success' => true, 'questions' => $questionsData]);
  }

  /**
   * Endpoint: Pobiera pełny arkusz egzaminacyjny (40 pytań).
   *
   * Symuluje prawdziwy egzamin poprzez losowanie 40 pytań z całej puli
   * dostępnej dla danego egzaminu, bez względu na kategorie wybrane przez użytkownika.
   *
   * @api
   * @method GET
   * @path /api/test/full/{examCode}
   *
   * @param array<string, string> $params Parametry z URL, np. ['examCode' => 'INF.03'].
   * 
   * @return void
   */
  public function getFullTest(array $params): void
  {
    $this->ensureGetRequest();

    // Krok 1: Walidacja i pobranie kodu egzaminu.
    $examCode = $params['examCode'] ?? null;
    if (!$examCode) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Nie podano kodu egzaminu.'], 400);
      return;
    }

    // Krok 2: Konwersja kodu egzaminu na jego ID.
    $examTypeId = $this->questionModel->getExamTypeIdByCode($examCode);
    if (!$examTypeId) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Egzamin o podanym kodzie nie istnieje.'], 404);
      return;
    }

    // Krok 3: Pobranie wszystkich ID tematów powiązanych z danym egzaminem.
    $allTopicIds = $this->questionModel->getTopicIdsByExamType($examTypeId);
    if (empty($allTopicIds)) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Dla tego egzaminu nie zdefiniowano tematów.'], 404);
      return;
    }

    // Krok 4: Pobranie 40 losowych pytań z puli tematów.
    $questions = $this->questionModel->getQuestions($allTopicIds, 40, $examTypeId);

    // Krok 5: Przygotowanie finalnej struktury danych z odpowiedziami.
    $questionsData = [];
    foreach ($questions as $question) {
      $answers = $this->answerModel->getAnswersToQuestion($question['id']);
      $questionsData[] = ['question' => $question, 'answers' => $answers];
    }

    $this->sendJsonResponse(['success' => true, 'questions' => $questionsData]);
  }

  /**
   * Endpoint: Sprawdza poprawność odpowiedzi i zapisuje postęp.
   *
   * Logika działania:
   * 1. Waliduje, czy przesłano ID pytania i odpowiedzi.
   * 2. Pobiera z bazy poprawną odpowiedź dla danego pytania.
   * 3. Porównuje ID odpowiedzi użytkownika z poprawnym ID.
   * 4. Jeśli użytkownik jest zalogowany, zapisuje wynik (1 lub 0) w jego historii postępów.
   * 5. Zwraca odpowiedź JSON z informacją o poprawności i ID poprawnej odpowiedzi.
   *
   * @api
   * @method POST
   * @path /api/check-answer
   *
   * @global int $_POST['question_id'] ID pytania, na które udzielono odpowiedzi.
   * @global int $_POST['answer_id'] ID odpowiedzi wybranej przez użytkownika.
   *
   * @return void
   */
  public function checkAnswer(): void
  {
    $this->ensurePostRequest();

    // Krok 1: Walidacja, czy przesłano wymagane identyfikatory.
    if (!isset($_POST['question_id'], $_POST['answer_id'])) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Brak ID pytania lub odpowiedzi.'], 400);
      return;
    }

    $questionId = (int) $_POST['question_id'];
    $userAnswerId = (int) $_POST['answer_id'];

    // Krok 2: Pobranie poprawnej odpowiedzi z bazy danych.
    $correctAnswer = $this->answerModel->getCorrectAnswerForQuestion($questionId);
    if (!$correctAnswer) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Nie znaleziono poprawnej odpowiedzi.'], 404);
      return;
    }

    $isCorrect = $userAnswerId === (int) $correctAnswer['id'];

    // Krok 3: Jeśli użytkownik jest zalogowany, zapisz jego postęp.
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user'])) {
      $this->userProgressModel->saveProgressForQuestion($_SESSION['user']->getId(), $questionId, $isCorrect);
    }

    // Krok 4: Zwróć wynik weryfikacji.
    $this->sendJsonResponse([
      'success' => true,
      'is_correct' => $isCorrect,
      'correct_answer_id' => (int) $correctAnswer['id'],
    ]);
  }

  /**
   * Endpoint: Masowo zapisuje postęp z testu (dla zalogowanych użytkowników).
   *
   * Odbiera tablicę obiektów z ID pytania i informacją o poprawności odpowiedzi,
   * a następnie iteruje przez nią, zapisując każdy wpis w historii postępów użytkownika.
   * Jeśli użytkownik nie jest zalogowany, endpoint kończy działanie cicho, bez błędu,
   * aby nie przerywać działania aplikacji dla gości.
   *
   * @api
   * @method POST
   * @path /api/save-progress-bulk
   *
   * @uses file_get_contents('php://input') do odczytu danych JSON.
   * Oczekiwana struktura: [{"questionId": int, "isCorrect": bool}, ...]
   *
   * @return void
   */
  public function saveProgressBulk(): void
  {
    $this->ensurePostRequest();

    $userId = $this->requireApiAuth();

    // Krok 2: Odczyt i walidacja danych wejściowych.
    $progressData = json_decode(file_get_contents('php://input'), true);
    if (!is_array($progressData)) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Nieprawidłowe dane.'], 400);
      return;
    }

    $userId = $_SESSION['user']->getId();

    // Krok 3: Zapis postępu dla każdego pytania w pętli.
    foreach ($progressData as $progressItem) {
      if (isset($progressItem['questionId'], $progressItem['isCorrect'])) {
        $questionId = (int) $progressItem['questionId'];
        $result = $progressItem['isCorrect'] ? 1 : 0;
        $this->userProgressModel->saveProgressForQuestion($userId, $questionId, $result);
      }
    }

    $this->sendJsonResponse(['success' => true, 'message' => 'Postęp został zapisany.']);
  }

  /**
   * Endpoint: Zapisuje wynik ukończonego testu (dla zalogowanych użytkowników).
   *
   * Odbiera kompletne podsumowanie testu (wynik procentowy, czasy, itp.)
   * i zapisuje je w tabeli egzaminów w ramach jednej transakcji bazodanowej.
   *
   * @api
   * @method POST
   * @path /api/save-test-result
   *
   * @uses file_get_contents('php://input') do odczytu danych JSON.
   * Oczekiwana struktura: {
   * "score_percent": float, "correct_answers": int, "total_questions": int,
   * "duration_seconds": int, "topic_ids": array<int>, "is_full_exam": bool
   * }
   *
   * @return void
   */
  public function saveTestResult(): void
  {
    $this->ensurePostRequest();

    $userId = $this->requireApiAuth();

    // Krok 2: Odczyt i walidacja danych wejściowych JSON.
    $data = json_decode(file_get_contents('php://input'), true);
    $requiredKeys = ['score_percent', 'correct_answers', 'total_questions', 'duration_seconds', 'topic_ids'];
    foreach ($requiredKeys as $key) {
      if (!isset($data[$key])) {
        $this->sendJsonResponse(['success' => false, 'message' => 'Niekompletne dane do zapisu.'], 400);
        return;
      }
    }

    // Krok 3: Przygotowanie danych do zapisu.
    $examData = [
      'user_id' => $_SESSION['user']->getId(),
      'is_full_exam' => !empty($data['is_full_exam']) ? 1 : 0,
      'correct_answers' => (int) $data['correct_answers'],
      'total_questions' => (int) $data['total_questions'],
      'score_percent' => (float) $data['score_percent'],
      'duration_seconds' => (int) $data['duration_seconds'],
    ];
    $topicIds = (array) $data['topic_ids'];

    // Krok 4: Zapis wyniku egzaminu w transakcji.
    $success = $this->examModel->saveExamWithTopics($examData, $topicIds);

    // Krok 5: Wysłanie odpowiedzi w zależności od wyniku operacji.
    if ($success) {
      $this->sendJsonResponse(['success' => true, 'message' => 'Wynik testu zapisany.']);
    } else {
      $this->sendJsonResponse(['success' => false, 'message' => 'Błąd podczas zapisu wyniku.'], 500);
    }
  }

  // ========================================================================
  // METODY POMOCNICZE (PRIVATE)
  // ========================================================================

  /**
   * Centralna metoda do pobierania pytań na podstawie filtrów.
   *
   * Ta metoda jest sercem logiki pobierania pytań. Abstrakcjonuje powtarzalne
   * kroki walidacji (kod egzaminu, tematy, uprawnienia premium) i deleguje
   * właściwe zapytanie do modelu na podstawie podanych kryteriów.
   * Zwraca tablicę z wynikiem operacji lub wysyła odpowiedź błędu i kończy skrypt.
   *
   * @param array<string, string> $params Parametry z URL (oczekiwany 'examCode').
   * @param int $limit Liczba pytań do pobrania.
   *
   * @return array<string, mixed> Tablica z kluczem 'success' (bool) i 'questions' (array).
   * W przypadku błędu walidacji, metoda sama wyśle odpowiedź JSON i funkcja
   * nadrzędna powinna zakończyć działanie.
   */
  private function fetchFilteredQuestions(array $params, int $limit): array
  {
    // Krok 1: Walidacja kodu egzaminu.
    $examCode = $params['examCode'] ?? null;
    if (!$examCode) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Nie podano kodu egzaminu.'], 400);
      return ['success' => false, 'questions' => []];
    }

    // Krok 2: Walidacja ID tematów.
    $subjectIds = $_GET['subject'] ?? [];
    if (empty($subjectIds)) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Wybierz przynajmniej jedną kategorię.'], 400);
      return ['success' => false, 'questions' => []];
    }

    // Krok 3: Konwersja kodu egzaminu na ID.
    $examTypeId = $this->questionModel->getExamTypeIdByCode($examCode);
    if (!$examTypeId) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Egzamin o podanym kodzie nie istnieje.'], 404);
      return ['success' => false, 'questions' => []];
    }

    // Krok 4: Przygotowanie filtrów i danych użytkownika.
    $subjectIds = array_map('intval', $subjectIds);
    $specialFilter = $_GET['premium_option'] ?? null;
    $isUserLoggedIn = isset($_SESSION['user']);
    $userId = $isUserLoggedIn ? $_SESSION['user']->getId() : null;

    // Krok 5: Weryfikacja uprawnień do filtrów premium.
    if ($specialFilter && !$isUserLoggedIn) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Opcje premium są dla zalogowanych.'], 403);
      return ['success' => false, 'questions' => []];
    }

    // Krok 6: Pobranie pytań z modelu z uwzględnieniem filtrów.
    $questions = [];
    if ($specialFilter && $isUserLoggedIn) {
      switch ($specialFilter) {
        case 'toDiscover':
          $questions = $this->questionModel->getUndiscoveredQuestions($userId, $subjectIds, $limit, $examTypeId);
          break;
        case 'toImprove':
          $questions = $this->questionModel->getLowerAccuracyQuestions($userId, $subjectIds, $limit, $examTypeId);
          break;
        case 'toRemind':
          $questions = $this->questionModel->getQuestionsRepeatedAtTheLatest($userId, $subjectIds, $limit, $examTypeId);
          break;
        case 'lastMistakes':
          $questions = $this->questionModel->getLastMistakes($userId, $subjectIds, $limit, $examTypeId);
          break;
        default:
          // Fallback na standardowe pobieranie w razie nieznanego filtra
          $questions = $this->questionModel->getQuestions($subjectIds, $limit, $examTypeId);
          break;
      }
    } else {
      // Domyślne pobieranie losowego pytania dla gości lub bez filtra premium.
      $questions = $this->questionModel->getQuestions($subjectIds, $limit, $examTypeId);
    }

    return ['success' => true, 'questions' => $questions];
  }

  /**
   * Weryfikuje, czy użytkownik jest zalogowany na potrzeby endpointu API.
   * Jeśli nie, wysyła odpowiedź JSON z błędem 403 i kończy działanie.
   *
   * @return int ID zalogowanego użytkownika.
   */
  private function requireApiAuth(): int
  {
    if (!isset($_SESSION['user'])) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Dostęp wymaga uwierzytelnienia.'], 403); // 403 Forbidden jest tu bardziej odpowiednie
      exit();
    }
    return $_SESSION['user']->getId();
  }

  /**
   * Weryfikuje, czy żądanie jest typu POST.
   * Jeśli nie, przerywa skrypt i wysyła odpowiedź 405 Method Not Allowed.
   * 
   * @return void
   */
  private function ensurePostRequest(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->sendJsonResponse(['success' => false, 'message' => 'Metoda niedozwolona. Wymagany POST.'], 405);
      exit();
    }
  }

  /**
   * Weryfikuje, czy żądanie jest typu GET.
   * Jeśli nie, przerywa skrypt i wysyła odpowiedź 405 Method Not Allowed.
   * 
   * @return void
   */
  private function ensureGetRequest(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
      $this->sendJsonResponse(['success' => false, 'message' => 'Metoda niedozwolona. Wymagany GET.'], 405);
      exit();
    }
  }

  /**
   * Centralna funkcja do wysyłania odpowiedzi w formacie JSON.
   *
   * Ustawia odpowiednie nagłówki HTTP, konwertuje tablicę PHP na format JSON
   * i wysyła ją do klienta, zapewniając spójność wszystkich odpowiedzi API.
   *
   * @param array<mixed> $data Tablica asocjacyjna z danymi do wysłania.
   * @param int $statusCode Kod statusu HTTP (domyślnie 200 OK).
   * 
   * @return void
   */
  private function sendJsonResponse(array $data, int $statusCode = 200): void
  {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data);
  }
}
