<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Services\FacilityPortalService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly FacilityPortalService $portal_service,
    ) {}

    public function index(Request $request)
    {
        $notifications = $this->portal_service->notificationsPaginate(
            $request->user(),
            (int) $request->get('per_page', 15),
        );

        return response()->json($notifications);
    }
}
