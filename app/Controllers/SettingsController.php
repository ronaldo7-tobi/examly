<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\TokenService;
use App\Services\Mailer;
use App\Services\Validator;

class SettingsController extends BaseController
{
  private UserModel $userModel;
  private TokenService $tokenService;
  private Mailer $mailer;
  private Validator $validator;

  public function __construct()
  {
    parent::__construct();
    $this->requireAuth();
    $this->userModel = new UserModel();
    $this->tokenService = new TokenService();
    $this->mailer = new Mailer();
    $this->validator = new Validator();
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
   * Obsługuje logikę zmiany imienia i nazwiska z użyciem Validatora.
   */
  private function handleNameChange(): void
  {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $errors = [];

    // 1. Walidacja formatu danych przez centralny serwis
    $errors = $this->validator->validateNames($firstName, $lastName);

    // 2. Walidacja bezpieczeństwa dla kont lokalnych
    if ($this->currentUser->getAuthProvider() === 'local') {
      if (empty($password)) {
        $errors[] = 'Musisz podać hasło, aby zatwierdzić zmiany.';
      } elseif (!$this->userModel->checkPassword($this->currentUser->getId(), $password)) {
        $errors[] = 'Podane hasło jest nieprawidłowe.';
      }
    }

    if (empty($errors)) {
      // 3. Zapis zmian
      if ($this->userModel->updateName($this->currentUser->getId(), trim($firstName), trim($lastName))) {
        // Odświeżenie danych użytkownika w sesji
        $this->currentUser = $this->userModel->getUserById($this->currentUser->getId());
        $_SESSION['user'] = $this->currentUser;

        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Twoje dane zostały pomyślnie zaktualizowane.'];
      } else {
        $errors[] = 'Wystąpił nieoczekiwany błąd serwera podczas zapisu.';
      }
    }

    if (!empty($errors)) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => $errors];
    }
  }

  private function handlePasswordChange(): void
  {
    if ($this->currentUser->getAuthProvider() === 'google') {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => ['Konto Google nie wymaga hasła.']];
      return;
    }

    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_new_password'] ?? '';
    $errors = [];

    // 1. Sprawdź stare hasło
    if (!$this->userModel->checkPassword($this->currentUser->getId(), $current)) {
      $errors[] = 'Obecne hasło jest nieprawidłowe.';
    } else {
      // 2. Waliduj nowe hasło przez serwis
      $errors = $this->validator->validatePasswordStrength($new, $confirm);

      if ($current === $new) {
        $errors[] = 'Nowe hasło nie może być takie samo jak stare.';
      }
    }

    if (empty($errors)) {
      // 3. ZAPIS (tutaj, nie w walidatorze!)
      if ($this->userModel->updatePassword($this->currentUser->getId(), $new)) {
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Hasło zostało zmienione.'];
      } else {
        $errors[] = 'Błąd serwera podczas zapisu.';
      }
    }

    if (!empty($errors)) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => $errors];
    }
  }

  /**
   * Obsługuje żądanie usunięcia konta (Soft Delete).
   */
  public function handleAccountDeletion(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    $password = $_POST['password'] ?? '';
    $isGoogleUser = ($this->currentUser->getAuthProvider() === 'google');

    // Walidacja hasła tylko dla użytkowników lokalnych
    if (!$isGoogleUser && !$this->userModel->checkPassword($this->currentUser->getId(), $password)) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => ['Podane hasło jest nieprawidłowe.']];
      header('Location: ' . url('ustawienia?active=delete'));
      exit();
    }

    // Wywołanie Soft Delete z bazy danych
    if ($this->userModel->deleteUser($this->currentUser->getId())) {
      session_unset();
      session_destroy();
      session_start();
      $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Konto zostało pomyślnie usunięte. Przykro nam, że odchodzisz.'];
      header('Location: ' . url('/'));
      exit();
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
      // Odbieramy tablicę i wyciągamy z niej sam token.
      $tokenData = $this->tokenService->generateToken($this->currentUser->getId(), 'email_change', $newEmail);
      $token = $tokenData['token'];

      $verifyLink = url("weryfikacja?token=$token");

      $body = "<p>Witaj,</p><p>Aby potwierdzić zmianę adresu e-mail na $newEmail, kliknij w poniższy link:</p><p><a href='$verifyLink'>$verifyLink</a></p>";

      if ($this->mailer->send($newEmail, 'Potwierdź swój nowy adres e-mail w Examly', $body)) {
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
}
