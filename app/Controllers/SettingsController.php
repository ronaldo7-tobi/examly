<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\TokenService;
use App\Services\Mailer;

class SettingsController extends BaseController
{
  private UserModel $userModel;

  public function __construct()
  {
    parent::__construct();
    $this->requireAuth();
    $this->userModel = new UserModel();
  }

  /**
   * Wyświetla stronę ustawień (GET) lub obsługuje formularze (POST).
   */
  public function showSettingsPage(): void
  {
    // Jeśli metoda to POST, obsłuż dane i przekieruj
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handlePostRequest();
    }

    $remainingCooldown = 0;
    if (isset($_SESSION['email_change_sent'])) {
      $elapsed = time() - $_SESSION['email_change_sent'];
      $remainingCooldown = max(0, 60 - $elapsed);
    }

    // Jeśli metoda to GET, po prostu wyświetl widok
    $this->renderView('settings', [
      'activeForm' => $_GET['active'] ?? null,
      'emailChangeRemaining' => $remainingCooldown // Przekazujemy do widoku
    ]);
  }

  /**
   * Przetwarza dane POST, ustawia komunikaty flash i przekierowuje.
   */
  private function handlePostRequest(): void
  {
    $formType = $_POST['form_type'] ?? null;
    $redirectSuffix = '';

    if ($formType === 'change_name') {
      $this->handleNameChange();
      $redirectSuffix = '?active=name';
    } elseif ($formType === 'change_password') {
      $this->handlePasswordChange();
      $redirectSuffix = '?active=password';
    } elseif ($formType === 'change_email') {
      $this->handleEmailChange();
      $redirectSuffix = '?active=email';
    }

    // Przekieruj z powrotem na stronę ustawień, aby pokazać komunikat
    header('Location: ' . url("ustawienia$redirectSuffix"));
    exit();
  }

  /**
   * Obsługuje logikę zmiany imienia i nazwiska.
   */
  private function handleNameChange(): void
  {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    if (!$this->userModel->checkPassword($this->currentUser->getId(), $password)) {
      $errors[] = 'Twoje hasło jest nieprawidłowe.';
    }
    if (mb_strlen($firstName) < 2) $errors[] = 'Imię jest za krótkie.';
    if (mb_strlen($lastName) < 2) $errors[] = 'Nazwisko jest za krótkie.';

    if (empty($errors)) {
      if ($this->userModel->updateName($this->currentUser->getId(), $firstName, $lastName)) {
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Twoje dane zostały pomyślnie zaktualizowane.'];
      } else {
        $errors[] = 'Wystąpił błąd serwera. Spróbuj ponownie.';
      }
    }

    if (!empty($errors)) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => $errors];
    }
  }

  /**
   * Obsługuje logikę zmiany hasła.
   */
  private function handlePasswordChange(): void
  {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_new_password'] ?? '';
    $errors = [];

    if (!$this->userModel->checkPassword($this->currentUser->getId(), $currentPassword)) {
      $errors[] = 'Twoje obecne hasło jest nieprawidłowe.';
    } elseif (strlen($newPassword) < 6) {
      $errors[] = 'Nowe hasło musi mieć co najmniej 6 znaków.';
    } elseif ($newPassword !== $confirmPassword) {
      $errors[] = 'Podane hasła nie są identyczne.';
    }

    if (empty($errors)) {
      if ($this->userModel->updatePassword($this->currentUser->getId(), $newPassword)) {
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Twoje hasło zostało pomyślnie zmienione!'];
      } else {
        $errors[] = 'Wystąpił błąd serwera. Spróbuj ponownie.';
      }
    }

    if (!empty($errors)) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => $errors];
    }
  }

  private function handleEmailChange(): void
  {
    if (isset($_SESSION['email_change_sent']) && time() - $_SESSION['email_change_sent'] < 60) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => ['Kolejny e-mail weryfikacyjny można wysłać dopiero po minucie.']];
      header('Location: ' . url('ustawienia?active=email'));
      exit();
    }

    $newEmail = trim($_POST['new_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Podano niepoprawny format adresu e-mail.';
    } elseif ($this->userModel->checkEmail($newEmail)) {
      $errors[] = 'Ten adres e-mail jest już zajęty.';
    } elseif (!$this->userModel->checkPassword($this->currentUser->getId(), $password)) {
      $errors[] = 'Twoje hasło jest nieprawidłowe.';
    }

    if (empty($errors)) {
      $tokenService = new TokenService();
      // Odbieramy tablicę i wyciągamy z niej sam token.
      $tokenData = $tokenService->generateToken($this->currentUser->getId(), 'email_change', $newEmail);
      $token = $tokenData['token'];

      $verifyLink = url("weryfikacja?token=$token");

      $body = "<p>Witaj,</p><p>Aby potwierdzić zmianę adresu e-mail na $newEmail, kliknij w poniższy link:</p><p><a href='$verifyLink'>$verifyLink</a></p>";
      $mailer = new Mailer();

      if ($mailer->send($newEmail, 'Potwierdź swój nowy adres e-mail w Examly', $body)) {
        $_SESSION['email_change_sent'] = time();
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Link weryfikacyjny został wysłany na Twój nowy adres e-mail.'];
      } else {
        $errors[] = 'Nie udało się wysłać e-maila weryfikacyjnego. Spróbuj ponownie.';
      }
    }

    if (!empty($errors)) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => $errors];
    }

    header('Location: ' . url('ustawienia?active=email'));
    exit();
  }

  /**
   * Obsługuje żądanie usunięcia konta.
   */
  public function handleAccountDeletion(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: ' . url('ustawienia'));
      exit();
    }

    $password = $_POST['password'] ?? '';

    if ($this->userModel->checkPassword($this->currentUser->getId(), $password)) {
      // Hasło poprawne - usuwamy konto
      $this->userModel->deleteUser($this->currentUser->getId());

      // Wylogowujemy i niszczymy sesję
      session_unset();
      session_destroy();

      // Przekierowujemy na stronę główną z komunikatem
      session_start();
      $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Twoje konto zostało pomyślnie usunięte.'];
      header('Location: ' . url('/'));
      exit();
    } else {
      // Hasło niepoprawne
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => ['Podane hasło jest nieprawidłowe.']];
      header('Location: ' . url('ustawienia?active=delete'));
      exit();
    }
  }
}
