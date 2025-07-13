<?php
class ApiController {
    
    private $questionModel;
    private $answerModel;
    private $userProgressModel;

    public function __construct() {
        $this->questionModel = new Question();
        $this->answerModel = new Answer();
        $this->userProgressModel = new UserProgress();
    }

    /**
     * Endpoint: /api/get-question
     * Zwraca nowe pytanie w formacie JSON.
     */
    public function getQuestion() {
        $this->ensurePostRequest();

        if (empty($_POST['subjects'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie wybrano żadnych tematów.'], 400);
            return;
        }

        $subjects = $_POST['subjects'];
        $questions = $this->questionModel->getQuestions($subjects, 1, 'INF.03');

        if (empty($questions)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie znaleziono pytań dla wybranych tematów.']);
            return;
        }

        $question = $questions[0];
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
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie znaleziono poprawnej odpowiedzi dla tego pytania.']);
            return;
        }
        
        $isCorrect = ($userAnswerId === (int)$correctAnswer['id']);

        // Sprawdzamy, czy sesja jest aktywna i użytkownik jest zalogowany
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
     * Wysyła odpowiedź w formacie JSON i ustawia kod statusu HTTP.
     * @param array $data Dane do zakodowania.
     * @param int $statusCode Kod statusu HTTP (domyślnie 200).
     */
    private function sendJsonResponse(array $data, int $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}