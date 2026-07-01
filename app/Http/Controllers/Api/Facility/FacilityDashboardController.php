<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Resources\Facility\FacilityDashboardResource;
use App\Http\Responses\ApiResponse;
use App\Services\FacilityPortalService;
use App\Services\NewFacilityDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityDashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly FacilityPortalService $portalService,
        private readonly NewFacilityDashboardService $dashboardService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $facility = $this->portalService->resolveFacility($request);

        $cards = $this->dashboardService->getCards($facility);
        $growth = $this->dashboardService->getGrowthPercentages($facility);
        $recent = $this->dashboardService->getRecentData($facility);
        $charts = $this->dashboardService->getCharts($facility);

        return $this->respond(
            new FacilityDashboardResource([
                'cards' => $cards,
                'growth_percentages' => $growth,
                'recent_data' => $recent,
                'charts' => $charts,
            ])
        );
    }
}
