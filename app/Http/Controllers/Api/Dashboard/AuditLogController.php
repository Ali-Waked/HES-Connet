<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\AuditLogResource;
use App\Http\Responses\ApiResponse;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        // $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::with('user:id,uuid,name');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('table_name')) {
            $query->where('table_name', $request->input('table_name'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->latest()->paginate((int) $request->integer('per_page', 15));

        return $this->respond(AuditLogResource::collection($logs));
    }
}
