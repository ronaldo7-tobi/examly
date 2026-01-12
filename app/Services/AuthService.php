<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\User;
use App\Services\Validator;

/**
 * Kontroler Uwierzytelniania (Serwis Autoryzacji).
 *
 * Klasa-serwis, która orkiestruje procesy rejestracji i logowania użytkowników.
 * Nie jest to typowy kontroler MVC odbierający żądania HTTP, lecz centralna
 * usługa wykorzystywana przez inne kontrolery (np. LoginController). Odpowiada za
 * walidację danych, komunikację z modelem użytkownika oraz zarządzanie sesją.
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */
class AuthService
{
  /**
   * Instancja modelu użytkownika, zapewniająca dostęp do bazy danych.
   * @var UserModel
   */
  private UserModel $userModel;

  /**
   * Obiekt aktualnie zalogowanego użytkownika.
   * Przechowuje dane użytkownika po pomyślnym zalogowaniu.
   * @var User|null
   */
  private ?User $loggedUser;

  private ?Validator $validator;

  /**
   * Konstruktor serwisu autoryzacji.
   * Inicjalizuje instancję modelu UserModel, która jest niezbędna do
   * wszystkich operacji na danych użytkowników.
   */
  public function __construct()
  {
    $this->userModel = new UserModel();
    $this->validator = new Validator();
  }

  /**
   * Orkiestruje proces rejestracji nowego użytkownika.
   *
   * Logika działania:
   * 1. Waliduje dane wejściowe z formularza za pomocą metody `validateRegistrationData`.
   * 2. Jeśli walidacja nie powiedzie się, natychmiast zwraca tablicę z błędami.
   * 3. Jeśli dane są poprawne, zleca modelowi `UserModel` rejestrację użytkownika w bazie danych.
   * 4. W przypadku sukcesu, zapisuje ID nowego użytkownika w sesji na potrzeby
   *    późniejszego procesu weryfikacji e-mail.
   * 5. Zwraca tablicę z wynikiem operacji.
   *
   * @param array<string, string> $formData Dane z formularza rejestracji.
   * 
   * @return array<string, mixed> Wynik operacji w formacie: `['success' => bool, 'errors' => array]`.
   */
  public function register(array $formData): array
  {
    // Krok 1: Walidacja danych wejściowych.
    $errors = $this->validator->validateRegistrationData($formData);

    // Krok 2: Jeśli wystąpiły błędy, zwróć je natychmiast.
    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    // Krok 3: Przygotowanie danych i rejestracja użytkownika przez model.
    $userData = [
      'first_name' => trim($formData['first_name']),
      'last_name' => trim($formData['last_name']),
      'email' => trim($formData['email']),
      'password' => $formData['password'],
    ];
    $success = $this->userModel->register($userData);

    // Krok 4: Obsługa wyniku operacji na bazie danych.
    if ($success) {
      // Zapisz ID nowego użytkownika do sesji. Jest to kluczowe dla procesu
      // weryfikacji e-mail, który nastąpi w kolejnym kroku.
      $_SESSION['verify_user_id'] = $this->userModel->getLastInsertId();
      return ['success' => true, 'errors' => []];
    }

    // Krok 5: Obsługa błędu po stronie serwera/modelu.
    return ['success' => false, 'errors' => ['Rejestracja nie powiodła się z powodu błędu serwera.']];
  }

  /**
   * Orkiestruje proces logowania użytkownika.
   *
   * Logika działania:
   * 1. Sprawdza, czy e-mail i hasło zostały podane.
   * 2. Zleca modelowi `UserModel` próbę zalogowania na podstawie przekazanych danych.
   * 3. Jeśli logowanie się powiedzie, model zwraca obiekt `User`.
   * 4. Sprawdza, czy konto użytkownika zostało zweryfikowane e-mailowo.
   * 5. Jeśli nie, zwraca specjalny status błędu `not_verified` i zapisuje ID
   *    użytkownika w sesji, aby umożliwić ponowne wysłanie linku weryfikacyjnego.
   * 6. Jeśli konto jest zweryfikowane, zapisuje obiekt użytkownika w sesji.
   * 7. W przypadku niepowodzenia logowania, zwraca ogólny błąd.
   *
   * @param array<string, string> $formData Dane z formularza logowania ('email', 'password').
   * 
   * @return array<string, mixed> Wynik operacji. Może zawierać klucze `success`, `errors` lub `error_type`.
   */
  public function login(array $formData): array
  {
    // Krok 1: Podstawowa walidacja istnienia danych.
    $email = $formData['email'] ?? '';
    $password = $formData['password'] ?? '';
    if (empty($email) || empty($password)) {
      return ['success' => false, 'errors' => ['Pola e-mail i hasło są wymagane.']];
    }

    // Krok 2: Próba zalogowania przez model. Model zwraca obiekt User lub null.
    $user = $this->userModel->login($formData);

    // Krok 3: Obsługa różnych scenariuszy po próbie logowania.
    if ($user instanceof User) {
      // Scenariusz A: Użytkownik istnieje, ale jego konto nie jest zweryfikowane.
      if (!$user->isVerified()) {
        // Zapisz ID do sesji, aby umożliwić ponowne wysłanie e-maila weryfikacyjnego.
        $_SESSION['verify_user_id'] = $user->getId();
        // Zwróć specjalny typ błędu, który frontend może zinterpretować i wyświetlić odpowiedni komunikat.
        return ['success' => false, 'error_type' => 'not_verified'];
      }

      // Scenariusz B: Sukces! Użytkownik poprawnie zalogowany i zweryfikowany.
      $this->loggedUser = $user;
      $_SESSION['user'] = $this->loggedUser;
      return ['success' => true];
    }

    // Scenariusz C: Niepowodzenie. Model zwrócił null, co oznacza błędne dane.
    return ['success' => false, 'errors' => ['Niepoprawny e-mail lub hasło.']];
  }
}
