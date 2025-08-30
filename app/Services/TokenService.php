<?php

/**
 * Serwis Tokenów (TokenService).
 *
 * Klasa ta stanowi centralny punkt zarządzania cyklem życia tokenów
 * jednorazowych, które służą do autoryzacji wrażliwych operacji (np.
 * weryfikacji e-maila, resetowania hasła). Odpowiada za ich bezpieczne
 * generowanie, odczytywanie i usuwanie.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class TokenService
{
  /**
   * Instancja centralnego obiektu do obsługi bazy danych (Singleton).
   * @var Database
   */
  private Database $db;

  /**
   * Konstruktor serwisu TokenService.
   *
   * Pobiera instancję połączenia z bazą danych, która jest niezbędna
   * do operacji na tabeli `user_tokens`.
   */
  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function generateToken(int $userId, string $type, ?string $data = null): array|false
  {
    // WAŻNA POPRAWKA: Najpierw usuwamy stare tokeny tego typu!
    $this->deleteTokensForUserByType($userId, $type);

    $token = bin2hex(random_bytes(32));
    $code = null;

    if ($type === 'password_reset') {
      $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
      $data = $code;
    }

    $sql = "INSERT INTO user_tokens (user_id, token, type, token_data, expires_at)
            VALUES (:user_id, :token, :type, :token_data, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
    $params = [
      ':user_id' => $userId,
      ':token' => $token,
      ':type' => $type,
      ':token_data' => $data
    ];

    if ($this->db->execute($sql, $params)) {
      return ['token' => $token, 'code' => $code];
    }

    return false;
  }

  /**
   * Usuwa wszystkie tokeny danego typu dla określonego użytkownika.
   *
   * Jest to kluczowa metoda "sprzątająca", wywoływana po pomyślnym
   * zakończeniu operacji (np. po weryfikacji e-maila). Zapewnia, że
   * zużyte lub stare tokeny są usuwane, co zapobiega ich ponownemu użyciu.
   *
   * @param int $userId ID użytkownika, którego tokeny mają być usunięte.
   * @param string $type Typ tokenów do usunięcia (np. 'email_verify').
   *
   * @return void
   */
  public function deleteTokensForUserByType(int $userId, string $type): void
  {
    $sql = 'DELETE FROM user_tokens WHERE user_id = ? AND type = ?';
    $this->db->execute($sql, [$userId, $type]);
  }

  /**
   * Pobiera pełny rekord tokena z bazy na podstawie jego wartości.
   *
   * Metoda ta służy do walidacji tokena otrzymanego od użytkownika (np. z linku).
   * Kontroler, który ją wywołuje, powinien następnie sprawdzić, czy token
   * nie wygasł, porównując `expires_at` z aktualnym czasem.
   *
   * @param string $token Wartość tokena do wyszukania.
   *
   * @return array|null Zwraca tablicę asocjacyjną z danymi rekordu tokena
   * lub `null`, jeśli token nie został znaleziony.
   */
  public function getTokenRecord(string $token): ?array
  {
    $sql = 'SELECT * FROM user_tokens WHERE token = ? AND expires_at > NOW()';
    $result = $this->db->fetch($sql, [$token]);

    // Użycie operatora trójargumentowego (ternary) zapewnia, że metoda
    // zawsze zwróci `null` w przypadku braku wyniku, a nie `false`.
    return $result ?: null;
  }
}
