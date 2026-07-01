<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\DashboardResource;
use App\Http\Responses\ApiResponse;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function __invoke(): JsonResponse
    {
        $cards = $this->dashboardService->getCards();
        $growth = $this->dashboardService->getGrowthPercentages();
        $recent = $this->dashboardService->getRecentActivity();
        $charts = $this->dashboardService->getCharts();

        return $this->respond(
            new DashboardResource([
                'cards' => $cards,
                'growth_percentages' => $growth,
                'recent_activity' => $recent,
                'charts' => $charts,
            ])
        );
    }
}
