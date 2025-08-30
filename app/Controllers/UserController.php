<?php

/**
 * Kontroler Akcji Zalogowanego Użytkownika.
 *
 * Obsługuje strony i akcje dostępne tylko dla uwierzytelnionych
 * użytkowników, takie jak wyświetlanie statystyk czy wylogowanie.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class UserController extends BaseController
{
  /**
   * Konstruktor, który zabezpiecza wszystkie akcje w tym kontrolerze.
   *
   * Jeśli użytkownik nie jest zalogowany, zostaje przekierowany na stronę logowania.
   */
  public function __construct()
  {
    parent::__construct();

    if (!$this->isUserLoggedIn) {
      header('Location: ' . url('logowanie'));
      exit();
    }
  }

  /**
   * Wyświetla stronę ze statystykami użytkownika.
   *
   * @return void
   */
  public function showStatistics(): void
  {
    $this->renderView('statistics');
  }

  /**
   * Wylogowuje użytkownika.
   *
   * Niszczy bieżącą sesję i przekierowuje na stronę logowania.
   *
   * @return void
   */
  public function logout(): void
  {
    session_unset(); // Usuwa wszystkie zmienne sesyjne
    session_destroy(); // Niszczy sesję

    header('Location: ' . url('logowanie'));
    exit();
  }
}
