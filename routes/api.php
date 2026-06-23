<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\Admin\PrescriptionController as AdminPrescriptionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityLookupController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\Dashboard\AppointmentController;
use App\Http\Controllers\Api\Dashboard\ArticleController;
use App\Http\Controllers\Api\Dashboard\CategoryController;
use App\Http\Controllers\Api\Dashboard\ConversationManagementController;
use App\Http\Controllers\Api\Dashboard\DepartmentController;
use App\Http\Controllers\Api\Dashboard\FacilityController;
use App\Http\Controllers\Api\Dashboard\FacilityReviewController;
use App\Http\Controllers\Api\Dashboard\OrganizationController;
use App\Http\Controllers\Api\Dashboard\OrganizationStatsController;
use App\Http\Controllers\Api\Dashboard\OrganizationUserController;
use App\Http\Controllers\Api\Dashboard\PatientController;
use App\Http\Controllers\Api\Dashboard\PermissionController;
use App\Http\Controllers\Api\Dashboard\PlatformReviewController as DashboardPlatformReviewController;
use App\Http\Controllers\Api\Dashboard\PositionController;
use App\Http\Controllers\Api\Dashboard\RoleController;
use App\Http\Controllers\Api\Dashboard\ScheduleController;
use App\Http\Controllers\Api\Dashboard\StaffController;
use App\Http\Controllers\Api\Dashboard\StaffPositionController;
use App\Http\Controllers\Api\Dashboard\StaffScheduleController;
use App\Http\Controllers\Api\Dashboard\StaffUnavailabilityController;
use App\Http\Controllers\Api\Dashboard\TagController;
use App\Http\Controllers\Api\Dashboard\UserController;
use App\Http\Controllers\Api\Facility\AppointmentController as FacilityAppointmentController;
use App\Http\Controllers\Api\Facility\ArticleController as FacilityArticleController;
use App\Http\Controllers\Api\Facility\FacilityDashboardController;
use App\Http\Controllers\Api\Facility\MedicineController as FacilityMedicineController;
use App\Http\Controllers\Api\Facility\NotificationController as FacilityNotificationController;
use App\Http\Controllers\Api\Facility\PatientController as FacilityPatientController;
use App\Http\Controllers\Api\Facility\ProfileController as FacilityProfileController;
use App\Http\Controllers\Api\Facility\ReviewController as FacilityPortalReviewController;
use App\Http\Controllers\Api\Facility\StaffReviewController;
use App\Http\Controllers\Api\FacilityOwner\PrescriptionController as FacilityOwnerPrescriptionController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\Patient\MedicationRequestController;
use App\Http\Controllers\Api\Patient\PrescriptionController as PatientPrescriptionController;
use App\Http\Controllers\Api\Public\AppointmentController as PublicAppointmentController;
use App\Http\Controllers\Api\Public\ArticleController as PublicArticleController;
use App\Http\Controllers\Api\Public\ContactMessageController as PublicContactMessageController;
use App\Http\Controllers\Api\Public\DoctorController as PublicDoctorController;
use App\Http\Controllers\Api\Public\FacilityController as PublicFacilityController;
use App\Http\Controllers\Api\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Api\Public\PageController as PublicPageController;
use App\Http\Controllers\Api\Public\ReviewController as PublicReviewController;
use App\Http\Controllers\Api\Staff\ArticleController as StaffArticleController;
use App\Http\Controllers\Api\Staff\AvailabilityController as StaffAvailabilityController;
use App\Http\Controllers\Api\Staff\MedicationRequestController as StaffMedicationRequestController;
use App\Http\Controllers\Api\Staff\PrescriptionController as StaffPrescriptionController;
use App\Http\Controllers\Api\Staff\ScheduleController as StaffOwnScheduleController;
use App\Http\Controllers\Api\Staff\StaffFacilityController;
use App\Http\Controllers\Api\Staff\UnavailabilityController as StaffOwnUnavailabilityController;
use Illuminate\Support\Facades\Route;

// =============================================================================
// PUBLIC ROUTES
// =============================================================================

Route::post('/contact-us', [PublicContactMessageController::class, 'store']);

Route::get('/cities/list', [CityLookupController::class, 'index']);

Route::prefix('doctors')->group(function () {
    Route::get('/', [PublicDoctorController::class, 'index']);
    Route::get('{facility}/{staff}/available-days', [PublicDoctorController::class, 'availableDays']);
    Route::get('{facility}/{staff}/available-slots', [PublicDoctorController::class, 'availableSlots']);
    Route::get('{staff}/facilities', [PublicDoctorController::class, 'facilities']);
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

Route::prefix('appointments')->group(function () {
    Route::get('/', [PublicAppointmentController::class, 'index']);
    Route::post('/', [PublicAppointmentController::class, 'store']);
    Route::get('{article}', [PublicAppointmentController::class, 'show']);
});

Route::get('/home', PublicHomeController::class);

Route::get('/pages/{slug}', [PublicPageController::class, 'show']);
Route::post('/reviews/{appointment}', [PublicReviewController::class, 'store']);

// =============================================================================
// AUTHENTICATED SHARED ROUTES
// =============================================================================

Route::middleware('auth:sanctum')->get('/profile', [AuthController::class, 'profile']);

// =============================================================================
// USER CONVERSATIONS
// =============================================================================

Route::middleware('auth:sanctum')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', [ConversationController::class, 'index'])->name('index');
    Route::post('/', [ConversationController::class, 'store'])->name('store');
    Route::get('{conversation}', [ConversationController::class, 'show'])->name('show');
    Route::post('{conversation}/messages', [ConversationController::class, 'storeMessage'])->name('messages.store');
    Route::post('{conversation}/read', [ConversationController::class, 'markAsRead'])->name('read');
});

// =============================================================================
// STAFF ROUTES
// =============================================================================

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/medicines', [MedicineController::class, 'index']);
    Route::get('/medicines/lookup', [MedicineController::class, 'lookup']);
    Route::get('/medicines/{medicine}', [MedicineController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'permission:medicines.manage'])->group(function () {
    Route::post('/medicines', [MedicineController::class, 'store']);
    Route::put('/medicines/{medicine}', [MedicineController::class, 'update']);
    Route::delete('/medicines/{medicine}', [MedicineController::class, 'destroy']);
});

// Staff Articles — staff can only see/manage their own articles
Route::middleware(['auth:sanctum', 'permission:articles.view'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/articles', [StaffArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/{article}', [StaffArticleController::class, 'show'])->name('articles.show');
});

Route::middleware(['auth:sanctum', 'permission:articles.manage'])->prefix('staff')->name('staff.')->group(function () {
    Route::post('/articles', [StaffArticleController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [StaffArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [StaffArticleController::class, 'destroy'])->name('articles.destroy');
});

// Staff Schedules — view
Route::middleware(['auth:sanctum'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/schedules', [StaffOwnScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/{staff}/unavailability', [StaffOwnUnavailabilityController::class, 'index'])->name('unavailability.index');
    Route::get('/{staff}/available-slots', [StaffAvailabilityController::class, 'availableSlots'])->name('available-slots');
});

// Staff Schedules — manage
Route::middleware(['auth:sanctum'])->prefix('staff')->name('staff.')->group(function () {
    Route::post('/schedules', [StaffOwnScheduleController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [StaffOwnScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [StaffOwnScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::post('/unavailability', [StaffOwnUnavailabilityController::class, 'store'])->name('unavailability.store');
    Route::put('/unavailability/{unavailability}', [StaffOwnUnavailabilityController::class, 'update'])->name('unavailability.update');
    Route::delete('/unavailability/{unavailability}', [StaffOwnUnavailabilityController::class, 'destroy'])->name('unavailability.destroy');
});

// Staff Facilities
Route::middleware('auth:sanctum')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/facilities', [StaffFacilityController::class, 'index'])->name('facilities.index');
});

Route::middleware(['auth:sanctum', 'dashboard.access:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        // Doctor dashboard routes are registered here.
        // Example:
        // Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    });

Route::middleware(['auth:sanctum', 'dashboard.access:patient'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function () {
        // Patient dashboard routes are registered here.
        // Example:
        // Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');
    });

// =============================================================================
// ADMIN ROUTES
// =============================================================================

Route::middleware(['auth:sanctum', 'dashboard.access:admin'])->prefix('dashboard')->group(function () {
    Route::get(
        'facilities/{facility:uuid}/review-stats',
        [FacilityReviewController::class, 'stats']
    )->name('facility-reviews.stats');

    Route::get('/facilities/stats', [FacilityController::class, 'stats']);
    Route::get('/facilities/{facility}/edit', [FacilityController::class, 'edit']);
    Route::get('/articles/stats', [ArticleController::class, 'stats']);
    Route::get('/departments/stats', [DepartmentController::class, 'stats']);
    Route::get('/tags/stats', [TagController::class, 'stats']);
    Route::get('/categories/stats', [CategoryController::class, 'stats']);
    Route::get('/positions/stats', [PositionController::class, 'stats']);
    Route::get('positions/lookup', [PositionController::class, 'lookup']);
    Route::get('staff-positions/lookup', [StaffPositionController::class, 'lookup']);
    Route::get('departments/lookup', [DepartmentController::class, 'lookup']);
    Route::get('/users/stats', [UserController::class, 'stats']);
    Route::get('/users/select', [UserController::class, 'select']);
    Route::get('/permissions/stats', [PermissionController::class, 'stats']);
    Route::get('/roles/stats', [RoleController::class, 'stats']);
    Route::get('/roles/facility', [RoleController::class, 'facilityRoles']);
    Route::get('/platform-reviews/stats', [DashboardPlatformReviewController::class, 'stats']);
    Route::get('/organizations/stats', [OrganizationStatsController::class, 'index']);
    Route::get('/staff/check-email', [StaffController::class, 'checkEmail']);

    Route::get('/facility-reviews', [FacilityReviewController::class, 'index']);
    Route::get('/facility-reviews/{facilityReview}', [FacilityReviewController::class, 'show']);
    Route::patch('/facility-reviews/{facilityReview}/hide', [FacilityReviewController::class, 'hide']);
    Route::patch('/facility-reviews/{facilityReview}/show', [FacilityReviewController::class, 'showReview']);

    Route::prefix('cities')->group(function () {
        require base_path('routes/api/dashboard/cities.php');
    });

    Route::prefix('pages')->group(function () {
        require base_path('routes/api/dashboard/pages.php');
    });

    Route::prefix('staff-positions')->group(function () {
        require base_path('routes/api/dashboard/staff-positions.php');
    });

    Route::post(
        'facility-staff/{staff}/terminate',
        [StaffController::class, 'terminate']
    )->name('staff.terminate');

    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/', [ConversationManagementController::class, 'index'])->name('index');
        Route::get('/stats', [ConversationManagementController::class, 'stats'])->name('stats');
        Route::get('{conversation}', [ConversationManagementController::class, 'show'])->name('show');
        Route::patch('{conversation}/archive', [ConversationManagementController::class, 'archive'])->name('archive');
        Route::patch('{conversation}/lock', [ConversationManagementController::class, 'lock'])->name('lock');
    });

    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/calendar', [ScheduleController::class, 'calendar'])->name('calendar');
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/', [ScheduleController::class, 'store'])->name('store');
        Route::get('{staffSchedule}', [ScheduleController::class, 'show'])->name('show');
        Route::put('{staffSchedule}', [ScheduleController::class, 'update'])->name('update');
        Route::delete('{staffSchedule}', [ScheduleController::class, 'destroy'])->name('destroy');
    });

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
        'staff-schedules' => StaffScheduleController::class,
        'staff-unavailabilities' => StaffUnavailabilityController::class,
        'reviews' => DashboardPlatformReviewController::class,
        'organization-users' => OrganizationUserController::class,
        'positions' => PositionController::class,
        'medicines' => MedicineController::class,
    ]);
});

// =============================================================================
// UNIFIED APPOINTMENT ROUTES
// =============================================================================
// Accessible by: admins, facility owners, and staff (authorization handled in service)
Route::middleware(['auth:sanctum'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/stats', [AppointmentController::class, 'stats'])->name('stats');
        Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
        Route::get('/analytics', [AppointmentController::class, 'analytics'])->name('analytics');

        // Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        // Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
        // Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
        // Route::post('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
        // Route::post('/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('reschedule');
        // Route::post('/{appointment}/restore', [AppointmentController::class, 'restore'])->name('restore');
        // Route::post('/{appointment}/force-complete', [AppointmentController::class, 'forceComplete'])->name('force-complete');
    });
    Route::get('facility/{facility}/appointments/stats', [FacilityAppointmentController::class, 'stats']);
    Route::get('facility/{facility}/patients', [FacilityPatientController::class, 'index']);
    Route::get('/reviews', [StaffReviewController::class, 'index']);
    Route::post('/reviews/{review}/reply', [StaffReviewController::class, 'reply']);
    Route::get('/facility/{facility}/medicine/lookup', [FacilityMedicineController::class, 'getAllMedicine']);
    Route::get('/facility/{facility}/appointments/lookup', [FacilityAppointmentController::class, 'lookup']);
    Route::get('/facility/{facility}/medicine/stats', [FacilityMedicineController::class, 'stats']);

    Route::get('facility/{facility}/medication-requests', [StaffMedicationRequestController::class, 'index']);
    Route::post('facility/{facility}/medication-requests/{medicationRequest}/accept', [StaffMedicationRequestController::class, 'acceptRequest']);
    Route::post('facility/{facility}/medication-requests/{medicationRequest}/reject', [StaffMedicationRequestController::class, 'rejectRequest']);

    Route::apiResource('facility.appointments', FacilityAppointmentController::class);
    Route::apiResource('facility.prescriptions', StaffPrescriptionController::class)->only(['index', 'store', 'show']);
    Route::apiResource('facility.medicine', FacilityMedicineController::class);

});

Route::middleware(['auth:sanctum', 'dashboard.access:admin'])->prefix('admin')->group(function () {
    Route::get('/contact-messages', [AdminContactMessageController::class, 'index']);
    Route::get('/contact-messages/{contact_message}', [AdminContactMessageController::class, 'show']);
    Route::patch('/contact-messages/{contact_message}/status', [AdminContactMessageController::class, 'updateStatus']);
});

// =============================================================================
// FACILITY OWNER ROUTES
// =============================================================================

Route::middleware(['auth:sanctum', 'dashboard.access:facility'])->prefix('facility')->group(function () {
    // Dashboard
    Route::middleware('permission:facility_dashboard.view')->group(function () {
        Route::get('/dashboard', [FacilityDashboardController::class, 'dashboard']);
        Route::get('/dashboard/appointments/live', [FacilityDashboardController::class, 'liveAppointments']);
        Route::get('/dashboard/doctors-performance', [FacilityDashboardController::class, 'doctorsPerformance']);
        Route::get('/dashboard/patients', [FacilityDashboardController::class, 'patients']);
        Route::get('/dashboard/schedules', [FacilityDashboardController::class, 'schedules']);
        Route::get('/dashboard/analytics', [FacilityDashboardController::class, 'analytics']);
        Route::get('/dashboard/alerts', [FacilityDashboardController::class, 'alerts']);
        Route::get('/staff', [FacilityDashboardController::class, 'staff']);
    });

    // Profile
    Route::middleware('permission:profile.view')->get('/profile', [FacilityProfileController::class, 'show']);
    Route::middleware('permission:profile.update')->put('/profile', [FacilityProfileController::class, 'update']);

    // Patients
    Route::middleware('permission:patients.view')->group(function () {
        Route::get('/patients', [FacilityPatientController::class, 'index']);
        Route::get('/patients/{patient}', [FacilityPatientController::class, 'show']);
    });

    // Medicines
    // Route::middleware('permission:medicines.view')->get('/medicines', [FacilityMedicineController::class, 'index']);

    // Reviews
    Route::middleware('permission:reviews.view')
        ->get('/reviews', [FacilityPortalReviewController::class, 'index']);
    Route::middleware('permission:reviews.approve')
        ->post('/reviews/{review}/approve', [FacilityPortalReviewController::class, 'approve']);
    Route::middleware('permission:reviews.reject')
        ->post('/reviews/{review}/reject', [FacilityPortalReviewController::class, 'reject']);

    // Articles
    Route::middleware('permission:articles.view')
        ->get('/articles', [FacilityArticleController::class, 'index']);

    // Notifications
    Route::middleware('permission:notifications.view')
        ->get('/notifications', [FacilityNotificationController::class, 'index']);
});

// =============================================================================
// PATIENT PRESCRIPTIONS
// =============================================================================

Route::middleware(['auth:sanctum'])
    ->prefix('patient')
    ->group(function () {
        Route::get('/prescriptions', [PatientPrescriptionController::class, 'index']);
        Route::get('/prescriptions/{prescription}', [PatientPrescriptionController::class, 'show']);
        Route::post('/prescriptions/{prescription}/select-pharmacy', [PatientPrescriptionController::class, 'selectPharmacy']);
        Route::get('/prescriptions/{prescription}/pharmacies', [PatientPrescriptionController::class, 'availablePharmacies']);
        Route::get('/prescriptions/{prescription}/pharmacies', [PatientPrescriptionController::class, 'availablePharmacies']);
        Route::get('/medication-requests', [MedicationRequestController::class, 'index']);
        Route::get('/medication-requests/{uuid}', [MedicationRequestController::class, 'show']);
        Route::patch('/medication-requests/{uuid}/cancel', [MedicationRequestController::class, 'cancel']);
    });

// =============================================================================
// STAFF PRESCRIPTIONS (Doctors & Pharmacists)
// =============================================================================

// Route::middleware(['auth:sanctum', 'dashboard.access:doctor'])
//     ->prefix('staff')
//     ->group(function () {
//         Route::get('/prescriptions', [StaffPrescriptionController::class, 'index']);
//         Route::post('/prescriptions', [StaffPrescriptionController::class, 'store']);
//         Route::get('/prescriptions/{prescription}', [StaffPrescriptionController::class, 'show']);
//     });

// Route::middleware(['auth:sanctum', 'dashboard.access:facility'])
//     ->prefix('staff')
//     ->group(function () {
//         Route::middleware('permission:medication_requests.view')
//             ->get('/medication-requests', [StaffPrescriptionController::class, 'medicationRequests']);
//         Route::middleware('permission:medication_requests.approve')
//             ->post('/medication-requests/{medicationRequest}/accept', [StaffPrescriptionController::class, 'acceptRequest']);
//         Route::middleware('permission:medication_requests.reject')
//             ->post('/medication-requests/{medicationRequest}/reject', [StaffPrescriptionController::class, 'rejectRequest']);
//         Route::middleware('permission:medication_requests.approve')
//             ->post('/medication-requests/{medicationRequest}/dispense', [StaffPrescriptionController::class, 'dispenseRequest']);
//     });

// =============================================================================
// ADMIN PRESCRIPTIONS (Super Admin)
// =============================================================================

Route::middleware(['auth:sanctum', 'dashboard.access:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/medicines', [AdminPrescriptionController::class, 'medicinesIndex']);
        Route::post('/medicines', [AdminPrescriptionController::class, 'medicinesStore']);
        Route::get('/medicines/{medicine}', [AdminPrescriptionController::class, 'medicinesShow']);
        Route::put('/medicines/{medicine}', [AdminPrescriptionController::class, 'medicinesUpdate']);
        Route::delete('/medicines/{medicine}', [AdminPrescriptionController::class, 'medicinesDestroy']);

        Route::get('/prescriptions', [AdminPrescriptionController::class, 'prescriptions']);
        Route::get('/medication-requests', [AdminPrescriptionController::class, 'medicationRequests']);
        Route::get('/prescriptions/analytics', [AdminPrescriptionController::class, 'analytics']);
    });

// =============================================================================
// FACILITY OWNER PRESCRIPTIONS
// =============================================================================

// Route::middleware(['auth:sanctum', 'dashboard.access:facility'])
//     ->prefix('facility-owner')
//     ->group(function () {
//         Route::middleware('permission:prescriptions.view')
//             ->get('/prescriptions', [FacilityOwnerPrescriptionController::class, 'prescriptions']);
//         Route::middleware('permission:medication_requests.view')
//             ->get('/medication-requests', [FacilityOwnerPrescriptionController::class, 'medicationRequests']);
//         Route::middleware('permission:analytics.view')
//             ->get('/prescriptions/analytics', [FacilityOwnerPrescriptionController::class, 'analytics']);
//     });
