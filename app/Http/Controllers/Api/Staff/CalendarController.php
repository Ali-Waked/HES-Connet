<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CalendarIndexRequest;
use App\Http\Resources\CalendarEventResource;
use App\Models\Facility;
use App\Services\CalendarService;
use App\Services\UuidResolver;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarService $service,
        private readonly UuidResolver $uuidResolver,
    ) {}

    public function index(CalendarIndexRequest $request): JsonResponse
    {
        $staff = $request->user()->staff()->firstOrFail();

        $facility = null;
        if ($facilityUuid = $request->query('facility_uuid')) {
            $facility = UuidResolver::model(Facility::class, $facilityUuid);
        }

        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->query('week_start'))->startOfDay()
            : Carbon::now()->startOfWeek();

        $weekEnd = $request->has('week_end')
            ? Carbon::parse($request->query('week_end'))->endOfDay()
            : Carbon::now()->endOfWeek();

        $events = $this->service->index($staff, $facility, $weekStart, $weekEnd);

        return response()->json([
            'events' => CalendarEventResource::collection($events),
        ]);
    }
}
