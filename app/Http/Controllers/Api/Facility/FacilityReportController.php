<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\ReportFilterRequest;
use App\Http\Resources\Facility\FacilityReportResource;
use App\Http\Responses\ApiResponse;
use App\Services\ExportService;
use App\Services\FacilityPortalService;
use App\Services\FacilityReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacilityReportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly FacilityPortalService $portalService,
        private readonly FacilityReportService $reportService,
        private readonly ExportService $exportService,
    ) {}

    public function index(ReportFilterRequest $request): JsonResponse
    {
        $facility = $this->portalService->resolveFacility($request);
        $filters = $request->validated();

        $overview = $this->reportService->getOverview($facility, $filters);
        $charts = $this->reportService->getCharts($facility, $filters);
        $tables = $this->reportService->getTables($facility, $filters);

        return $this->respond(
            new FacilityReportResource([
                'overview' => $overview,
                'charts' => $charts,
                'tables' => $tables,
                'filters_applied' => $filters,
            ])
        );
    }

    public function exportExcel(ReportFilterRequest $request): StreamedResponse
    {
        $facility = $this->portalService->resolveFacility($request);
        $filters = $request->validated();
        $data = $this->reportService->getExportData($facility, $filters);

        $filename = 'facility-report-'.now()->format('Y-m-d-His').'.csv';

        return $this->exportService->exportCsv($data, $filename);
    }

    public function exportPdf(ReportFilterRequest $request): Response
    {
        $facility = $this->portalService->resolveFacility($request);
        $filters = $request->validated();
        $data = $this->reportService->getExportData($facility, $filters);

        $filename = 'facility-report-'.now()->format('Y-m-d-His').'.html';

        return $this->exportService->exportPdf($data, $filename);
    }
}
