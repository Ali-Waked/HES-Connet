<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Dashboard\CategoryController;
use App\Http\Controllers\Api\Dashboard\DepartmentController;
use App\Http\Controllers\Api\Dashboard\AppointmentController;
use App\Http\Controllers\Api\Dashboard\PrescriptionController;
use App\Http\Controllers\Api\Dashboard\StaffScheduleController;
use App\Http\Controllers\Api\Dashboard\StaffUnavailabilityController;
use App\Http\Controllers\Api\Dashboard\FacilityController;
use App\Http\Controllers\Api\Dashboard\OrganizationController;
use App\Http\Controllers\Api\Dashboard\OrganizationUserController;
use App\Http\Controllers\Api\Dashboard\PatientController;
use App\Http\Controllers\Api\Dashboard\PermissionController;
use App\Http\Controllers\Api\Dashboard\RoleController;
use App\Http\Controllers\Api\Dashboard\StaffController;
use App\Http\Controllers\Api\Dashboard\TagController;
use App\Http\Controllers\Api\Dashboard\UserController;
use App\Http\Controllers\Api\Dashboard\ArticleController;
use App\Http\Controllers\Api\Dashboard\UserManagementController;
use App\Http\Controllers\Api\CityLookupController;
use App\Http\Controllers\Api\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\Dashboard\PositionController;
use App\Http\Controllers\Api\Dashboard\StaffPositionController;
use App\Http\Controllers\Api\Public\ArticleController as PublicArticleController;
use App\Http\Controllers\Api\Public\ContactMessageController as PublicContactMessageController;
use App\Http\Controllers\Api\Public\DoctorController as PublicDoctorController;
use App\Http\Controllers\Api\Public\FacilityController as PublicFacilityController;
use App\Http\Controllers\Api\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Api\Public\PageController as PublicPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    $user = $request->user()->load('role.permissions');

    return [
        'user' => new \App\Http\Resources\UserResource($user),
        'roles' => [$user->role?->name['en'] ?? null],
        'permissions' => $user->allPermissions()->pluck('key'),
    ];
})->middleware('auth:sanctum');

Route::post('/contact-us', [PublicContactMessageController::class, 'store']);

Route::get('/cities/list', [CityLookupController::class, 'index']);

Route::prefix('doctors')->group(function () {
    Route::get('/', [PublicDoctorController::class, 'index']);
    Route::get('{staff}', [PublicDoctorController::class, 'show']);
});

Route::prefix('facilities')->group(function () {
    Route::get('/', [PublicFacilityController::class, 'index']);
    Route::get('{facility}', [PublicFacilityController::class, 'show']);
});

Route::prefix('articles')->group(function () {
    Route::get('/', [PublicArticleController::class, 'index']);
    Route::get('{article}', [PublicArticleController::class, 'show']);
});

Route::get('/home', PublicHomeController::class);

Route::get('/pages/{slug}', [PublicPageController::class, 'show']);

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/facilities/stats', [FacilityController::class, 'stats']);
    Route::get('/articles/stats', [ArticleController::class, 'stats']);
    Route::get('/departments/stats', [DepartmentController::class, 'stats']);
    Route::get('/tags/stats', [TagController::class, 'stats']);
    Route::get('/categories/stats', [CategoryController::class, 'stats']);
    Route::get('/positions/stats', [PositionController::class, 'stats']);
    Route::get('positions/lookup', [PositionController::class, 'lookup']);
    Route::get('staff-positions/lookup', [StaffPositionController::class, 'lookup']);
    Route::get('departments/lookup', [DepartmentController::class, 'lookup']);
    Route::get('/users/stats', [UserController::class, 'stats']);
    Route::get('/permissions/stats', [PermissionController::class, 'stats']);
    Route::get('/roles/stats', [RoleController::class, 'stats']);
    Route::post('/staff/check-email', [StaffController::class, 'checkEmail']);

    Route::prefix('cities')->group(function () {
        require base_path('routes/api/dashboard/cities.php');
    });

    Route::prefix('pages')->group(function () {
        require base_path('routes/api/dashboard/pages.php');
    });

    Route::prefix('staff-positions')->group(function () {
        require base_path('routes/api/dashboard/staff-positions.php');
    });

    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);

    Route::apiResources([
        'organizations' => OrganizationController::class,
        'facilities' => FacilityController::class,
        'departments' => DepartmentController::class,
        'users' => UserController::class,
        'staff' => StaffController::class,
        'patients' => PatientController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
        'categories' => CategoryController::class,
        'tags' => TagController::class,
        'articles' => ArticleController::class,
        'appointments' => AppointmentController::class,
        'staff-schedules' => StaffScheduleController::class,
        'staff-unavailabilities' => StaffUnavailabilityController::class,
        'prescriptions' => PrescriptionController::class,
        'organization-users' => OrganizationUserController::class,
        'positions' => PositionController::class,
    ]);

    // Route::prefix('user-management')->group(function () {
    //     Route::get('stats', [UserManagementController::class, 'stats']);
    //     Route::get('staff', [UserManagementController::class, 'staff']);
    //     Route::get('patients', [UserManagementController::class, 'patients']);
    // });
});

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/contact-messages', [AdminContactMessageController::class, 'index']);
    Route::get('/contact-messages/{contact_message}', [AdminContactMessageController::class, 'show']);
    Route::patch('/contact-messages/{contact_message}/status', [AdminContactMessageController::class, 'updateStatus']);
});
