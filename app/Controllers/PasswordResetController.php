<?php

class PasswordResetController extends BaseController
{
  private UserModel $userModel;
  private TokenService $tokenService;

  public function __construct()
  {
    parent::__construct();
    $this->userModel = new UserModel();
    $this->tokenService = new TokenService();
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
      // Użytkownik istnieje, generujemy token i wysyłamy e-mail
      $token = $this->tokenService->generateToken($user->getId(), 'password_reset');
      $resetLink = url("nowe-haslo?token=$token");

      $body = '<p>Witaj,</p><p>Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta. Kliknij poniższy link, aby ustawić nowe hasło:</p>';
      $body .= "<p><a href='$resetLink'>$resetLink</a></p>";
      $body .= '<p>Jeśli to nie ty prosiłeś o zmianę hasła, zignoruj tę wiadomość.</p>';

      $mailer = new Mailer();
      if ($mailer->send($user->getEmail(), 'Reset hasła w serwisie Examly', $body)) {
        $_SESSION['password_reset_sent'] = time();
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

  /**
   * Obsługuje zmianę hasła na nowe.
   */
  public function handleNewPasswordRequest(): void
  {
    $token = $_POST['token'] ?? null;
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_new_password'] ?? '';

    $tokenRecord = $this->tokenService->getTokenRecord($token);

    if (!$token || !$tokenRecord || strtotime($tokenRecord['expires_at']) < time()) {
      $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Sesja zmiany hasła wygasła. Spróbuj ponownie.'];
      header('Location: ' . url('logowanie'));
      exit();
    }

    if (strlen($newPassword) < 6) {
      $errors[] = 'Nowe hasło musi mieć co najmniej 6 znaków.';
    } elseif ($newPassword !== $confirmPassword) {
      $errors[] = 'Podane hasła nie są identyczne.';
    }

    if (!empty($errors)) {
      $_SESSION['flash_message'] = ['type' => 'error', 'errors' => $errors];
      header('Location: ' . url("nowe-haslo?token=$token"));
      exit();
    }

    // Wszystko w porządku, zmień hasło
    $this->userModel->updatePassword($tokenRecord['user_id'], $newPassword);
    $this->tokenService->deleteTokensForUserByType($tokenRecord['user_id'], 'password_reset');

    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Twoje hasło zostało zmienione! Możesz się teraz zalogować.'];
    header('Location: ' . url('logowanie'));
    exit();
  }
}
