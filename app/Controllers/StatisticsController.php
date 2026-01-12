<?php

namespace App\Controllers;

use App\Services\StatisticsService;

class StatisticsController extends BaseController
{
  private StatisticsService $statsService;

  public function __construct()
  {
    $this->statsService = new StatisticsService();
  }

  /**
   * Wyświetla stronę statystyk użytkownika.
   */
  public function index(): void
  {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
      header('Location: /login');
      exit;
    }

    // Pobranie danych z zrefaktoryzowanego serwisu
    $data = [
      'history'  => $this->statsService->getUserExamsData($userId)
    ];

    $this->renderView('statistics', $data);
  }
}
