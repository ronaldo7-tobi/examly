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
     * Zwraca nowe pytanie w formacie JSON lub odpowiedni komunikat.
     */
    public function getQuestion()
    {
        $this->ensurePostRequest();

        $isUserLoggedIn = session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']);
        $userId = $isUserLoggedIn ? $_SESSION['user']->getId() : null;

        $subjectsAndOptions = $_POST['subjects'] ?? [];
        if (empty($subjectsAndOptions)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie wybrano żadnych tematów.'], 400);
            return;
        }

        $isUndiscoveredMode = in_array('toDiscover', $subjectsAndOptions);
        $subjects = array_filter($subjectsAndOptions, function($value) {
            return !in_array($value, ['toDiscover', 'toImprove', 'toRemind']);
        });

        if (empty($subjects)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Wybierz przynajmniej jedną kategorię materiału.'], 400);
            return;
        }

        $questions = []; // Inicjujemy pustą tablicę na pytania

        if ($isUndiscoveredMode) {
            if (!$isUserLoggedIn) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Opcja "Nieodkryte" jest dostępna tylko dla zalogowanych użytkowników.']);
                return;
            }
            $questions = $this->questionModel->getUndiscoveredQuestions($userId, $subjects, 1, 'INF.03');
        } else {
            // Standardowa logika, gdy opcje premium nie są wybrane
            $questions = $this->questionModel->getQuestions($subjects, 1, 'INF.03');
        }

        // Teraz mamy JEDNO miejsce, które obsługuje wynik, niezależnie od trybu.

        if (empty($questions)) {
            // Jeśli nie znaleziono pytań, wyślij odpowiedni komunikat informacyjny.
            $seenSubjects = [];
            if($isUndiscoveredMode){
                foreach ($subjects as $subject) {
                    if (empty($this->questionModel->getUndiscoveredQuestions($userId, [$subject], 1, 'INF.03'))) {
                        $seenSubjects[] = $subject;
                    }
                }
            }
        
            $message = 'Gratulacje! Odpowiedziałeś już na wszystkie pytania z kategorii: ' . implode(', ', $subjects) . '.';
            if (count($seenSubjects) !== count($subjects) && $isUndiscoveredMode){
                // Jeśli rozwiązano tylko część kategorii, a dla reszty po prostu nie ma pytań
                $message = 'Nie znaleziono więcej pytań dla wybranych kryteriów.';
            }


            $this->sendJsonResponse([
                'success' => true,
                'status' => 'no_questions_left',
                'message' => $message
            ]);
            return;
        }

        // Jeśli znaleziono pytanie, przygotuj i wyślij odpowiedź.
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
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Nie znaleziono poprawnej odpowiedzi dla tego pytania.'
            ]);
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