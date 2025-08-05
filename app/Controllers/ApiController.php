<?php

/**
 * Kontroler API do obsługi logiki quizu.
 * * Odpowiada za obsługę żądań przychodzących z frontendu, komunikację
 * z modelami bazy danych oraz zwracanie danych w formacie JSON.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class ApiController {
    
    /**
     * @var Question Model do operacji na pytaniach.
     */
    private $questionModel;
    
    /**
     * @var Answer Model do operacji na odpowiedziach.
     */
    private $answerModel;
    
    /**
     * @var UserProgress Model do śledzenia postępów użytkownika.
     */
    private $userProgressModel;

    private $examModel;

    /**
     * Konstruktor ApiController.
     * * Inicjalizuje wszystkie niezbędne modele do interakcji z bazą danych.
     */
    public function __construct() {
        $this->questionModel = new Question(); 
        $this->answerModel = new Answer();
        $this->userProgressModel = new UserProgress();
        $this->examModel = new Exam();
    }

    /**
     * Endpoint do pobierania pojedynczego pytania dla określonego egzaminu.
     *
     * Metoda jest elastyczna i działa na podstawie kodu egzaminu przekazanego w URL.
     *
     * @api
     * @method GET
     * @path /api/question/{examCode}
     * @param array<string, string> $params Parametry z URL, np. ['examCode' => 'INF.03'].
     * @param array<int> $_GET['subject'] Tablica z numerycznymi ID wybranych kategorii.
     * @param string|null $_GET['premium_option'] Opcjonalny filtr premium.
     * @return void Wysyła odpowiedź JSON.
     */
    public function getQuestion(array $params)
    {
        $this->ensureGetRequest();

        // Krok 1: Pobierz kod egzaminu z URL
        $examCode = $params['examCode'] ?? null;
        if (!$examCode) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie podano kodu egzaminu w adresie URL.'], 400);
            return;
        }

        // Krok 2: Pobierz ID tematów z parametrów GET
        $subjectIds = $_GET['subject'] ?? [];
        if (empty($subjectIds)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Wybierz przynajmniej jedną kategorię materiału.'], 400);
            return;
        }

        // Krok 3: Przekonwertuj kod egzaminu na jego ID
        $examTypeId = $this->questionModel->getExamTypeIdByCode($examCode);
        if (!$examTypeId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Egzamin o podanym kodzie nie istnieje.'], 404);
            return;
        }

        $subjectIds = array_map('intval', $subjectIds);
        $specialFilter = $_GET['premium_option'] ?? null;
        $isUserLoggedIn = session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']);
        $userId = $isUserLoggedIn ? $_SESSION['user']->getId() : null;
        $limit = 1;
        
        if ($specialFilter && !$isUserLoggedIn) {
            $this->sendJsonResponse(['success' => false, 'message' => "Opcje premium są dostępne tylko dla zalogowanych użytkowników."], 403);
            return;
        }

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
                    $questions = $this->questionModel->getQuestions($subjectIds, $limit, $examTypeId);
                    break;
            }
        } else {
            $questions = $this->questionModel->getQuestions($subjectIds, $limit, $examTypeId);
        }

        $question = $questions[0] ?? null;

        if (!$question) {
            $this->sendJsonResponse(['success' => true, 'status' => 'no_questions_left', 'message' => 'Gratulacje! Brak dostępnych pytań dla wybranych kryteriów.']);
            return;
        }

        $answers = $this->answerModel->getAnswersToQuestion($question['id']);
        $this->sendJsonResponse(['success' => true, 'question' => $question, 'answers' => $answers]);
    }

    /**
     * Endpoint do pobierania pełnego testu na podstawie kodu egzaminu.
     *
     * Metoda jest elastyczna i pozwala na pobranie testu dla dowolnego,
     * zdefiniowanego w bazie egzaminu poprzez przekazanie jego kodu w URL.
     *
     * @api
     * @method GET
     * @path /api/test/full/{examCode}
     * @param array<string, string> $params Parametry z URL, np. ['examCode' => 'INF.03'].
     * @return void Wysyła odpowiedź JSON z 40 pytaniami.
     */
    public function getFullTest(array $params)
    {
        $this->ensureGetRequest();
        
        $examCode = $params['examCode'] ?? null;
        if (!$examCode) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie podano kodu egzaminu w adresie URL.'], 400);
            return;
        }

        // Krok 1: Znajdź ID egzaminu na podstawie jego kodu (np. 'INF.03' -> 1)
        $examTypeId = $this->questionModel->getExamTypeIdByCode($examCode);
        if (!$examTypeId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Egzamin o podanym kodzie nie istnieje.'], 404);
            return;
        }
        
        // Krok 2: Znajdź wszystkie tematy przypisane do tego egzaminu
        $allTopicIds = $this->questionModel->getTopicIdsByExamType($examTypeId);
        if (empty($allTopicIds)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Dla tego egzaminu nie zdefiniowano jeszcze żadnych tematów w puli pytań.'], 404);
            return;
        }

        // Krok 3: Pobierz 40 losowych pytań z puli tematów dla danego egzaminu
        $questions = $this->questionModel->getQuestions($allTopicIds, 40, $examTypeId);
        
        $questionsData = [];
        foreach ($questions as $question) {
            $answers = $this->answerModel->getAnswersToQuestion($question['id']);
            $questionsData[] = [
                'question' => $question,
                'answers' => $answers
            ];
        }

        $this->sendJsonResponse(['success' => true, 'questions' => $questionsData]);
    }

    /**
     * Endpoint do zapisywania wyniku ukończonego testu.
     * Działa tylko dla zalogowanych użytkowników.
     *
     * @api
     * @method POST
     * @path /api/save-test-result
     * @return void
     */
    public function saveTestResult()
    {
        $this->ensurePostRequest();

        // Sprawdzamy, czy użytkownik jest zalogowany
        if (!isset($_SESSION['user'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Tylko zalogowani użytkownicy mogą zapisywać wyniki.'], 403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Walidacja danych przychodzących z frontendu
        if (!isset($data['score_percent'], $data['correct_answers'], $data['total_questions'], $data['duration_seconds'], $data['topic_ids'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Niekompletne dane do zapisu.'], 400);
            return;
        }
        
        $userId = $_SESSION['user']->getId();

        $examData = [
            'user_id' => $userId,
            'is_full_exam' => 1, // Zakładamy, że to zawsze pełny egzamin
            'correct_answers' => (int)$data['correct_answers'],
            'total_questions' => (int)$data['total_questions'],
            'score_percent' => (float)$data['score_percent'],
            'duration_seconds' => (int)$data['duration_seconds']
        ];
        
        $topicIds = (array)$data['topic_ids'];

        // Używamy naszej nowej, transakcyjnej metody z modelu Exam
        $success = $this->examModel->saveExamWithTopics($examData, $topicIds);

        if ($success) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Wynik testu został pomyślnie zapisany.']);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Wystąpił błąd podczas zapisywania wyniku.'], 500);
        }
    }

    /**
     * Endpoint do sprawdzania odpowiedzi.
     *
     * Weryfikuje, czy odpowiedź udzielona przez użytkownika jest poprawna.
     * Jeśli użytkownik jest zalogowany, zapisuje jego postęp w nauce.
     *
     * @api
     * @method POST
     * @path /api/check-answer
     * @param int $_POST['question_id'] ID pytania, na które udzielono odpowiedzi.
     * @param int $_POST['answer_id'] ID odpowiedzi wybranej przez użytkownika.
     * @return void Wysyła odpowiedź JSON.
     */
    public function checkAnswer() {
        $this->ensurePostRequest();
        
        if (!isset($_POST['question_id'], $_POST['answer_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Brak ID pytania lub odpowiedzi.'], 400); // 400 Bad Request
            return;
        }
        
        $questionId = (int)$_POST['question_id'];
        $userAnswerId = (int)$_POST['answer_id'];

        $correctAnswer = $this->answerModel->getCorrectAnswerForQuestion($questionId);

        if (!$correctAnswer) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie znaleziono poprawnej odpowiedzi dla tego pytania.'], 404); // 404 Not Found
            return;
        }
        
        $isCorrect = ($userAnswerId === (int)$correctAnswer['id']);

        // Zapisz postęp tylko dla zalogowanych użytkowników
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user'])) {
            $this->userProgressModel->saveProgressForQuestion($_SESSION['user']->getId(), $questionId, $isCorrect);
        }

        $this->sendJsonResponse([
            'success' => true,
            'is_correct' => $isCorrect,
            'correct_answer_id' => (int)$correctAnswer['id'],
        ]);
    }
    
    /**
     * Zabezpiecza endpoint, aby akceptował tylko żądania POST.
     * * W przypadku innej metody HTTP, wysyła odpowiedź z kodem 405 i kończy działanie skryptu.
     *
     * @return void
     */
    private function ensurePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Metoda niedozwolona. Wymagany POST.'], 405); // 405 Method Not Allowed
            exit;
        }
    }

    /**
     * Zabezpiecza endpoint, aby akceptował tylko żądania GET.
     * * W przypadku innej metody HTTP, wysyła odpowiedź z kodem 405 i kończy działanie skryptu.
     *
     * @return void
     */
    private function ensureGetRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Metoda niedozwolona. Wymagany GET.'], 405); // 405 Method Not Allowed
            exit;
        }
    }

    /**
     * Wysyła odpowiedź w formacie JSON z odpowiednim kodem statusu HTTP.
     *
     * @param array<mixed> $data Tablica z danymi do zakodowania w JSON.
     * @param int $statusCode Kod statusu HTTP (domyślnie 200).
     * @return void
     */
    private function sendJsonResponse(array $data, int $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}