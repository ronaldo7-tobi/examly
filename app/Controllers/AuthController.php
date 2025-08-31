<?php

/**
 * Kontroler Uwierzytelniania (Serwis Autoryzacji).
 *
 * Klasa-serwis, która orkiestruje procesy rejestracji i logowania użytkowników.
 * Nie jest to typowy kontroler MVC odbierający żądania HTTP, lecz centralna
 * usługa wykorzystywana przez inne kontrolery (np. LoginController). Odpowiada za
 * walidację danych, komunikację z modelem użytkownika oraz zarządzanie sesją.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class AuthController
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
  private ?User $loggedUser = null;

  /**
   * Konstruktor serwisu autoryzacji.
   * Inicjalizuje instancję modelu UserModel, która jest niezbędna do
   * wszystkich operacji na danych użytkowników.
   */
  public function __construct()
  {
    $this->userModel = new UserModel();
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
    $errors = $this->validateRegistrationData($formData);

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
   * użytkownika w sesji, aby umożliwić ponowne wysłanie linku weryfikacyjnego.
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

  // ========================================================================
  // METODY POMOCNICZE (PRIVATE)
  // ========================================================================

  /**
   * Waliduje dane z formularza rejestracyjnego.
   *
   * Sprawdza poprawność imienia, nazwiska, formatu i unikalności adresu e-mail
   * oraz zgodność i minimalną długość haseł.
   *
   * @param array<string, string> $formData Dane z formularza.
   * 
   * @return array<string> Tablica zawierająca komunikaty o błędach. Pusta, jeśli dane są poprawne.
   */
  private function validateRegistrationData(array $formData): array
  {
    $errors = [];

    $firstName = trim($formData['first_name'] ?? '');
    $lastName = trim($formData['last_name'] ?? '');
    $email = trim($formData['email'] ?? '');
    $password = $formData['password'] ?? '';
    $confirmPassword = $formData['confirm_password'] ?? '';

    // Walidacja imienia
    if (mb_strlen($firstName) < 2 || !preg_match('/^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]{1,}$/u', $firstName)) {
      $errors[] = 'Imię musi zaczynać się z dużej litery i mieć co najmniej 2 znaki.';
    }
    // Walidacja nazwiska
    if (mb_strlen($lastName) < 2 || !preg_match('/^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]{1,}$/u', $lastName)) {
      $errors[] = 'Nazwisko musi zaczynać się z dużej litery i mieć co najmniej 2 znaki.';
    }
    // Walidacja adresu e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Podano niepoprawny format adresu e-mail.';
    } elseif ($this->userModel->checkEmail($email)) {
      $errors[] = 'Konto z podanym adresem e-mail już istnieje.';
    }
    // Walidacja hasła
    if (strlen($password) < 8) {
      $errors[] = 'Hasło musi mieć co najmniej 8 znaków.';
    } elseif ($this->isPasswordPwned($password)) { // <-- NOWY WARUNEK
      $errors[] = 'To hasło jest niebezpieczne, ponieważ pojawiło się w publicznym wycieku danych. Proszę, użyj innego.';
    } else {
      if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Hasło musi zawierać co najmniej jedną wielką literę.';
      }
      if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Hasło musi zawierać co najmniej jedną małą literę.';
      }
      if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Hasło musi zawierać co najmniej jedną cyfrę.';
      }
      // Opcjonalnie: wymóg znaku specjalnego
      if (!preg_match('/[\W_]/', $password)) {
        $errors[] = 'Hasło musi zawierać co najmniej jeden znak specjalny (np. !, @, #, ?).';
      }
    }
    // Walidacja potwierdzenia hasła
    if ($password !== $confirmPassword) {
      $errors[] = 'Podane hasła nie są identyczne.';
    }

    return $errors;
  }

  /**
   * Sprawdza, czy hasło znajduje się w bazie znanych wycieków danych (Have I Been Pwned).
   * 
   * @param string $password Hasło do sprawdzenia.
   * 
   * @return bool True, jeśli hasło wyciekło, w przeciwnym razie false.
   */
  private function isPasswordPwned(string $password): bool
  {
    $sha1Password = sha1($password);
    $prefix = substr($sha1Password, 0, 5);
    $suffix = substr($sha1Password, 5);

    $ch = curl_init("https://api.pwnedpasswords.com/range/{$prefix}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if (empty($response)) {
      return false;
    }

    $hashes = explode("\r\n", $response);
    foreach ($hashes as $hash) {
      $parts = explode(':', $hash);
      if (strtoupper($parts[0]) === strtoupper($suffix)) {
        return true; // Znaleziono hasło w wycieku
      }
    }

    return false;
  }
}
