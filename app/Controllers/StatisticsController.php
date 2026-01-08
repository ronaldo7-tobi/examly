<?php 

namespace App\Controllers;
use App\Services\StatisticsService;

class StatisticsController extends BaseController
{
  
  private StatisticsService $statisticsService;

  public function __construct()
  {
    $this->requireAuth();
    $this->statisticsService = new StatisticsService();
  }

  public function showStatisticsPage(): void
  {
    $this->renderView('statistics');
  }
}