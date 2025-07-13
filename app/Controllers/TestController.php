<?php
class TestController {
    private Question $question;
    private Answer $answer;
    private UserProgress $userProgress;

    public function __construct()
    {
        $this->question = new Question();
        $this->answer = new Answer();
        $this->userProgress = new UserProgress();
    }

    public function handleRequest($view): void
    {   
        switch ($view) {
            case 'one_question':
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