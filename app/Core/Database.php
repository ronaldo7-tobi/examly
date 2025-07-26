<?php
class Database {
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct() {
        $host = 'localhost';
        $dbname = 'examly';
        $username = 'root';
        $password = '';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Ten błąd jest krytyczny - aplikacja nie może działać bez bazy.
            error_log("KRYTYCZNY BŁĄD POŁĄCZENIA Z BAZĄ: " . $e->getMessage());
            // Zakończ działanie i wyświetl ogólny komunikat
            die("Wystąpił błąd serwera. Prosimy spróbować później.");
        }
    }

    /**
     * Zapewnia jedną instancję klasy Database (Singleton).
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Wykonuje zapytanie i zwraca wszystkie pasujące wiersze.
     *
     * @param string $sql Zapytanie SQL z placeholderami (np. ?, :name).
     * @param array<mixed> $params Parametry do bindowania.
     * @return array Wyniki zapytania lub pusta tablica w przypadku błędu.
     */
    public function fetchAll(string $sql, array $params = []): array {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Błąd zapytania (fetchAll): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Wykonuje zapytanie i zwraca jeden pasujący wiersz.
     *
     * @param string $sql Zapytanie SQL z placeholderami.
     * @param array<mixed> $params Parametry do bindowania.
     * @return array|false Wynik zapytania lub false w przypadku błędu/braku wyników.
     */
    public function fetch(string $sql, array $params = []): array|false {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Błąd zapytania (fetch): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Wykonuje zapytanie, które nie zwraca wyników (INSERT, UPDATE, DELETE).
     *
     * @param string $sql Zapytanie SQL z placeholderami.
     * @param array<mixed> $params Parametry do bindowania.
     * @return bool True w przypadku sukcesu, false w przypadku błędu.
     */
    public function execute(string $sql, array $params = []): bool {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Błąd zapytania (execute): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Zwraca ID ostatnio wstawionego wiersza.
     *
     * @return int|null ID wiersza lub null w przypadku błędu.
     */
    public function lastInsertId(): ?int
    {
        // Ta metoda jest wyjątkiem - musi być wywołana na oryginalnym obiekcie PDO.
        try {
            $id = $this->pdo->lastInsertId();
            return $id ? (int)$id : null;
        } catch (PDOException $e) {
            error_log("Błąd pobierania lastInsertId: " . $e->getMessage());
            return null;
        }
    }
}