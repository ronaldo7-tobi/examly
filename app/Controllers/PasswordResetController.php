<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\TokenService;
use App\Services\Mailer;
use App\Services\Validator;

class PasswordResetController extends BaseController
{
  private UserModel $userModel;
  private TokenService $tokenService;
  private Validator $validator;
  private Mailer $mailer;

  public function __construct()
  {
    parent::__construct();
    $this->userModel = new UserModel();
    $this->tokenService = new TokenService();
    $this->validator = new Validator();
    $this->mailer = new Mailer();
  }

  /**
   * Wyświetla formularz do wpisania adresu e-mail.
   */
  public function showForgotPasswordForm(): void
  {
    $remainingCooldown = 0;
    if (isset($_SESSION['password_reset_sent'])) {
      $elapsed = time() - $_SESSION['password_reset_sent'];
      $remainingCooldown = max(0, 60 - $elapsed);
    }

    $this->renderView('forgot_password', ['remaining' => $remainingCooldown]);
  }

  /**
   * Obsługuje wysłanie linku do resetowania hasła.
   */
  public function handleForgotPasswordRequest(): void
  {
    if (isset($_SESSION['password_reset_sent']) && time() - $_SESSION['password_reset_sent'] < 60) {
      $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Kolejny e-mail można wysłać dopiero po minucie.'];
      header('Location: ' . url('reset-hasla'));
      exit();
    }

    $email = $_POST['email'] ?? null;
    $user = $this->userModel->getUserByEmail($email);

    if ($user) {
      // Generujemy token i kod
      $tokenData = $this->tokenService->generateToken($user->getId(), 'password_reset');

      if ($tokenData) {
        $token = $tokenData['token'];
        $code = $tokenData['code'];

        $resetLink = url("nowe-haslo?token=$token");
        $body = "<p>Witaj,</p>" .
          "<p>Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta. Kliknij poniższy link, aby ustawić nowe hasło:</p>" .
          "<p><a href='$resetLink'>$resetLink</a></p>" .
          "<p>Na następnej stronie zostaniesz poproszony o podanie 6-cyfrowego kodu bezpieczeństwa, który jest widoczny na stronie resetowania hasła.</p>" .
          "<p>Jeśli to nie Ty prosiłeś o zmianę, zignoruj tę wiadomość.</p>";

        if ($this->mailer->send($user->getEmail(), 'Reset hasła w serwisie Examly', $body)) {
          $_SESSION['password_reset_sent'] = time();
        }

        // Zapisz kod do sesji, aby wyświetlić go użytkownikowi
        $_SESSION['password_reset_code'] = $code;
      }
    }

    // Zawsze pokazuj ten sam komunikat, aby nie ujawniać, czy e-mail istnieje w bazie
    $_SESSION['flash_message'] = [
      'type' => 'info',
      'text' => "Jeśli konto o podanym adresie e-mail ($email) istnieje, wysłaliśmy na nie instrukcję resetowania hasła."
    ];

    header('Location: ' . url('reset-hasla'));
    exit();
  }

  /**
   * Wyświetla formularz do ustawienia nowego hasła.
   */
  public function showNewPasswordForm(): void
  {
    $token = $_GET['token'] ?? null;
    $tokenRecord = $this->tokenService->getTokenRecord($token);

    if (!$token || !$tokenRecord || strtotime($tokenRecord['expires_at']) < time()) {
      // Token jest nieprawidłowy lub wygasł
      $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Link do resetowania hasła jest nieprawidłowy lub wygasł.'];
      header('Location: ' . url('logowanie'));
      exit();
    }

    // Token jest poprawny, wyświetl formularz
    $this->renderView('reset_password', ['token' => $token]);
  }

  public function handleNewPasswordRequest(): void
  {
    $token = $_POST['token'] ?? null;
    $tokenRecord = $this->tokenService->getTokenRecord($token);

    if (!$tokenRecord) {
      $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Sesja wygasła.'];
      header('Location: ' . url('logowanie'));
      exit();
    }

    // Używamy zunifikowanej metody walidacji siły hasła
    $errors = $this->validator->validatePasswordStrength($_POST['new_password'] ?? '', $_POST['confirm_new_password'] ?? '');

    if (!empty($errors)) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => $errors];
      header('Location: ' . url("nowe-haslo?token=$token"));
      exit();
    }

    // Walidacja przeszła - wykonujemy akcję zapisu
    $this->userModel->updatePassword($tokenRecord['user_id'], $_POST['new_password']);
    $this->tokenService->deleteTokensForUserByType($tokenRecord['user_id'], 'password_reset');

    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Hasło zostało zresetowane!'];
    header('Location: ' . url('logowanie'));
    exit();
  }
}
