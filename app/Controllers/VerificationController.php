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
    // Krok 1: Sprawdzenie wstępne — zalogowany użytkownik nie wymaga weryfikacji.
    if ($this->isUserLoggedIn) {
      header('Location: /');
      exit();
    }

    // Krok 2: Sprawdzenie czy użytkownik na pewno jest w procesie weryfikacji.
    if (!isset($_SESSION['verify_user_id'])) {
      header('Location: /rejestracja');
      exit();
    }

    // Krok 2: Pobranie i walidacja istnienia tokenu w adresie URL.
    $token = $_GET['token'] ?? null;
    if (!$token) {
      $this->renderErrorView('Brak tokenu weryfikacyjnego w adresie URL.');
      return;
    }

    // Krok 3: Walidacja poprawności i ważności tokenu.
    $tokenRecord = $this->tokenService->getTokenRecord($token);
    if (!$tokenRecord || strtotime($tokenRecord['expires_at']) < time()) {
      $this->renderErrorView('Token jest nieprawidłowy lub wygasł. Spróbuj wysłać link ponownie.');
      return;
    }

    // Krok 4: Aktywacja użytkownika powiązanego z tokenem.
    if ($this->userModel->verifyUser($tokenRecord['user_id'])) {
      // Krok 5: Proces finalizacji — sprzątanie i przygotowanie do logowania.

      // Usuń zużyty token (oraz inne tego typu dla tego usera), aby nie mógł być ponownie użyty.
      $this->tokenService->deleteTokensForUserByType($tokenRecord['user_id'], 'email_verify');

      // Ustaw jednorazowy komunikat (flash message) dla strony logowania.
      $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => 'Adres e-mail został pomyślnie zweryfikowany! Możesz się teraz zalogować.',
      ];

      // Przekieruj użytkownika, aby mógł się zalogować na aktywowane konto.
      header('Location: /logowanie');
      exit();
    } else {
      // Krok 6: Obsługa błędu krytycznego — nieudana aktualizacja w bazie danych.
      $this->renderErrorView('Wystąpił nieoczekiwany błąd podczas aktywacji konta. Skontaktuj się z administratorem.');
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
