<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\User;

// Dołączamy klasy z biblioteki Google
use Google\Client;
use Google\Service\Oauth2; // Potrzebne do pobrania informacji o użytkowniku
use Exception;

/**
 * Kontroler obsługujący proces logowania przez Google (OAuth 2.0).
 */
class GoogleAuthController extends BaseController
{
  private Client $googleClient;
  private UserModel $userModel;

  public function __construct()
  {
    parent::__construct();
    // Wymuszamy bycie gościem, aby zalogowani użytkownicy
    // przypadkowo nie trafili na callback
    $this->requireGuest();
    $this->userModel = new UserModel();

    // Inicjalizacja klienta Google API (bez zmian)
    $this->googleClient = new Client();
    $this->googleClient->setClientId(getenv('GOOGLE_CLIENT_ID'));
    $this->googleClient->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
    $this->googleClient->setRedirectUri(getenv('GOOGLE_REDIRECT_URI'));
    $this->googleClient->addScope('email');
    $this->googleClient->addScope('profile');
  }

  /**
   * Przekierowuje użytkownika na stronę autoryzacji Google. (bez zmian)
   */
  public function redirectToGoogle(): void
  {
    $authUrl = $this->googleClient->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
  }

  /**
   * Obsługuje odpowiedź zwrotną (callback) od Google po autoryzacji.
   */
  public function handleGoogleCallback(): void
  {
    // Sprawdź, czy Google zwróciło błąd
    if (isset($_GET['error'])) {
      // Można tu zalogować błąd: $_GET['error']
      $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Logowanie przez Google nie powiodło się. Spróbuj ponownie.'];
      header('Location: ' . url('logowanie'));
      exit();
    }

    // Sprawdź, czy otrzymaliśmy kod autoryzacyjny
    if (!isset($_GET['code'])) {
      $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Brak kodu autoryzacyjnego od Google.'];
      header('Location: ' . url('logowanie'));
      exit();
    }

    try {
      // Krok 1: Wymień kod autoryzacyjny na token dostępu
      $token = $this->googleClient->fetchAccessTokenWithAuthCode($_GET['code']);

      // Sprawdź, czy wystąpił błąd podczas pobierania tokenu
      if (isset($token['error'])) {
        throw new Exception('Błąd podczas pobierania tokenu dostępu: ' . $token['error_description']);
      }

      // Krok 2: Ustaw token w kliencie Google
      $this->googleClient->setAccessToken($token);

      // Krok 3: Pobierz dane użytkownika z Google
      $oauth2 = new Oauth2($this->googleClient);
      $googleUserInfo = $oauth2->userinfo->get();

      $googleId = $googleUserInfo->getId();
      $email = $googleUserInfo->getEmail();
      $firstName = $googleUserInfo->getGivenName();
      $lastName = $googleUserInfo->getFamilyName();

      // Krok 4: Znajdź lub utwórz użytkownika w swojej bazie danych
      $user = $this->userModel->findOrCreateGoogleUser($googleId, $email, $firstName, $lastName);

      if ($user instanceof User) {
        // Krok 5: Zaloguj użytkownika (utwórz sesję)
        session_regenerate_id(true); // Ważne dla bezpieczeństwa
        $_SESSION['user'] = $user;
        unset($_SESSION['verify_user_id']); // Sprzątanie na wszelki wypadek

        // Krok 6: Przekieruj na stronę główną
        header('Location: ' . url('/'));
        exit();
      } else {
        // Jeśli findOrCreateGoogleUser zwróci false lub null (błąd bazy danych)
        throw new Exception('Nie udało się zalogować ani zarejestrować użytkownika.');
      }
    } catch (Exception $e) {
      // Złap wszystkie wyjątki (z biblioteki Google lub nasze własne)
      error_log('Błąd logowania Google: ' . $e->getMessage()); // Zaloguj błąd
      $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Wystąpił nieoczekiwany błąd podczas logowania przez Google. Spróbuj ponownie.'];
      header('Location: ' . url('logowanie'));
      exit();
    }
  }
}
