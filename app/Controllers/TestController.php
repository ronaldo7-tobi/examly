<?php
class TestController {
    private Question $Question;
    private Answer $Answer;
    private UserProgress $UserProgress;

    public function __construct()
    {
        $this->Question = new Question();
        $this->Answer = new Answer();
        $this->UserProgress = new UserProgress();
    }

    public function handleRequest($view): void
    {   
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
        }
        $data = [];
        $errors = [];
        $questions = [];
        $question = null;

        if (isset($_POST['subject'])) {
            $subjects = $_POST['subject'];
        }

        switch ($view) {
            case 'one_question':
                // Sprawdzenie odpowiedzi użytkownika (jeśli została przesłana)
                if (isset($_POST['answer'], $_POST['question_id'])) {
                    $userAnswer = (int)$_POST['answer'];
                    $questionId = (int)$_POST['question_id'];

                    $correctAnswer = $this->Answer->getCorrectAnswerForQuestion($questionId);
                    if ($correctAnswer) {
                        $isCorrect = $userAnswer === (int)$correctAnswer['id'] ? 1 : 0;
                        $this->UserProgress->saveProgressForQuestion($user->getId(), $questionId, $isCorrect);

                        // Możesz opcjonalnie zapisać do sesji, by pokazać feedback użytkownikowi
                        $_SESSION['last_result'] = $isCorrect;
                    }
                }

                // Wygenerowanie nowego pytania
                if (!empty($subjects)) {
                    $questions = $this->Question->getQuestions($subjects, 1, 'INF.03');
                    if (empty($questions)) {
                        die('Brak pytań do wybranych tematów. Wybierz inne tematy.');
                    } else {
                        $question = $questions[0];
                    }
                }

                if ($question) {
                    $answers = $this->Answer->getAnswersToQuestion($question['id']);
                } else {
                    $errors = 'Brak odpowiedniego pytania do wyświetlenia. Spróbuj ponownie.';
                }

                include __DIR__ . '/../../views/inf03_one_question.php';
                break;

            case 'personalized_test':
            case 'test':
            default:
                http_response_code(404);
                break;
        }
    }
}
?>