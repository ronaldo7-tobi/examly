<?php

namespace App\Controllers;

use App\Models\Question;
use App\Models\Answer;
use App\Models\UserProgress;
use App\Models\Attempt;

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
class ApiController extends BaseController
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
   * @var Attempt Model do zarządzania historią i wynikami egzaminów.
   */
  private Attempt $attemptModel;

  /**
   * Konstruktor klasy ApiController.
   *
   * Inicjalizuje wszystkie niezbędne obiekty modeli, które stanowią warstwę
   * dostępu do danych. Każdy model jest odpowiedzialny za operacje na
   * konkretnej części bazy danych (pytania, odpowiedzi, postępy, egzaminy).
   */
  public function __construct()
  {
    parent::__construct();
    $this->questionModel = new Question();
    $this->answerModel = new Answer();
    $this->userProgressModel = new UserProgress();
    $this->attemptModel = new Attempt();
  }

  /**
   * Endpoint: Pobiera pojedyncze pytanie.
   */
  public function getQuestion(array $params): void
  {
    $this->ensureGetRequest();
    $result = $this->fetchFilteredQuestions($params, 1);

    if (!$result['success']) return;

    $question = $result['questions'][0] ?? null;

    if (!$question) {
      $this->sendJsonResponse([
        'success' => true,
        'status' => 'no_questions_left',
        'message' => 'Gratulacje! Brak dostępnych pytań.',
      ]);
      return;
    }

    $answers = $this->answerModel->getAnswersByVersionId($question['question_version_id']);
    $this->sendJsonResponse(['success' => true, 'question' => $question, 'answers' => $answers]);
  }

  /**
   * Endpoint: Pobiera spersonalizowany test.
   */
  public function getPersonalizedTest(array $params): void
  {
    $this->ensureGetRequest();

    $limit = isset($_GET['question_count']) ? (int) $_GET['question_count'] : 20;
    $limit = max(10, min(40, $limit));

    $result = $this->fetchFilteredQuestions($params, $limit);

    if (!$result['success']) {
      return;
    }

    $questions = $result['questions'];

    if (count($questions) < $limit) {
      $this->sendJsonResponse([
        'success' => true,
        'status' => 'no_questions_left',
        'message' => 'Brak wystarczającej liczby pytań.',
      ]);
      return;
    }

    $questionsData = [];
    foreach ($questions as $question) {
      $answers = $this->answerModel->getAnswersByVersionId($question['question_version_id']);
      $questionsData[] = ['question' => $question, 'answers' => $answers];
    }

    $this->sendJsonResponse(['success' => true, 'questions' => $questionsData]);
  }

  /**
   * Endpoint: Pobiera pełny arkusz egzaminacyjny.
   */
  public function getFullTest(array $params): void
  {
    $this->ensureGetRequest();

    $examCode = $params['examCode'] ?? null;
    $examTypeId = $this->questionModel->getExamTypeIdByCode($examCode);

    if (!$examTypeId) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Egzamin nie istnieje.'], 404);
      return;
    }

    $allTopicIds = $this->questionModel->getTopicIdsByExamType($examTypeId);
    $questions = $this->questionModel->getQuestions($allTopicIds, 40, $examTypeId);

    $questionsData = [];
    foreach ($questions as $question) {
      $answers = $this->answerModel->getAnswersByVersionId($question['question_version_id']);
      $questionsData[] = ['question' => $question, 'answers' => $answers];
    }

    $this->sendJsonResponse(['success' => true, 'questions' => $questionsData]);
  }

  /**
   * Endpoint: Sprawdza poprawność odpowiedzi i zapisuje progres.
   */
  public function checkAnswer(): void
  {
    $this->ensurePostRequest();

    $qvId = (int) ($_POST['question_version_id'] ?? 0);
    $userAnswerId = (int) ($_POST['answer_id'] ?? 0);

    if (!$qvId || !$userAnswerId) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Brak wymaganych parametrów.'], 400);
      return;
    }

    $correctAnswer = $this->answerModel->getCorrectAnswerForVersion($qvId);
    if (!$correctAnswer) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Błąd wersji pytania.'], 404);
      return;
    }

    $isCorrect = $userAnswerId === (int) $correctAnswer['id'];

    // Wykorzystujemy BaseController do zapisu progresu
    if ($this->isUserLoggedIn) {
      $questionId = $this->questionModel->getQuestionIdByVersionId($qvId);
      if ($questionId) {
        $this->userProgressModel->saveProgress($this->currentUser->getId(), $questionId, (int)$isCorrect);
      }
    }

    $this->sendJsonResponse([
      'success' => true,
      'is_correct' => $isCorrect,
      'correct_answer_id' => (int) $correctAnswer['id'],
    ]);
  }

  public function saveProgressBulk(): void
  {
    $this->ensurePostRequest();

    if (!$this->isUserLoggedIn) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Wymagane uwierzytelnienie'], 403);
      return;
    }

    $progressData = json_decode(file_get_contents('php://input'), true);
    if (!is_array($progressData)) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Nieprawidłowe dane.'], 400);
      return;
    }

    $userId = $this->currentUser->getId();

    foreach ($progressData as $progressItem) {
      if (isset($progressItem['questionId'], $progressItem['isCorrect'])) {
        $this->userProgressModel->saveProgress($userId, (int)$progressItem['questionId'], $progressItem['isCorrect'] ? 1 : 0);
      }
    }

    $this->sendJsonResponse(['success' => true, 'message' => 'Postęp został zapisany.']);
  }

  /**
   * Endpoint: Zapisuje wynik całego podejścia.
   */
  public function saveAttempt(): void
  {
    $this->ensurePostRequest();

    if (!$this->isUserLoggedIn) {
      $this->sendJsonResponse(['error' => 'Wymagane uwierzytelnienie'], 403);
      return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $this->currentUser->getId();

    $examTypeId = $this->questionModel->getExamTypeIdByCode($input['examCode'] ?? 'INF.03');
    if (!$examTypeId) {
      $this->sendJsonResponse(['error' => 'Invalid exam type'], 400);
      return;
    }

    $attemptData = [
      'user_id'          => $userId,
      'exam_type_id'     => $examTypeId,
      'test_type'        => ($input['isFullExam'] ?? false) ? 'full_exam' : 'personalized',
      'correct_count'    => (int)($input['correctAnswers'] ?? 0),
      'total_questions'  => (int)($input['totalQuestions'] ?? 0),
      'duration_seconds' => (int)($input['duration'] ?? 0)
    ];

    $answersDetails = $input['answers'] ?? [];
    $attemptId = $this->attemptModel->createAttempt($attemptData, $input['topicIds'] ?? [], $answersDetails);

    if ($attemptId) {
      $this->userProgressModel->updateBatchProgress($userId, $answersDetails);
      $this->sendJsonResponse(['success' => true, 'attempt_id' => $attemptId]);
    } else {
      $this->sendJsonResponse(['error' => 'Failed to save attempt'], 500);
    }
  }

  // ========================================================================
  // METODY POMOCNICZE (PRIVATE)
  // ========================================================================

  /**
   * Centralna metoda do pobierania pytań (Zoptymalizowana pod BaseController).
   */
  private function fetchFilteredQuestions(array $params, int $limit): array
  {
    $examCode = $params['examCode'] ?? null;
    $subjectIds = $_GET['subject'] ?? [];

    if (!$examCode || empty($subjectIds)) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Brak parametrów filtrowania.'], 400);
      return ['success' => false, 'questions' => []];
    }

    $examTypeId = $this->questionModel->getExamTypeIdByCode($examCode);
    $subjectIds = array_map('intval', $subjectIds);
    $specialFilter = $_GET['premium_option'] ?? null;

    // Wykorzystujemy właściwości z BaseController
    if ($specialFilter && !$this->isUserLoggedIn) {
      $this->sendJsonResponse(['success' => false, 'message' => 'Opcje premium są dla zalogowanych.'], 403);
      return ['success' => false, 'questions' => []];
    }

    $userId = $this->isUserLoggedIn ? $this->currentUser->getId() : null;

    $questions = [];
    if ($specialFilter && $this->isUserLoggedIn) {
      $questions = match ($specialFilter) {
        'toDiscover'   => $this->questionModel->getUndiscoveredQuestions($userId, $subjectIds, $limit, $examTypeId),
        'toImprove'    => $this->questionModel->getLowerAccuracyQuestions($userId, $subjectIds, $limit, $examTypeId),
        'toRemind'     => $this->questionModel->getQuestionsRepeatedAtTheLatest($userId, $subjectIds, $limit, $examTypeId),
        'lastMistakes' => $this->questionModel->getLastMistakes($userId, $subjectIds, $limit, $examTypeId),
        default        => $this->questionModel->getQuestions($subjectIds, $limit, $examTypeId),
      };
    } else {
      $questions = $this->questionModel->getQuestions($subjectIds, $limit, $examTypeId);
    }

    return ['success' => true, 'questions' => $questions];
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
    exit;
  }
}
