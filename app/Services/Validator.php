<?php

namespace App\Services;

use App\Models\UserModel;

/**
 * Serwis Walidacji - odpowiada wyłącznie za weryfikację poprawności danych.
 */
class Validator
{
  private UserModel $userModel;

  public function __construct()
  {
    $this->userModel = new UserModel();
  }

  /**
   * Waliduje dane rejestracyjne.
   */
  public function validateRegistrationData(array $formData): array
  {
    $errors = [];

    // 1. Walidacja Imienia i Nazwiska
    $nameErrors = $this->validateNames($formData['first_name'] ?? '', $formData['last_name'] ?? '');
    $errors = array_merge($errors, $nameErrors);

    // 2. Walidacja E-mail
    $email = trim($formData['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Podano niepoprawny format adresu e-mail.';
    } elseif ($this->userModel->checkEmail($email)) {
      $errors[] = 'Konto z podanym adresem e-mail już istnieje.';
    }

    // 3. Walidacja Hasła (siła i zgodność)
    $passwordErrors = $this->validatePasswordStrength(
      $formData['password'] ?? '',
      $formData['confirm_password'] ?? ''
    );
    $errors = array_merge($errors, $passwordErrors);

    return $errors;
  }

  /**
   * Waliduje siłę hasła (reużywalne w rejestracji, resecie i ustawieniach).
   */
  public function validatePasswordStrength(string $password, string $confirm): array
  {
    $errors = [];

    if (strlen($password) < 8) {
      $errors[] = 'Hasło musi mieć co najmniej 8 znaków.';
    } elseif ($this->isPasswordPwned($password)) {
      $errors[] = 'To hasło wyciekło do sieci w publicznych bazach danych. Wybierz inne dla własnego bezpieczeństwa.';
    } else {
      if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Hasło musi zawierać wielką literę.';
      if (!preg_match('/[a-z]/', $password)) $errors[] = 'Hasło musi zawierać małą literę.';
      if (!preg_match('/[0-9]/', $password)) $errors[] = 'Hasło musi zawierać cyfrę.';
      if (!preg_match('/[\W_]/', $password)) $errors[] = 'Hasło musi zawierać znak specjalny.';
    }

    if ($password !== $confirm) {
      $errors[] = 'Podane hasła nie są identyczne.';
    }

    return $errors;
  }

  /**
   * Zaawansowana walidacja imienia i nazwiska.
   * @return array Lista błędów.
   */
  public function validateNames(string $firstName, string $lastName): array
  {
    $errors = [];

    // Reguła: Litery (Unicode), dopuszczalny pojedynczy łącznik lub spacja między literami.
    // Zapobiega: "Jan  Paweł", "Kowalska--Nowak", "Jan123".
    $nameRegex = '/^[\p{L}]+(?:[\s-][\p{L}]+)*$/u';

    $fields = [
      'Imię' => trim($firstName),
      'Nazwisko' => trim($lastName)
    ];

    foreach ($fields as $label => $value) {
      if (mb_strlen($value) < 2) {
        $errors[] = "{$label} jest za krótkie (minimum 2 znaki).";
      } elseif (mb_strlen($value) > 50) {
        $errors[] = "{$label} jest za długie (maksimum 50 znaków).";
      } elseif (!preg_match($nameRegex, $value)) {
        $errors[] = "{$label} zawiera niedozwolone znaki. Użyj tylko liter.";
      } elseif (!preg_match('/^\p{Lu}/u', $value)) {
        // Sprawdza czy zaczyna się od wielkiej litery (Unicode-aware)
        $errors[] = "{$label} musi zaczynać się od wielkiej litery.";
      }
    }

    return $errors;
  }

  private function isPasswordPwned(string $password): bool
  {
    $sha1 = strtoupper(sha1($password));
    $prefix = substr($sha1, 0, 5);
    $suffix = substr($sha1, 5);

    $ch = curl_init("https://api.pwnedpasswords.com/range/$prefix");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response && str_contains($response, $suffix);
  }
}
