<?php
require_once __DIR__ . '/../../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Klasa Mailer - Usługa do wysyłania wiadomości e-mail.
 *
 * Ta klasa działa jako "fasada" (Facade) lub "adapter" dla biblioteki PHPMailer.
 * Jej celem jest uproszczenie procesu wysyłania e-maili i centralizacja
 * konfiguracji w jednym miejscu. Dzięki temu reszta aplikacji nie musi wiedzieć,
 * jakiej biblioteki używamy – wystarczy wywołać prostą metodę `send()`.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class Mailer
{
  /**
   * Przechowuje skonfigurowaną instancję obiektu PHPMailer.
   * @var PHPMailer
   */
  private PHPMailer $mailer;

  /**
   * Konstruktor inicjalizujący i konfigurujący PHPMailer do wysyłki przez SMTP.
   *
   * Logika działania:
   * 1. Tworzy nową instancję PHPMailer z włączoną obsługą wyjątków.
   * 2. Konfiguruje wszystkie niezbędne parametry do połączenia z serwerem SMTP
   *    (w tym przypadku Gmail).
   * 3. Ustawia domyślne dane nadawcy oraz format wiadomości na HTML.
   * 4. W przypadku błędu konfiguracji rzuca wyjątek, aby zapobiec
   *    działaniu niepoprawnie skonfigurowanej usługi.
   *
   * @throws Exception Jeśli konfiguracja mailera się nie powiedzie.
   */
  public function __construct()
  {
    // Utwórz instancję PHPMailer; przekazanie `true` włącza wyjątki.
    $this->mailer = new PHPMailer(true);

    // --- Konfiguracja serwera SMTP ---
    // Użyj SMTP do wysyłki.
    $this->mailer->isSMTP();
    // Adres serwera SMTP Gmaila.
    $this->mailer->Host = '***REMOVED***';
    // Włącz autoryzację SMTP.
    $this->mailer->SMTPAuth = true;
    // E-mail do wysyłania wiadomości.
    $this->mailer->Username = '***REMOVED***';
    // To jest hasło aplikacji Google, a nie hasło do konta.
    $this->mailer->Password = '***REMOVED***';
    // Włącz szyfrowanie TLS.
    $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    // Port TCP do połączenia.
    $this->mailer->Port = 587;

    // --- Domyślne ustawienia wiadomości ---
    // Ustaw domyślnego nadawcę.
    $this->mailer->setFrom('***REMOVED***', 'Examly');
    // Ustaw domyślny format wiadomości na HTML.
    $this->mailer->isHTML(true);
    // Ustaw kodowanie znaków na UTF-8.
    $this->mailer->CharSet = 'UTF-8';
  }

  /**
   * Wysyła wiadomość e-mail do jednego odbiorcy.
   *
   * Metoda opakowuje logikę wysyłki w blok `try-catch`, aby gracefully
   * obsłużyć ewentualne błędy (np. błąd połączenia z serwerem, zły adres)
   * i zwrócić prostą wartość `true`/`false`.
   *
   * @param string $to Adres e-mail odbiorcy.
   * @param string $subject Temat wiadomości.
   * @param string $body Treść wiadomości w formacie HTML.
   *
   * @return bool `true`, jeśli wysyłka się powiodła, `false` w przypadku błędu.
   */
  public function send(string $to, string $subject, string $body): bool
  {
    try {
      // Krok 1: Wyczyść listę odbiorców z poprzednich wysyłek.
      // Kluczowe, jeśli ten sam obiekt Mailer miałby być użyty wielokrotnie.
      $this->mailer->clearAllRecipients();

      // Krok 2: Ustaw dane konkretnej wiadomości.
      $this->mailer->addAddress($to);
      $this->mailer->Subject = $subject;
      $this->mailer->Body = $body;

      // Krok 3: Wyślij e-mail.
      return $this->mailer->send();
    } catch (Exception $e) {
      // W przypadku błędu wysyłki, zapisz go w logu serwera
      // i zwróć `false`, informując, że operacja się nie powiodła.
      error_log('Błąd wysyłki e-maila: ' . $this->mailer->ErrorInfo);
      return false;
    }
  }
}
