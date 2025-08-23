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
}
