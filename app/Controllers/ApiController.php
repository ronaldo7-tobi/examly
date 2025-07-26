<?php

/**
 * Kontroler API do obsługi logiki quizu.
 * * Odpowiada za obsługę żądań przychodzących z frontendu, komunikację
 * z modelami bazy danych oraz zwracanie danych w formacie JSON.
 *
 * @version 1.0.0
 * @author Twoje Imię/Nazwa Firmy
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

    /**
     * Konstruktor ApiController.
     * * Inicjalizuje wszystkie niezbędne modele do interakcji z bazą danych.
     */
    public function __construct() {
        $this->questionModel = new Question(); 
        $this->answerModel = new Answer();
        $this->userProgressModel = new UserProgress();
    }

    /**
     * Endpoint do pobierania pojedynczego pytania.
     *
     * Na podstawie przekazanych parametrów GET, pobiera jedno pytanie z bazy danych.
     * Obsługuje zarówno standardowe losowanie pytań z wybranych kategorii, jak i
     * zaawansowane filtry "inteligentnej nauki" dla zalogowanych użytkowników.
     * * @api
     * @method GET
     * @path /api/get-question
     * @param array<string> $_GET['subject'] Tablica z nazwami wybranych kategorii (np. ['HTML', 'CSS']).
     * @param string|null $_GET['premium_option'] Opcjonalny filtr premium (np. 'toDiscover', 'toImprove').
     * @return void Wysyła odpowiedź JSON.
     */
    public function getQuestion()
    {
        $this->ensureGetRequest();

        $subjects = $_GET['subject'] ?? [];
        $specialFilter = $_GET['premium_option'] ?? null;
        
        if (empty($subjects)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Wybierz przynajmniej jedną kategorię materiału.'], 400);
            return;
        }

        $isUserLoggedIn = session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']);
        $userId = $isUserLoggedIn ? $_SESSION['user']->getId() : null;

        if ($specialFilter && !$isUserLoggedIn) {
            $this->sendJsonResponse(['success' => false, 'message' => "Opcje premium są dostępne tylko dla zalogowanych użytkowników."], 403); // 403 Forbidden
            return;
        }

        $question = null;
        $limit = 1;
        $examType = 'INF.03';

        // Logika wyboru odpowiedniej metody pobierania pytań
        if ($specialFilter && $isUserLoggedIn) {
            switch ($specialFilter) {
                case 'toDiscover':
                    $questions = $this->questionModel->getUndiscoveredQuestions($userId, $subjects, $limit, $examType);
                    break;
                case 'toImprove':
                    $questions = $this->questionModel->getLowerAccuracyQuestions($userId, $subjects, $limit, $examType);
                    break;
                case 'toRemind':
                    $questions = $this->questionModel->getQuestionsRepeatedAtTheLatest($userId, $subjects, $limit, $examType);
                    break;
                case 'lastMistakes':
                    $questions = $this->questionModel->getLastMistakes($userId, $subjects, $limit, $examType);
                    break;
                default:
                    // Standardowe losowanie, jeśli filtr jest nieznany
                    $questions = $this->questionModel->getQuestions($subjects, $limit, $examType);
                    break;
            }
        } else {
            // Standardowe pobieranie losowego pytania dla niezalogowanych lub bez filtra
            $questions = $this->questionModel->getQuestions($subjects, $limit, $examType);
        }

        $question = $questions[0] ?? null;

        // Jeśli nie znaleziono pytań dla danego filtra, zwróć specjalny status
        if (!$question) {
            $this->sendJsonResponse([
                'success' => true,
                'status' => 'no_questions_left',
                'message' => 'Gratulacje! Brak dostępnych pytań dla wybranych kryteriów.'
            ]);
            return;
        }

        $answers = $this->answerModel->getAnswersToQuestion($question['id']);

        $this->sendJsonResponse([
            'success' => true,
            'question' => $question,
            'answers' => $answers,
        ]);
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