<?php
// Wychodzimy z /Services do /app, a potem wchodzimy do /Core
require_once '../Core/autoload.php'; // <-- WSTAWIAMY POPRAWNĄ ŚCIEŻKĘ DO PLIKU Z TWOIM KODEM AUTOLOADERA

// Na samej górze api.php - solidna obsługa błędów na czas developmentu
error_reporting(E_ALL);
ini_set('display_errors', 0); // Wyłączamy standardowe wyświetlanie błędów jako HTML

// Rejestrujemy własną funkcję obsługi błędów
set_error_handler(function ($severity, $message, $file, $line) {
    // Jeśli błąd już został stłumiony przez @, nie rób nic
    if (error_reporting() === 0) {
        return false;
    }
    // Wyrzuć wyjątek, który możemy złapać
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Rejestrujemy funkcję, która złapie wszystkie wyjątki
set_exception_handler(function ($exception) {
    // Ustawiamy nagłówek, że to błąd serwera
    http_response_code(500);
    // Zapewniamy, że odpowiedź jest JSON
    header('Content-Type: application/json');
    
    // Zwracamy błąd w formacie JSON
    echo json_encode([
        'success' => false,
        'message' => 'Wystąpił krytyczny błąd serwera.',
        'error_details' => [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]
    ]);
    exit; // Zakończ skrypt, aby nic więcej się nie wykonało
});






// Ustawiamy nagłówek, aby przeglądarka wiedziała, że odpowiedź to JSON
header('Content-Type: application/json');

// Tworzymy instancje modeli (zakładając, że masz połączenie z bazą $db)
$questionModel = new Question();
$answerModel = new Answer();

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Nieznana akcja.'];

switch ($action) {
    /**
     * Akcja pobierania nowego pytania na podstawie wybranych tematów.
     */
    case 'get_question':
        if (!empty($_POST['subjects'])) {
            $subjects = $_POST['subjects'];
            // Losujemy jedno pytanie
            $questions = $questionModel->getQuestions($subjects, 1, 'INF.03');

            if (!empty($questions)) {
                $question = $questions[0];
                $answers = $answerModel->getAnswersToQuestion($question['id']);

                $response = [
                    'success' => true,
                    'question' => $question,
                    'answers' => $answers,
                ];
            } else {
                $response['message'] = 'Nie znaleziono pytań dla wybranych tematów.';
            }
        } else {
            $response['message'] = 'Nie wybrano żadnych tematów.';
        }
        break;

    /**
     * Akcja sprawdzania odpowiedzi użytkownika.
     */
    case 'check_answer':
        if (isset($_POST['question_id'], $_POST['answer_id'])) {
            $questionId = (int)$_POST['question_id'];
            $userAnswerId = (int)$_POST['answer_id'];

            $correctAnswer = $answerModel->getCorrectAnswerForQuestion($questionId);

            if ($correctAnswer) {
                $isCorrect = ($userAnswerId === (int)$correctAnswer['id']);
                // Tutaj możesz dodać logikę zapisu do bazy (UserProgress)
                // $this->UserProgress->saveProgressForQuestion(...)

                $response = [
                    'success' => true,
                    'is_correct' => $isCorrect,
                    'correct_answer_id' => (int)$correctAnswer['id'],
                ];
            } else {
                $response['message'] = 'Nie znaleziono poprawnej odpowiedzi dla tego pytania.';
            }
        } else {
            $response['message'] = 'Brak ID pytania lub odpowiedzi.';
        }
        break;
}

// Zwracamy odpowiedź w formacie JSON
echo json_encode($response);