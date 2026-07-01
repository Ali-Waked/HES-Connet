<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Resources\LookupResource;
use App\Models\Department;
use App\Models\Facility;
use App\Models\Position;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class StaffLookupController extends Controller
{
    public function __invoke(Facility $facility): JsonResponse
    {
        return response()->json([
            'data' => [
                'positions' => LookupResource::collection(
                    Position::query()
                        ->where('is_active', true)
                        ->orderBy('name->en')
                        ->get()
                ),

                'departments' => LookupResource::collection(
                    Department::where('facility_id', $facility->id)
                        ->where('is_active', true)
                        ->orderBy('name->en')
                        ->get()
                ),

                'roles' => LookupResource::collection(
                    Role::query()
                        ->where('scope', 'facility')
                        ->where('is_active', true)
                        ->orderBy('name->en')
                        ->get()
                ),
            ],
        ]);
    }
}
