<?php
require_once __DIR__ . '/../../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Klasa Mailer odpowiedzialna za wysyłanie wiadomości e-mail.
 * Wykorzystuje bibliotekę PHPMailer.
 */
class Mailer
{
    /**
     * Obiekt PHPMailer.
     *
     * @var PHPMailer
     */
    private PHPMailer $mailer;

    /**
     * Konstruktor inicjalizujący PHPMailera i ustawiający konfigurację SMTP.
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        try {
            // Konfiguracja SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host       = '***REMOVED***';
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = '***REMOVED***';
            $this->mailer->Password   = '***REMOVED***';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = 587;

            // Domyślny nadawca
            $this->mailer->setFrom('***REMOVED***', 'Examly');
            $this->mailer->isHTML(true);
        } catch (Exception $e) {
            // Logowanie błędu lub rzutowanie wyjątku dalej
            throw new Exception("Błąd konfiguracji mailera: " . $e->getMessage());
        }
    }

    /**
     * Wysyła wiadomość e-mail do użytkownika.
     *
     * @param string $to E-mail odbiorcy.
     * @param string $subject Temat wiadomości.
     * @param string $body Treść wiadomości (HTML).
     * @return bool True, jeśli wysyłka się powiodła.
     */
    public function send(string $to, string $subject, string $body): bool
    {
        try {
            $this->mailer->clearAddresses(); // Wyczyść stare adresy
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            return $this->mailer->send();
        } catch (Exception $e) {
            // Możesz logować błąd tutaj, jeśli chcesz
            return false;
        }
    }
}
