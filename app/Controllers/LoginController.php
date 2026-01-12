<?php

namespace App\Controllers;

use App\Services\AuthService;

/**
 * Kontroler Strony Logowania.
 *
 * Odpowiada za logikę związaną z wyświetlaniem i obsługą formularza logowania.
 * Działa jako "klej" między żądaniem HTTP użytkownika, serwisem uwierzytelniania
 * a widokiem HTML. Dziedziczy po `BaseController`, aby mieć dostęp do
 * globalnego stanu aplikacji (np. statusu logowania).
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class LoginController extends BaseController
{
  /**
   * Serwis uwierzytelniania, który zawiera główną logikę biznesową logowania.
   * @var AuthService
   */
  private AuthService $auth;

  /**
   * Konstruktor Kontrolera Logowania.
   * Wywołuje konstruktor klasy nadrzędnej (`BaseController`) w celu
   * inicjalizacji sesji i sprawdzenia statusu logowania, a następnie
   * tworzy instancję serwisu `AuthController`.
   */
  public function __construct()
  {
    parent::__construct();
    $this->requireGuest();
    $this->auth = new AuthService();
  }

  /**
   * Główna metoda kontrolera, zarządzająca całą logiką strony logowania.
   * 2. Jeśli żądanie jest typu POST, przetwarza dane z formularza, wywołując serwis `auth->login()`.
   * 3. W przypadku sukcesu: regeneruje ID sesji (ze względów bezpieczeństwa) i przekierowuje na stronę główną.
   * 4. W przypadku błędu: zbiera komunikaty o błędach.
   * 5. Na koniec renderuje widok `login.php`, przekazując do niego ewentualne błędy
   *    oraz dane wprowadzone przez użytkownika w formularzu.
   * 
   * @return void
   */
  public function handleRequest(): void
  {
    $errors = [];
    $formData = [];

    // 2. Obsłuż dane z formularza
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $formData = $_POST;
      $result = $this->auth->login($formData);

      // 3. Reakcja na sukces
      if ($result['success']) {
        session_regenerate_id(true); // Zabezpieczenie przed atakiem session fixation
        unset($_SESSION['verify_user_id']); // Sprzątanie po procesie rejestracji

        header('Location: ' . url('/'));
        exit();
      } else {
        // 4. Reakcja na błąd
        if (isset($result['error_type']) && $result['error_type'] === 'not_verified') {
          // Stwórz link, który uruchomi ponowne wysłanie e-maila
          $resendLink = '<a href="' . url('autoryzacja-email?send=true') . '">Kliknij tutaj, aby wysłać link weryfikacyjny ponownie.</a>';
          $errors[] = 'Konto nie zostało jeszcze zweryfikowane. ' . $resendLink;
        } else {
          // W przeciwnym razie, obsłuż standardowe błędy
          $errors = $result['errors'];
        }
      }
    }

    // 5. Renderowanie widoku
    $this->renderView('login', [
      'errors' => $errors,
      'formData' => $formData,
    ]);
  }
}
