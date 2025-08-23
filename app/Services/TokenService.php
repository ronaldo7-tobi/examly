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

  /**
   * Generuje nowy, kryptograficznie bezpieczny token i zapisuje go w bazie.
   *
   * Do generowania tokena używana jest funkcja `random_bytes()`, która jest
   * standardem bezpieczeństwa w nowoczesnym PHP. Gwarantuje ona, że token
   * jest nieprzewidywalny i odporny na próby odgadnięcia. Tokeny są zapisywane
   * z jednogodzinnym terminem ważności, co ogranicza ryzyko w razie ich wycieku.
   *
   * @param int $userId ID użytkownika, dla którego tworzony jest token.
   * @param string $type Typ tokena (np. 'email_verify', 'password_reset'),
   * pozwalający na rozróżnienie ich przeznaczenia.
   *
   * @return string Wygenerowany, 64-znakowy token w formacie heksadecymalnym.
   */
  public function generateToken(int $userId, string $type): string
  {
    // Krok 1: Wygeneruj 32 bajty kryptograficznie bezpiecznych, losowych danych
    // i przekonwertuj je na 64-znakowy ciąg heksadecymalny.
    $token = bin2hex(random_bytes(32));

    // Krok 2: Zapisz token w bazie danych z datą wygaśnięcia ustawioną na 1 godzinę w przyszłości.
    $sql = "INSERT INTO user_tokens (user_id, token, type, expires_at)
                VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))";

    $this->db->execute($sql, [$userId, $token, $type]);

    // Krok 3: Zwróć wygenerowany token, aby mógł być np. wysłany w e-mailu.
    return $token;
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
    $sql = 'SELECT * FROM user_tokens WHERE token = ?';
    $result = $this->db->fetch($sql, [$token]);

    // Użycie operatora trójargumentowego (ternary) zapewnia, że metoda
    // zawsze zwróci `null` w przypadku braku wyniku, a nie `false`.
    return $result ?: null;
  }
}