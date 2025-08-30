<?php

/**
 * Model Użytkownika (User).
 *
 * Klasa ta stanowi jedyny, bezpieczny punkt dostępu do danych w tabeli `users`.
 * Hermetyzuje całą logikę operacji na użytkownikach, od rejestracji i logowania
 * po weryfikację i pobieranie danych. Zwraca obiekty-encje typu `User`,
 * a nie surowe tablice danych, co podnosi jakość i spójność kodu.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class UserModel
{
  /**
   * Instancja centralnego obiektu do obsługi bazy danych (Singleton).
   * @var Database
   */
  private Database $db;

  /**
   * Konstruktor modelu User.
   *
   * Pobiera instancję połączenia z bazą danych, która będzie używana
   * we wszystkich metodach tego modelu.
   */
  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Sprawdza, czy w bazie danych istnieje użytkownik o podanym adresie e-mail.
   *
   * Używane głównie w procesie walidacji formularza rejestracyjnego, aby
   * zapobiec tworzeniu zduplikowanych kont.
   *
   * @param string $email Adres e-mail do sprawdzenia.
   *
   * @return bool `true`, jeśli e-mail istnieje w bazie, w przeciwnym razie `false`.
   */
  public function checkEmail(string $email): bool
  {
    $sql = 'SELECT id FROM users WHERE email = ?';
    return $this->db->fetch($sql, [$email]) !== false;
  }

  /**
   * Rejestruje nowego użytkownika w bazie danych, bezpiecznie hashując hasło.
   *
   * Do hashowania hasła używana jest natywna funkcja `password_hash()` z flagą
   * `PASSWORD_DEFAULT`. Jest to najlepsza praktyka, ponieważ zapewnia, że
   * zawsze zostanie użyty najnowszy i najbezpieczniejszy algorytm
   * obsługiwany przez daną wersję PHP.
   *
   * @param array<string, string> $data Dane użytkownika: 'first_name', 'last_name', 'email', 'password'.
   *
   * @return bool `true`, jeśli operacja `INSERT` się powiodła, `false` w przypadku błędu.
   */
  public function register(array $data): bool
  {
    $sql = "INSERT INTO users (first_name, last_name, email, password_hash)
                VALUES (?, ?, ?, ?)";

    $params = [
      $data['first_name'],
      $data['last_name'],
      $data['email'],
      // Surowe hasło nigdy nie jest zapisywane. Zapisujemy tylko jego bezpieczny hash.
      password_hash($data['password'], PASSWORD_DEFAULT),
    ];

    return $this->db->execute($sql, $params);
  }

  /**
   * Zmienia status weryfikacji użytkownika na "zweryfikowany" (`is_verified = 1`).
   *
   * @param int $userId ID użytkownika do zweryfikowania.
   *
   * @return bool `true` w przypadku sukcesu, `false` w przypadku błędu.
   */
  public function verifyUser(int $userId): bool
  {
    $sql = 'UPDATE users SET is_verified = 1 WHERE id = ?';
    return $this->db->execute($sql, [$userId]);
  }

  /**
   * Weryfikuje dane logowania i zwraca obiekt użytkownika w przypadku sukcesu.
   *
   * Logika działania:
   * 1. Pobiera z bazy rekord użytkownika na podstawie podanego adresu e-mail.
   * 2. Jeśli użytkownik istnieje, używa funkcji `password_verify()` do bezpiecznego
   * porównania podanego hasła z hashem zapisanym w bazie.
   * 3. W przypadku sukcesu, usuwa hash hasła z danych i tworzy obiekt `User`.
   * 4. W każdym innym przypadku (brak użytkownika, błędne hasło) zwraca `null`.
   *
   * @param array<string, string> $data Tablica z danymi: 'email' i 'password'.
   *
   * @return User|null Zwraca obiekt `User` w przypadku pomyślnego logowania,
   * w przeciwnym razie `null`.
   */
  public function login(array $data): ?User
  {
    $sql = 'SELECT id, first_name, last_name, email, password_hash, is_verified, role FROM users WHERE email = ?';
    $user_data = $this->db->fetch($sql, [$data['email']]);

    // Sprawdź, czy użytkownik istnieje ORAZ czy hasło jest poprawne.
    // Użycie `password_verify` jest kluczowe - chroni przed atakami typu "timing attack".
    if ($user_data && password_verify($data['password'], $user_data['password_hash'])) {
      // Krok krytyczny dla bezpieczeństwa: nigdy nie przechowuj ani nie przekazuj
      // hasha hasła poza ten model. Usuwamy go natychmiast po weryfikacji.
      unset($user_data['password_hash']);
      return new User($user_data);
    }

    return null;
  }

  /**
   * Zwraca ID ostatnio wstawionego wiersza do tabeli `users`.
   *
   * Metoda-proxy, wywoływana bezpośrednio po `register()` w celu uzyskania
   * ID nowego użytkownika na potrzeby procesu weryfikacji e-mail.
   *
   * @return int|null ID użytkownika lub `null` w przypadku błędu.
   */
  public function getLastInsertId(): ?int
  {
    return $this->db->lastInsertId();
  }

  /**
   * Pobiera dane użytkownika na podstawie jego ID i zwraca jako obiekt.
   *
   * @param int $id ID szukanego użytkownika.
   *
   * @return User|null Zwraca obiekt `User` lub `null`, jeśli użytkownik
   * o podanym ID nie został znaleziony.
   */
  public function getUserById(int $id): ?User
  {
    $sql = 'SELECT id, first_name, last_name, email, is_verified, role FROM users WHERE id = ?';
    $user_data = $this->db->fetch($sql, [$id]);

    return $user_data ? new User($user_data) : null;
  }

  /**
   * Pobiera obiekt użytkownika na podstawie jego adresu e-mail.
   *
   * @param string $email Adres e-mail użytkownika.
   *
   * @return User|null Obiekt User lub null, jeśli nie znaleziono.
   */
  public function getUserByEmail(string $email): ?User
  {
    $sql = 'SELECT id, first_name, last_name, email, is_verified, role FROM users WHERE email = ?';
    $user_data = $this->db->fetch($sql, [$email]);

    return $user_data ? new User($user_data) : null;
  }

  /**
   * Sprawdza, czy podane hasło jest zgodne z hasłem użytkownika w bazie.
   *
   * @param int    $userId   ID użytkownika do sprawdzenia.
   * @param string $password Hasło do weryfikacji.
   *
   * @return bool `true`, jeśli hasło jest poprawne, w przeciwnym razie `false`.
   */
  public function checkPassword(int $userId, string $password): bool
  {
    $sql = 'SELECT password_hash FROM users WHERE id = ?';
    $user_data = $this->db->fetch($sql, [$userId]);

    if ($user_data && password_verify($password, $user_data['password_hash'])) {
      return true;
    }

    return false;
  }

  /**
   * Aktualizuje hasło użytkownika w bazie danych.
   *
   * @param int    $userId      ID użytkownika, któremu zmieniamy hasło.
   * @param string $newPassword Nowe, niezaszyfrowane hasło.
   *
   * @return bool `true` w przypadku sukcesu, `false` w przypadku błędu.
   */
  public function updatePassword(int $userId, string $newPassword): bool
  {
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $sql = 'UPDATE users SET password_hash = ? WHERE id = ?';
    
    return $this->db->execute($sql, [$newPasswordHash, $userId]);
  }

  /**
   * Aktualizuje imię i nazwisko użytkownika.
   *
   * @param int    $userId      ID użytkownika.
   * @param string $firstName   Nowe imię.
   * @param string $lastName    Nowe nazwisko.
   *
   * @return bool `true` w przypadku sukcesu.
   */
  public function updateName(int $userId, string $firstName, string $lastName): bool
  {
    $sql = 'UPDATE users SET first_name = ?, last_name = ? WHERE id = ?';
    if ($this->db->execute($sql, [$firstName, $lastName, $userId])) {
        // Po udanej aktualizacji w bazie, zaktualizuj obiekt użytkownika w sesji
        $_SESSION['user'] = $this->getUserById($userId);
        return true;
    }
    return false;
  }

  /**
   * Aktualizuje adres e-mail użytkownika i od razu ustawia konto jako zweryfikowane.
   * Używane po pomyślnym kliknięciu w link potwierdzający zmianę e-maila.
   *
   * @param int    $userId   ID użytkownika.
   * @param string $newEmail Nowy, zweryfikowany adres e-mail.
   *
   * @return bool `true` w przypadku sukcesu.
   */
  public function updateAndVerifyEmail(int $userId, string $newEmail): bool
  {
    // Ustawiamy is_verified na 1, ponieważ nowy e-mail został właśnie zweryfikowany
    $sql = 'UPDATE users SET email = ?, is_verified = 1 WHERE id = ?';
    return $this->db->execute($sql, [$newEmail, $userId]);
  }

/**
 * Trwale usuwa użytkownika i wszystkie powiązane z nim dane.
 * Operacja jest wykonywana w ramach transakcji, aby zapewnić spójność danych.
 */
public function deleteUser(int $userId): bool
{
    $this->db->beginTransaction();
    try {
        // Usuń powiązane dane (kolejność jest ważna)
        $this->db->execute('DELETE FROM user_progress WHERE user_id = ?', [$userId]);
        $this->db->execute('DELETE FROM user_tokens WHERE user_id = ?', [$userId]);
        // Musimy usunąć wpisy z tabeli łączącej, zanim usuniemy egzaminy
        $this->db->execute(
            'DELETE uet FROM user_exam_topics uet JOIN user_exams ue ON uet.user_exam_id = ue.id WHERE ue.user_id = ?',
            [$userId]
        );
        $this->db->execute('DELETE FROM user_exams WHERE user_id = ?', [$userId]);

        // Na końcu usuń samego użytkownika
        $this->db->execute('DELETE FROM users WHERE id = ?', [$userId]);

        return $this->db->commit();
    } catch (Exception $e) {
        $this->db->rollBack();
        error_log('Błąd podczas usuwania użytkownika: ' . $e->getMessage());
        return false;
    }
}
}
