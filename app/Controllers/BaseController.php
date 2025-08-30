<?php

/**
 * Kontroler Bazowy.
 *
 * Klasa nadrzędna dla wszystkich innych kontrolerów w aplikacji.
 * Odpowiada za logikę, która musi być wykonana przy każdym żądaniu,
 * np. startowanie sesji, sprawdzanie statusu logowania i renderowanie widoków.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class BaseController
{
  /**
   * @var bool Status zalogowania użytkownika.
   */
  protected bool $isUserLoggedIn = false;

  /**
   * @var User|null Obiekt zalogowanego użytkownika lub null.
   */
  protected ?User $currentUser = null;

  /**
   * Konstruktor uruchamiany przy każdym żądaniu do dowolnego kontrolera,
   * który po nim dziedziczy.
   */
  public function __construct()
  {
    // Sprawdź status logowania i ustaw właściwości
    if (isset($_SESSION['user']) && $_SESSION['user'] instanceof User) {
      $this->isUserLoggedIn = true;
      $this->currentUser = $_SESSION['user'];
    }
  }

  /**
   * Renderuje widok, przekazując do niego dane.
   *
   * @param string $viewName Nazwa pliku widoku (bez .php).
   * @param array<string, mixed> $data Tablica danych do przekazania do widoku.
   * 
   * @return void
   */
  protected function renderView(string $viewName, array $data = []): void
  {
    // Udostępnij dane z kontrolera bazowego każdemu widokowi
    $isUserLoggedIn = $this->isUserLoggedIn;
    $currentUser = $this->currentUser;

    // Zamień klucze tablicy na zmienne (np. $data['errors'] -> $errors)
    extract($data);

    // Dołącz plik widoku
    include __DIR__ . "/../../views/{$viewName}.php";
  }

  /**
   * Wymusza, aby użytkownik NIE był zalogowany (był gościem).
   * Jeśli jest zalogowany, przekierowuje go na stronę główną.
   * 
   * @return void
   */
  protected function requireGuest(): void
  {
    if ($this->isUserLoggedIn) {
      header('Location: ' . url('/'));
      exit();
    }
  }

  /**
   * Wymusza, aby użytkownik był zalogowany.
   * Jeśli nie jest, przekierowuje go na stronę logowania.
   * 
   * @return void
   */
  protected function requireAuth(): void
  {
    if (!$this->isUserLoggedIn) {
      header('Location: ' . url('logowanie'));
      exit();
    }
  }
}
