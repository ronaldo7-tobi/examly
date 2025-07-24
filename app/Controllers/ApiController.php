<?php
class ApiController {
    
    private $questionModel;
    private $answerModel;
    private $userProgressModel;

    public function __construct() {
        // Upewnij się, że używasz poprawnych nazw klas swoich modeli
        $this->questionModel = new Question(); 
        $this->answerModel = new Answer();
        $this->userProgressModel = new UserProgress();
    }

    /**
     * Endpoint: /api/get-question
     * Zwraca nowe pytanie w formacie JSON na podstawie wybranych kryteriów.
     * Używa metody GET.
     */
    public function getQuestion()
    {
        $this->ensureGetRequest();

        $subjects = $_GET['subject'] ?? [];
        $specialFilter = $_GET['premium_option'] ?? null; // Odczytujemy opcję premium
        
        if (empty($subjects)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Wybierz przynajmniej jedną kategorię materiału.'], 400);
            return;
        }

        $isUserLoggedIn = session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']);
        $userId = $isUserLoggedIn ? $_SESSION['user']->getId() : null;

        if ($specialFilter && !$isUserLoggedIn) {
            $this->sendJsonResponse(['success' => false, 'message' => "Opcje premium są dostępne tylko dla zalogowanych użytkowników."], 403);
            return;
        }

        // --- NOWA, POPRAWIONA LOGIKA ---
        $question = null;
        $limit = 1; // Zawsze pobieramy jedno pytanie
        $examType = 'INF.03'; // Ustaw typ egzaminu na sztywno lub pobierz go dynamicznie

        if ($specialFilter === 'toDiscover' && $isUserLoggedIn) {
            // Użyj metody do pobierania nieodkrytych pytań
            $questions = $this->questionModel->getUndiscoveredQuestions($userId, $subjects, $limit, $examType);
            $question = $questions[0] ?? null; // Pobierz pierwszy element, jeśli istnieje
            if (!$question) {
                $this->sendJsonResponse([
                    'success' => true,
                    'status' => 'no_questions_left',
                    'message' => 'Gratulacje! Wygląda na to, że odpowiedziałeś na wszystkie dostępne pytania z wybranych kategorii.'
                ]);
                return;
            }
        } else if ($specialFilter === 'toImprove' && $isUserLoggedIn) {
            $questions = $this->questionModel->getLowerAccuracyQuestions($userId, $subjects, $limit, $examType);
            $question = $questions[0] ?? null;
            if (!$question) {
                $this->sendJsonResponse([
                    'success' => true,
                    'status' => 'no_questions_left',
                    'message' => 'Gratulacje! Wygląda na to, że masz wysoką skuteczność odpowiedzi z wybranych kategorii.'
                ]);
                return;
            }
        } else if ($specialFilter === 'toRemind') {
            $questions = $this->questionModel->getQuestionsRepeatedAtTheLatest($userId, $subjects, $limit, $examType);
            $question = $questions[0] ?? null;
        } else if ($specialFilter === 'lastMistakes') {
            $questions = $this->questionModel->getLastMistakes($userId, $subjects, $limit, $examType);
            $question = $questions[0] ?? null;
            if (!$question) {
                $this->sendJsonResponse([
                    'success' => true,
                    'status' => 'no_questions_left',
                    'message' => 'Gratulacje! Wygląda na to, że na wszystkie pytania z wybranych kategorii ostatnio odpowiadałeś poprawnie'
                ]);
                return;
            }
        } else {
            // Standardowe pobieranie losowego pytania
            $questions = $this->questionModel->getQuestions($subjects, $limit, $examType);
            $question = $questions[0] ?? null; // Pobierz pierwszy element, jeśli istnieje
        }
        // --- KONIEC NOWEJ LOGIKI ---

        $answers = $this->answerModel->getAnswersToQuestion($question['id']);

        $this->sendJsonResponse([
            'success' => true,
            'question' => $question,
            'answers' => $answers,
        ]);
    }

    /**
     * Endpoint: /api/check-answer
     * Sprawdza odpowiedź użytkownika i zwraca wynik.
     */
    public function checkAnswer() {
        $this->ensurePostRequest();
        
        if (!isset($_POST['question_id'], $_POST['answer_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Brak ID pytania lub odpowiedzi.'], 400);
            return;
        }
        
        $questionId = (int)$_POST['question_id'];
        $userAnswerId = (int)$_POST['answer_id'];

        $correctAnswer = $this->answerModel->getCorrectAnswerForQuestion($questionId);

        if (!$correctAnswer) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Nie znaleziono poprawnej odpowiedzi dla tego pytania.'
            ], 404);
            return;
        }
        
        $isCorrect = ($userAnswerId === (int)$correctAnswer['id']);

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
     * Upewnia się, że żądanie jest typu POST.
     */
    private function ensurePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Metoda niedozwolona. Wymagany POST.'], 405);
            exit;
        }
    }

    /**
     * Upewnia się, że żądanie jest typu GET.
     */
    private function ensureGetRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Metoda niedozwolona. Wymagany GET.'], 405);
            exit;
        }
    }

    /**
     * Wysyła odpowiedź w formacie JSON i ustawia kod statusu HTTP.
     */
    private function sendJsonResponse(array $data, int $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}