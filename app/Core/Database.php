<?php

/**
 * Klasa Bazy Danych (Database Wrapper).
 *
 * Implementuje wzorzec projektowy Singleton, aby zagwarantować istnienie
 * tylko jednej, globalnie dostępnej instancji połączenia z bazą danych (PDO)
 * w całym cyklu życia aplikacji. Zapobiega to kosztownemu i niepotrzebnemu
 * tworzeniu wielu połączeń do bazy. Działa jako warstwa abstrakcji nad PDO,
 * upraszczając operacje i centralizując obsługę błędów.
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */
class Database
{
  /**
   * Przechowuje jedyną instancję klasy (serce wzorca Singleton).
   * @var self|null
   */
  private static ?self $instance = null;

  /**
   * Przechowuje aktywny obiekt połączenia z bazą danych (PDO).
   * @var PDO
   */
  private PDO $pdo;

  /**
   * Prywatny konstruktor zapobiegający tworzeniu instancji przez `new Database()`.
   *
   * Jest wywoływany tylko raz, wewnętrznie, przez metodę `getInstance()`.
   * Odpowiada za nawiązanie połączenia z bazą danych i ustawienie
   * kluczowych atrybutów PDO dla bezpieczeństwa i wygody.
   */
  private function __construct()
  {
    // UWAGA: W środowisku produkcyjnym dane te powinny być ładowane
    // z pliku konfiguracyjnego lub zmiennych środowiskowych, a nie
    // przechowywane bezpośrednio w kodzie.
    $host = 'localhost';
    $dbname = 'examly';
    $username = 'root';
    $password = '';

    try {
      // Utworzenie nowego obiektu PDO, który reprezentuje połączenie z bazą.
      $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

      // Ustawienie trybu obsługi błędów na PDO::ERRMODE_EXCEPTION.
      // To kluczowe dla bezpieczeństwa. Dzięki temu błędy SQL będą rzucać
      // wyjątkami, które możemy przechwycić, zamiast powodować ciche błędy.
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Ustawienie domyślnego trybu pobierania wyników na PDO::FETCH_ASSOC.
      // Dzięki temu wszystkie wyniki zapytań będą zwracane jako tablice
      // asocjacyjne (klucz => wartość), co jest bardzo wygodne w użyciu.
      $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      // W przypadku krytycznego błędu połączenia, logujemy go i kończymy
      // działanie aplikacji, wyświetlając użytkownikowi bezpieczny komunikat.
      error_log('KRYTYCZNY BŁĄD POŁĄCZENIA Z BAZĄ: ' . $e->getMessage());
      die('Wystąpił błąd serwera. Prosimy spróbować później.');
    }
  }

  /**
   * Publiczny punkt dostępu do jedynej instancji klasy (wzorzec Singleton).
   *
   * @return self Zwraca instancję klasy Database.
   */
  public static function getInstance(): self
  {
    // Krok 1: Sprawdź, czy instancja nie została jeszcze utworzona.
    if (self::$instance === null) {
      // Krok 2: Jeśli nie, utwórz ją po raz pierwszy (i jedyny).
      self::$instance = new self();
    }
    // Krok 3: Zwróć istniejącą instancję.
    return self::$instance;
  }

  /**
   * Wykonuje zapytanie i zwraca wszystkie pasujące wiersze.
   *
   * Metoda wykorzystuje przygotowane zapytania (prepared statements), co
   * stanowi podstawową ochronę przed atakami typu SQL Injection.
   *
   * @param string $sql Zapytanie SQL z placeholderami (np. `?` lub `:name`).
   * @param array<mixed> $params Tablica parametrów do bezpiecznego wstawienia w zapytanie.
   * 
   * @return array Wyniki zapytania lub pusta tablica w przypadku błędu.
   */
  public function fetchAll(string $sql, array $params = []): array
  {
    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll();
    } catch (PDOException $e) {
      error_log('Błąd zapytania (fetchAll): ' . $e->getMessage() . " SQL: $sql");
      return []; // Zwróć bezpieczną, pustą tablicę w razie błędu.
    }
  }

  /**
   * Wykonuje zapytanie i zwraca pojedynczy wiersz.
   * Idealna do pobierania jednego rekordu po jego ID.
   *
   * @param string $sql Zapytanie SQL z placeholderami.
   * @param array<mixed> $params Parametry do bindowania.
   * 
   * @return array|false Wynik zapytania, lub `false` gdy nie znaleziono rekordu lub wystąpił błąd.
   */
  public function fetch(string $sql, array $params = []): array|false
  {
    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetch();
    } catch (PDOException $e) {
      error_log('Błąd zapytania (fetch): ' . $e->getMessage() . " SQL: $sql");
      return false;
    }
  }

  /**
   * Wykonuje zapytanie modyfikujące dane (INSERT, UPDATE, DELETE).
   *
   * @param string $sql Zapytanie SQL z placeholderami.
   * @param array<mixed> $params Parametry do bindowania.
   * 
   * @return bool `true` w przypadku sukcesu, `false` w przypadku błędu.
   */
  public function execute(string $sql, array $params = []): bool
  {
    try {
      $stmt = $this->pdo->prepare($sql);
      return $stmt->execute($params);
    } catch (PDOException $e) {
      error_log('Błąd zapytania (execute): ' . $e->getMessage() . " SQL: $sql");
      return false;
    }
  }

  /**
   * Zwraca ID ostatnio wstawionego wiersza.
   * Należy wywołać zaraz po udanej operacji INSERT.
   *
   * @return int|null ID wiersza lub `null` w przypadku błędu.
   */
  public function lastInsertId(): ?int
  {
    try {
      $id = $this->pdo->lastInsertId();
      return $id ? (int) $id : null;
    } catch (PDOException $e) {
      error_log('Błąd pobierania lastInsertId: ' . $e->getMessage());
      return null;
    }
  }

  /**
   * Rozpoczyna transakcję, wyłączając tryb autocommit.
   *
   * Umożliwia wykonanie serii operacji jako jednej, atomowej całości.
   * 
   * @return bool `true` jeśli transakcja została pomyślnie rozpoczęta.
   */
  public function beginTransaction(): bool
  {
    try {
      return $this->pdo->beginTransaction();
    } catch (PDOException $e) {
      error_log('Błąd rozpoczynania transakcji: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Zatwierdza bieżącą transakcję, zapisując zmiany na stałe.
   * 
   * @return bool `true` jeśli transakcja została pomyślnie zatwierdzona.
   */
  public function commit(): bool
  {
    try {
      return $this->pdo->commit();
    } catch (PDOException $e) {
      error_log('Błąd zatwierdzania transakcji (commit): ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Wycofuje bieżącą transakcję, anulując wszystkie zmiany.
   * Należy wywołać w bloku `catch` w przypadku błędu operacji w trakcie transakcji.
   * 
   * @return bool `true` jeśli transakcja została pomyślnie wycofana.
   */
  public function rollBack(): bool
  {
    try {
      return $this->pdo->rollBack();
    } catch (PDOException $e) {
      error_log('Błąd wycofywania transakcji (rollback): ' . $e->getMessage());
      return false;
    }
  }
}
