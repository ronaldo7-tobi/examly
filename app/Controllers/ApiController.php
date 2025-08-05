<?php

/**
 * Kontroler API do obsługi logiki quizu.
 *
 * Odpowiada za obsługę żądań przychodzących z frontendu, komunikację
 * z modelami bazy danych oraz zwracanie danych w formacie JSON.
 * Jest to centralny punkt logiki aplikacji po stronie serwera.
 *
 * @version 1.1.0
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
    
    /**
     * @var Exam Model do zarządzania egzaminami.
     */
    private $examModel;

    /**
     * Konstruktor klasy ApiController.
     *
     * Inicjalizuje obiekty modeli, które są niezbędne do interakcji
     * z bazą danych. Każdy model odpowiada za inną część logiki biznesowej
     * (pytania, odpowiedzi, postępy użytkownika, egzaminy).
     */
    public function __construct() {
        $this->questionModel = new Question();
        $this->answerModel = new Answer();
        $this->userProgressModel = new UserProgress();
        $this->examModel = new Exam();
    }

    /**
     * Pobiera pojedyncze pytanie dla określonego egzaminu i kategorii.
     *
     * Endpoint jest elastyczny i pozwala na filtrowanie pytań na podstawie
     * kodu egzaminu, wybranych kategorii tematycznych oraz specjalnych
     * opcji dla użytkowników premium (np. pytania do powtórki, najtrudniejsze).
     *
     * @api
     * @method GET
     * @path /api/question/{examCode}
     *
     * @param array<string, string> $params Parametry z URL, np. ['examCode' => 'INF.03'].
     * @global array<int> $_GET['subject'] Tablica numerycznych ID wybranych kategorii. Wymagana.
     * @global string|null $_GET['premium_option'] Opcjonalny filtr premium dla zalogowanych użytkowników.
     * Dostępne wartości: 'toDiscover', 'toImprove', 'toRemind', 'lastMistakes'.
     *
     * @return void Funkcja nie zwraca wartości, lecz wysyła odpowiedź JSON, która może zawierać:
     * - Obiekt pytania i tablicę odpowiedzi (sukces).
     * - Komunikat o braku dostępnych pytań (sukces, status 'no_questions_left').
     * - Komunikat o błędzie (np. brak parametrów, błąd serwera).
     */
    public function getQuestion(array $params)
    {
        $this->ensureGetRequest();

        // Krok 1: Walidacja i pobranie kodu egzaminu z parametrów URL.
        $examCode = $params['examCode'] ?? null;
        if (!$examCode) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie podano kodu egzaminu w adresie URL.'], 400);
            return;
        }

        // Krok 2: Walidacja i pobranie ID tematów z parametrów GET.
        $subjectIds = $_GET['subject'] ?? [];
        if (empty($subjectIds)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Wybierz przynajmniej jedną kategorię materiału.'], 400);
            return;
        }

        // Krok 3: Konwersja kodu egzaminu (np. "INF.03") na jego numeryczne ID.
        $examTypeId = $this->questionModel->getExamTypeIdByCode($examCode);
        if (!$examTypeId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Egzamin o podanym kodzie nie istnieje.'], 404);
            return;
        }

        // Krok 4: Przygotowanie filtrów i danych użytkownika.
        $subjectIds = array_map('intval', $subjectIds);
        $specialFilter = $_GET['premium_option'] ?? null;
        $isUserLoggedIn = session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']);
        $userId = $isUserLoggedIn ? $_SESSION['user']->getId() : null;
        $limit = 1;

        // Krok 5: Weryfikacja uprawnień do filtrów premium.
        if ($specialFilter && !$isUserLoggedIn) {
            $this->sendJsonResponse(['success' => false, 'message' => "Opcje premium są dostępne tylko dla zalogowanych użytkowników."], 403);
            return;
        }

        // Krok 6: Pobranie pytania z modelu z uwzględnieniem filtrów.
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
            // Domyślne pobieranie losowego pytania.
            $questions = $this->questionModel->getQuestions($subjectIds, $limit, $examTypeId);
        }

        $question = $questions[0] ?? null;

        // Krok 7: Obsługa przypadku, gdy nie znaleziono żadnych pytań.
        if (!$question) {
            $this->sendJsonResponse(['success' => true, 'status' => 'no_questions_left', 'message' => 'Gratulacje! Brak dostępnych pytań dla wybranych kryteriów.']);
            return;
        }
        
        // Krok 8: Pobranie odpowiedzi do pytania i wysłanie kompletnej odpowiedzi JSON.
        $answers = $this->answerModel->getAnswersToQuestion($question['id']);
        $this->sendJsonResponse(['success' => true, 'question' => $question, 'answers' => $answers]);
    }

    /**
     * Pobiera pełny arkusz egzaminacyjny (40 pytań) dla danego egzaminu.
     *
     * Endpoint losuje 40 pytań z całej puli dostępnej dla określonego
     * egzaminu, identyfikowanego przez jego kod.
     *
     * @api
     * @method GET
     * @path /api/test/full/{examCode}
     *
     * @param array<string, string> $params Parametry z URL, np. ['examCode' => 'INF.03'].
     *
     * @return void Funkcja nie zwraca wartości, lecz wysyła odpowiedź JSON zawierającą
     * tablicę 40 obiektów, gdzie każdy obiekt to pytanie wraz z odpowiedziami,
     * lub komunikat o błędzie.
     */
    public function getFullTest(array $params)
    {
        $this->ensureGetRequest();
        
        // Krok 1: Walidacja i pobranie kodu egzaminu.
        $examCode = $params['examCode'] ?? null;
        if (!$examCode) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie podano kodu egzaminu w adresie URL.'], 400);
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
            $this->sendJsonResponse(['success' => false, 'message' => 'Dla tego egzaminu nie zdefiniowano jeszcze żadnych tematów w puli pytań.'], 404);
            return;
        }

        // Krok 4: Pobranie 40 losowych pytań z puli tematów dla danego egzaminu.
        $questions = $this->questionModel->getQuestions($allTopicIds, 40, $examTypeId);
        
        // Krok 5: Przygotowanie finalnej struktury danych z pytaniami i odpowiedziami.
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
     * Zapisuje wynik ukończonego testu w bazie danych.
     *
     * Endpoint dostępny wyłącznie dla zalogowanych użytkowników. Odbiera dane
     * w formacie JSON, waliduje je i zapisuje wynik egzaminu wraz z powiązanymi
     * tematami w ramach jednej transakcji bazodanowej.
     *
     * @api
     * @method POST
     * @path /api/save-test-result
     *
     * @uses file_get_contents('php://input') do odczytu danych JSON z ciała żądania.
     * Oczekiwana struktura: {
     * "score_percent": float,
     * "correct_answers": int,
     * "total_questions": int,
     * "duration_seconds": int,
     * "topic_ids": array<int>
     * }
     *
     * @return void Wysyła odpowiedź JSON z komunikatem o sukcesie lub porażce.
     */
    public function saveTestResult()
    {
        $this->ensurePostRequest();

        // Krok 1: Weryfikacja, czy użytkownik jest zalogowany.
        if (!isset($_SESSION['user'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Tylko zalogowani użytkownicy mogą zapisywać wyniki.'], 403);
            return;
        }

        // Krok 2: Odczyt i walidacja danych wejściowych JSON.
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['score_percent'], $data['correct_answers'], $data['total_questions'], $data['duration_seconds'], $data['topic_ids'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Niekompletne dane do zapisu.'], 400);
            return;
        }
        
        // Krok 3: Przygotowanie danych do zapisu.
        $userId = $_SESSION['user']->getId();
        $examData = [
            'user_id' => $userId,
            'is_full_exam' => 1,
            'correct_answers' => (int)$data['correct_answers'],
            'total_questions' => (int)$data['total_questions'],
            'score_percent' => (float)$data['score_percent'],
            'duration_seconds' => (int)$data['duration_seconds']
        ];
        $topicIds = (array)$data['topic_ids'];

        // Krok 4: Zapis wyniku egzaminu i powiązanych tematów w transakcji.
        $success = $this->examModel->saveExamWithTopics($examData, $topicIds);

        // Krok 5: Wysłanie odpowiedzi w zależności od wyniku operacji.
        if ($success) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Wynik testu został pomyślnie zapisany.']);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Wystąpił błąd podczas zapisywania wyniku.'], 500);
        }
    }

    /**
     * Sprawdza poprawność odpowiedzi użytkownika i zapisuje jego postęp.
     *
     * Weryfikuje, czy odpowiedź o podanym ID jest prawidłowa dla danego pytania.
     * Dla zalogowanych użytkowników, wynik (poprawny/niepoprawny) jest
     * zapisywany w bazie danych w celu śledzenia postępów w nauce.
     *
     * @api
     * @method POST
     * @path /api/check-answer
     *
     * @global int $_POST['question_id'] ID pytania, na które udzielono odpowiedzi.
     * @global int $_POST['answer_id'] ID odpowiedzi wybranej przez użytkownika.
     *
     * @return void Wysyła odpowiedź JSON z informacją o poprawności odpowiedzi
     * oraz ID poprawnej odpowiedzi.
     */
    public function checkAnswer() {
        $this->ensurePostRequest();
        
        // Krok 1: Walidacja, czy przesłano wymagane identyfikatory.
        if (!isset($_POST['question_id'], $_POST['answer_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Brak ID pytania lub odpowiedzi.'], 400);
            return;
        }
        
        $questionId = (int)$_POST['question_id'];
        $userAnswerId = (int)$_POST['answer_id'];

        // Krok 2: Pobranie poprawnej odpowiedzi z bazy danych.
        $correctAnswer = $this->answerModel->getCorrectAnswerForQuestion($questionId);
        if (!$correctAnswer) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Nie znaleziono poprawnej odpowiedzi dla tego pytania.'], 404);
            return;
        }
        
        $isCorrect = ($userAnswerId === (int)$correctAnswer['id']);

        // Krok 3: Jeśli użytkownik jest zalogowany, zapisz jego postęp.
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user'])) {
            $this->userProgressModel->saveProgressForQuestion($_SESSION['user']->getId(), $questionId, $isCorrect);
        }

        // Krok 4: Zwróć wynik weryfikacji.
        $this->sendJsonResponse([
            'success' => true,
            'is_correct' => $isCorrect,
            'correct_answer_id' => (int)$correctAnswer['id'],
        ]);
    }
    
    /**
     * Prywatna metoda pomocnicza weryfikująca, czy żądanie jest typu POST.
     *
     * Jeśli metoda żądania HTTP jest inna niż POST, skrypt jest przerywany,
     * a do klienta wysyłana jest odpowiedź JSON z kodem statusu 405 (Method Not Allowed).
     *
     * @return void
     */
    private function ensurePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Metoda niedozwolona. Wymagany POST.'], 405);
            exit;
        }
    }

    /**
     * Prywatna metoda pomocnicza weryfikująca, czy żądanie jest typu GET.
     *
     * Jeśli metoda żądania HTTP jest inna niż GET, skrypt jest przerywany,
     * a do klienta wysyłana jest odpowiedź JSON z kodem statusu 405 (Method Not Allowed).
     *
     * @return void
     */
    private function ensureGetRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Metoda niedozwolona. Wymagany GET.'], 405);
            exit;
        }
    }

    /**
     * Centralna funkcja do wysyłania odpowiedzi w formacie JSON.
     *
     * Ustawia odpowiednie nagłówki HTTP (Content-Type, kod statusu),
     * konwertuje tablicę PHP na format JSON i wysyła ją do klienta.
     * Zapewnia spójność wszystkich odpowiedzi API.
     *
     * @param array<mixed> $data Tablica asocjacyjna z danymi do wysłania.
     * @param int $statusCode Kod statusu HTTP do ustawienia (domyślnie 200 OK).
     *
     * @return void
     */
    private function sendJsonResponse(array $data, int $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}