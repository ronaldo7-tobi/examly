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
        $data = [];
        $errors = [];
        if (isset($_POST['subject'])) {
            $subjects = $_POST['subject'];
        }
        switch ($view) {
            case 'one_question':
                $questions = [];
                $answers = [];
                if (isset($subjects)) {
                    $questions = $this->Question->getQuestions($subjects, 1, 'INF.03');
                    if (empty($questions)) {
                        die('Brak pytań do wybranych tematów. Wybierz inne tematy.');
                    }
                    $question = $questions[0] ?? null;
                    if ($question) {
                        $answers = $this->Answer->getAnswersToQuestion($question['id']);
                    } else {
                        $errors = 'Brak odpowiedniego pytania do wyświetlenia. Spróbuj ponownie.';
                    }
                }


                
                include __DIR__ . '/../../views/inf03_one_question.php';

            case 'personalized_test':
            case 'test':
            default:
                http_response_code(404);
                break;
        }
    }
}
?>