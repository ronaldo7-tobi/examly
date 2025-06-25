<?php 
/**
 * Klasa Database zarządza połączeniem z bazą danych (singleton).
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Zwraca instancję PDO.
     * 
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if(self::$instance === null) {
            // Popraw bezpieczeństwo: odczyt danych połączenia z Apache, bez wyrzucania błędów użytkownikowi na ekran - ew error log.
            $host = 'localhost';
            $dbname = 'examly';
            $user = 'root'; 
            $pass = '';
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            } catch (PDOException $e) {
                die("Błąd połączenia z bazą danych: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
?>