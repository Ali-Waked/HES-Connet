<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ReportFilterRequest;
use App\Http\Resources\Dashboard\DashboardReportResource;
use App\Http\Responses\ApiResponse;
use App\Services\DashboardReportService;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardReportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DashboardReportService $reportService,
        private readonly ExportService $exportService,
    ) {}

    public function index(ReportFilterRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $overview = $this->reportService->getOverview($filters);
        $charts = $this->reportService->getCharts($filters);
        $tables = $this->reportService->getTables($filters);

        return $this->respond(
            new DashboardReportResource([
                'overview' => $overview,
                'charts' => $charts,
                'tables' => $tables,
                'filters_applied' => $filters,
            ])
        );
    }

    public function exportExcel(ReportFilterRequest $request): StreamedResponse
    {
        $filters = $request->validated();
        $data = $this->reportService->getExportData($filters);

        $filename = 'dashboard-report-'.now()->format('Y-m-d-His').'.csv';

        return $this->exportService->exportCsv($data, $filename);
    }

    public function exportPdf(ReportFilterRequest $request): Response
    {
        $filters = $request->validated();
        $data = $this->reportService->getExportData($filters);

        $filename = 'dashboard-report-'.now()->format('Y-m-d-His').'.html';

        return $this->exportService->exportPdf($data, $filename);
    }
}
