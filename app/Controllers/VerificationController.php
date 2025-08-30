<?php

/**
 * Kontroler Weryfikacji Adresu E-mail.
 *
 * Odpowiada za finalny etap aktywacji konta użytkownika. Jego jedynym zadaniem
 * jest odebranie tokenu weryfikacyjnego z adresu URL, jego walidacja oraz,
 * w przypadku powodzenia, aktywacja powiązanego konta.
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */
class VerificationController extends BaseController
{
  /**
   * Instancja modelu użytkownika do interakcji z tabelą użytkowników.
   * @var UserModel
   */
  private UserModel $userModel;

  /**
   * Instancja serwisu do obsługi logiki tokenów (walidacja, usuwanie).
   * @var TokenService
   */
  private TokenService $tokenService;

  /**
   * Konstruktor kontrolera weryfikacji.
   *
   * Inicjalizuje nadrzędny BaseController oraz usługi niezbędne do
   * przeprowadzenia procesu weryfikacji: UserModel i TokenService.
   */
  public function __construct()
  {
    parent::__construct();
    $this->userModel = new UserModel();
    $this->tokenService = new TokenService();
  }

  /**
   * Główny endpoint obsługujący proces weryfikacji konta.
   *
   * Logika działania:
   * 1. Sprawdza, czy użytkownik nie jest już zalogowany i czy jest w procesie weryfikacji.
   * 2. Pobiera token z parametrów GET i sprawdza jego istnienie.
   * 3. Waliduje token w bazie danych (czy istnieje i czy nie wygasł).
   * 4. Jeśli token jest poprawny, aktywuje konto użytkownika w modelu.
   * 5. Po udanej aktywacji, usuwa zużyty token w celu zachowania higieny i bezpieczeństwa.
   * 6. Ustawia komunikat o sukcesie i przekierowuje na stronę logowania.
   * 7. W każdym przypadku błędu, wyświetla dedykowany widok z informacją.
   *
   * @return void
   */
  public function handle(): void
  {
    // Strażnik #1: Sprawdź, czy użytkownik jest zalogowany. Jeśli tak, nie ma tu czego szukać.
    if ($this->isUserLoggedIn) {
      header('Location: ' . url('/'));
      exit();
    }

    // Strażnik #2: Sprawdź, czy w adresie URL jest token.
    // To jest warunek, który blokuje dostęp przypadkowym, niezalogowanym użytkownikom.
    $token = $_GET['token'] ?? null;
    if (!$token) {
      $this->renderErrorView('Brak tokenu weryfikacyjnego.');
      return;
    }

    $tokenRecord = $this->tokenService->getTokenRecord($token);
    if (!$tokenRecord) {
      $this->renderErrorView('Token jest nieprawidłowy lub wygasł.');
      return;
    }

    $success = false;
    $userId = $tokenRecord['user_id'];
    $tokenType = $tokenRecord['type'];

    // Rozdzielamy logikę na podstawie typu tokenu
    switch ($tokenType) {
      case 'email_verify':
        $success = $this->userModel->verifyUser($userId);
        break;

      case 'email_change':
        $newEmail = $tokenRecord['token_data'];
        $success = $this->userModel->updateAndVerifyEmail($userId, $newEmail);
        break;
    }

    if ($success) {
      // Po udanej operacji, usuwamy zużyty token
      $this->tokenService->deleteTokensForUserByType($userId, $tokenType);

      $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Operacja zakończona pomyślnie! Możesz się teraz zalogować.'];
      unset($_SESSION['verify_user_id']);
      header('Location: ' . url('logowanie'));
      exit();
    } else {
      $this->renderErrorView('Wystąpił nieoczekiwany błąd podczas przetwarzania tokenu.');
    }
  }

  // ========================================================================
  // METODY POMOCNICZE (PRIVATE)
  // ========================================================================

  /**
   * Renderuje ustandaryzowany widok błędu weryfikacji.
   *
   * Centralizuje logikę wyświetlania strony błędu, aby zapewnić spójny
   * wygląd i komunikację z użytkownikiem we wszystkich scenariuszach błędów.
   *
   * @param string $message Komunikat błędu do wyświetlenia na stronie.
   * 
   * @return void
   */
  private function renderErrorView(string $message): void
  {
    $this->renderView('verify', [
      'status' => 'error',
      'message' => $message,
    ]);
  }
}
