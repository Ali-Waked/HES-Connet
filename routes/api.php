<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Dashboard\DepartmentController;
use App\Http\Controllers\Api\Dashboard\DoctorScheduleController;
use App\Http\Controllers\Api\Dashboard\DoctorUnavailableController;
use App\Http\Controllers\Api\Dashboard\FacilityController;
use App\Http\Controllers\Api\Dashboard\OrganizationController;
use App\Http\Controllers\Api\Dashboard\OrganizationUserController;
use App\Http\Controllers\Api\Dashboard\PatientController;
use App\Http\Controllers\Api\Dashboard\PermissionController;
use App\Http\Controllers\Api\Dashboard\RoleController;
use App\Http\Controllers\Api\Dashboard\StaffController;
use App\Http\Controllers\Api\Dashboard\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/facilities/stats', [FacilityController::class, 'stats']);
    Route::apiResources([
        'organizations' => OrganizationController::class,
        'facilities' => FacilityController::class,
        'departments' => DepartmentController::class,
        'users' => UserController::class,
        'staff' => StaffController::class,
        'patients' => PatientController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
        'doctor-schedules' => DoctorScheduleController::class,
        'doctor-unavailable' => DoctorUnavailableController::class,
        'organization-users' => OrganizationUserController::class,
    ]);
});
