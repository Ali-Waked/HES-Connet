<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\OrganizationStatsService;
use Illuminate\Http\JsonResponse;

class OrganizationStatsController extends Controller
{
    public function __construct(private readonly OrganizationStatsService $organizationStatsService) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->organizationStatsService->getStats()
        );
    }
}
