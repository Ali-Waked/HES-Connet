<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityLookupController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\Dashboard\AiController;
use App\Http\Controllers\Api\Dashboard\AppointmentController;
use App\Http\Controllers\Api\Dashboard\ArticleController;
use App\Http\Controllers\Api\Dashboard\AuditLogController;
use App\Http\Controllers\Api\Dashboard\CategoryController;
use App\Http\Controllers\Api\Dashboard\CommentController as DashboardCommentController;
use App\Http\Controllers\Api\Dashboard\ConversationManagementController;
use App\Http\Controllers\Api\Dashboard\DashboardAnalyticsController;
use App\Http\Controllers\Api\Dashboard\DashboardController;
use App\Http\Controllers\Api\Dashboard\DashboardReportController;
use App\Http\Controllers\Api\Dashboard\DepartmentController;
use App\Http\Controllers\Api\Dashboard\FacilityController;
use App\Http\Controllers\Api\Dashboard\FacilityReviewController;
use App\Http\Controllers\Api\Dashboard\InvoiceController;
use App\Http\Controllers\Api\Dashboard\OrganizationController;
use App\Http\Controllers\Api\Dashboard\OrganizationStatsController;
use App\Http\Controllers\Api\Dashboard\OrganizationUserController;
use App\Http\Controllers\Api\Dashboard\PatientController;
use App\Http\Controllers\Api\Dashboard\PermissionController;
use App\Http\Controllers\Api\Dashboard\PlatformReviewController as DashboardPlatformReviewController;
use App\Http\Controllers\Api\Dashboard\PositionController;
use App\Http\Controllers\Api\Dashboard\PrescriptionController;
use App\Http\Controllers\Api\Dashboard\RoleController;
use App\Http\Controllers\Api\Dashboard\ScheduleController;
use App\Http\Controllers\Api\Dashboard\SpecializationController;
use App\Http\Controllers\Api\Dashboard\StaffController;
use App\Http\Controllers\Api\Dashboard\StaffPositionController;
use App\Http\Controllers\Api\Dashboard\StaffScheduleController;
use App\Http\Controllers\Api\Dashboard\StaffUnavailabilityController;
use App\Http\Controllers\Api\Dashboard\StoryController as DashboardStoryController;
use App\Http\Controllers\Api\Dashboard\SymptomController as DashboardSymptomController;
use App\Http\Controllers\Api\Dashboard\TagController;
use App\Http\Controllers\Api\Dashboard\UserController;
use App\Http\Controllers\Api\Dashboard\UsersController as SuperAdminUsersController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\Facility\AppointmentController as FacilityAppointmentController;
// use App\Http\Controllers\Api\Facility\ArticleController as FacilityArticleController;
use App\Http\Controllers\Api\Facility\FacilityDashboardController;
use App\Http\Controllers\Api\Facility\FacilityReportController;
use App\Http\Controllers\Api\Facility\MedicineController as FacilityMedicineController;
use App\Http\Controllers\Api\Facility\NotificationController as FacilityNotificationController;
use App\Http\Controllers\Api\Facility\PatientController as FacilityPatientController;
use App\Http\Controllers\Api\Facility\ReviewController as FacilityPortalReviewController;
use App\Http\Controllers\Api\Facility\StaffController as FacilityStaffController;
use App\Http\Controllers\Api\Facility\StaffLookupController;
use App\Http\Controllers\Api\Facility\StaffReviewController;
use App\Http\Controllers\Api\Facility\StaffScheduleController as FacilityStaffScheduleController;
use App\Http\Controllers\Api\Facility\SymptomController as FacilitySymptomController;
use App\Http\Controllers\Api\Facility\UsersController as FacilityUsersController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Patient\AiConversationController;
use App\Http\Controllers\Api\Patient\MedicationRequestController;
use App\Http\Controllers\Api\Patient\PrescriptionController as PatientPrescriptionController;
use App\Http\Controllers\Api\Patient\StoryController;
use App\Http\Controllers\Api\PlatformReviewController as UserPlatformReviewController;
use App\Http\Controllers\Api\Public\AppointmentController as PublicAppointmentController;
use App\Http\Controllers\Api\Public\ArticleController as PublicArticleController;
use App\Http\Controllers\Api\Public\CommentController as PublicCommentController;
use App\Http\Controllers\Api\Public\ContactMessageController as PublicContactMessageController;
use App\Http\Controllers\Api\Public\DoctorController as PublicDoctorController;
use App\Http\Controllers\Api\Public\DonationController;
use App\Http\Controllers\Api\Public\FacilityController as PublicFacilityController;
use App\Http\Controllers\Api\Public\FacilityReviewController as PublicFacilityReviewController;
use App\Http\Controllers\Api\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Api\Public\JobPostController as PublicJobPostController;
use App\Http\Controllers\Api\Public\PageController as PublicPageController;
use App\Http\Controllers\Api\Public\PaymentController;
use App\Http\Controllers\Api\Public\ProfileController;
use App\Http\Controllers\Api\Public\PublicSubscriptionController;
use App\Http\Controllers\Api\Public\ReviewController as PublicReviewController;
use App\Http\Controllers\Api\Public\StoryController as PublicStoryController;
use App\Http\Controllers\Api\Public\WebhookController;
use App\Http\Controllers\Api\PublicPlatformReviewController;
use App\Http\Controllers\Api\SearchHistoryController;
use App\Http\Controllers\Api\Staff\ArticleController as StaffArticleController;
use App\Http\Controllers\Api\Staff\AvailabilityController as StaffAvailabilityController;
use App\Http\Controllers\Api\Staff\CalendarController;
use App\Http\Controllers\Api\Staff\MedicationRequestController as StaffMedicationRequestController;
use App\Http\Controllers\Api\Staff\PrescriptionController as StaffPrescriptionController;
use App\Http\Controllers\Api\Staff\ScheduleController as StaffOwnScheduleController;
use App\Http\Controllers\Api\Staff\StaffFacilityController;
use App\Http\Controllers\Api\Staff\SymptomController as StaffSymptomController;
use App\Http\Controllers\Api\Staff\UnavailabilityController as StaffOwnUnavailabilityController;
use App\Http\Controllers\Api\Staff\WorkspaceController;
use Illuminate\Support\Facades\Route;

// =============================================================================
// PUBLIC ROUTES
// =============================================================================

Route::post('/contact-us', [PublicContactMessageController::class, 'store']);

Route::get('/cities/list', [CityLookupController::class, 'index']);

Route::get('/categories/{type}', App\Http\Controllers\Api\CategoryController::class);
Route::get('/tags', App\Http\Controllers\Api\TagController::class);

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
    Route::get('{facility}/reviews', [PublicFacilityReviewController::class, 'index']);
    Route::post('{facility}/reviews', [PublicFacilityReviewController::class, 'store'])->middleware('auth:sanctum');
});

Route::prefix('articles')->group(function () {
    Route::get('/', [PublicArticleController::class, 'index']);
    Route::get('{article}', [PublicArticleController::class, 'show']);
    Route::get('{article:uuid}/comments', [PublicCommentController::class, 'index']);
});

Route::middleware('auth:sanctum')->prefix('articles')->group(function () {
    Route::post('{article:uuid}/comment', [PublicCommentController::class, 'store']);
    Route::put('{article:uuid}/comment/{comment}', [PublicCommentController::class, 'update']);
    Route::delete('{article:uuid}/comment/{comment}', [PublicCommentController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/set-active-workspace/{facility}', [WorkspaceController::class, 'setActiveWorkspace']);
});

// Email Verification
Route::middleware(['auth:sanctum', 'throttle:6,1'])->prefix('email')->group(function () {
    Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.send');
});

Route::prefix('appointments')->group(function () {
    Route::get('/', [PublicAppointmentController::class, 'index']);
    Route::post('/', [PublicAppointmentController::class, 'store']);
    Route::get('{article}', [PublicAppointmentController::class, 'show']);
});

Route::get('/stories', [PublicStoryController::class, 'index']);
Route::get('/stories/{story}', [PublicStoryController::class, 'show']);
Route::get('/home', PublicHomeController::class);

Route::get('/pages/{slug}', [PublicPageController::class, 'show']);
Route::post('/reviews/{appointment}', [PublicReviewController::class, 'store']);

Route::prefix('job-posts')->group(function () {
    Route::get('/', [PublicJobPostController::class, 'index']);
    Route::get('{slug}', [PublicJobPostController::class, 'show']);
});

Route::prefix('public/subscriptions')->group(function () {
    Route::post('/', [PublicSubscriptionController::class, 'subscribe']);
    Route::get('/verify/{token}', [PublicSubscriptionController::class, 'verify'])->name('subscriptions.verify');
    Route::patch('/{token}', [PublicSubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::post('/unsubscribe/{token}', [PublicSubscriptionController::class, 'unsubscribe'])->name('subscriptions.unsubscribe');
});

// =============================================================================
// DONATIONS & PAYMENTS
// =============================================================================

Route::prefix('donations')->group(function () {
    Route::get('/', [DonationController::class, 'index']);
    Route::get('status', [DonationController::class, 'status']);
    Route::post('{story}/checkout', [DonationController::class, 'checkout']);
});

Route::prefix('payments')->group(function () {
    Route::post('/stripe/checkout', [PaymentController::class, 'createStripeCheckout']);
});

Route::post('/webhooks/stripe', [WebhookController::class, 'stripe']);

// =============================================================================
// AUTHENTICATED SHARED ROUTES
// =============================================================================

Route::middleware('auth:sanctum')->get('/profile', [AuthController::class, 'profile']);
Route::middleware('auth:sanctum')->put('/profile', [ProfileController::class, 'update']);
Route::get('/public/platform-reviews', [PublicPlatformReviewController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('platform-review')->group(function () {
    Route::get('/', [UserPlatformReviewController::class, 'myReview']);
    Route::post('/', [UserPlatformReviewController::class, 'store']);
    Route::put('/', [UserPlatformReviewController::class, 'update']);
    Route::delete('/', [UserPlatformReviewController::class, 'destroy']);
});

// =============================================================================
// SEARCH HISTORY
// =============================================================================

Route::middleware('auth:sanctum')->prefix('search-histories')->group(function () {
    Route::get('/', [SearchHistoryController::class, 'index']);
    Route::post('/', [SearchHistoryController::class, 'store']);
    Route::delete('/', [SearchHistoryController::class, 'destroy']);
});

// =============================================================================
// FAVORITES
// =============================================================================

Route::middleware('auth:sanctum')->prefix('favorites')->group(function () {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/', [FavoriteController::class, 'toggle']);
    Route::delete('{favorite}', [FavoriteController::class, 'destroy']);
});

// =============================================================================
// USER CONVERSATIONS
// =============================================================================

Route::middleware('auth:sanctum')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', [ConversationController::class, 'index'])->name('index');
    Route::post('/', [ConversationController::class, 'store'])->name('store');
    Route::post('/find-or-create', [ConversationController::class, 'findOrCreate']);
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

Route::middleware(['auth:sanctum'])->prefix('patient')->group(function () {
    Route::get('/stories', [StoryController::class, 'index']);
    Route::post('/stories', [StoryController::class, 'store']);
    Route::put('/stories/{story}', [StoryController::class, 'update']);
});

// Story Donations
Route::middleware(['auth:sanctum'])->prefix('story')->group(function () {
    Route::get('/{story}/donations', [App\Http\Controllers\Api\Story\DonationController::class, 'index']);
    Route::post('/{story}/donations', [App\Http\Controllers\Api\Story\DonationController::class, 'store']);
    Route::get('/{story}/donations/stats', [App\Http\Controllers\Api\Story\DonationController::class, 'stats']);
});

Route::middleware(['auth:sanctum', 'permission:medicines.manage'])->group(function () {
    Route::post('/medicines', [MedicineController::class, 'store']);
    Route::put('/medicines/{medicine}', [MedicineController::class, 'update']);
    Route::delete('/medicines/{medicine}', [MedicineController::class, 'destroy']);
});

// Staff Articles — staff can only see/manage their own articles
Route::middleware(['auth:sanctum'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/articles', [StaffArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/{article}', [StaffArticleController::class, 'show'])->name('articles.show');
});

Route::middleware(['auth:sanctum'])->prefix('staff')->name('staff.')->group(function () {
    Route::post('/articles', [StaffArticleController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [StaffArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [StaffArticleController::class, 'destroy'])->name('articles.destroy');
});

// Staff Schedules — view
// Route::middleware(['auth:sanctum'])->prefix('staff')->name('staff.')->group(function () {
//     Route::get('/schedules', [StaffOwnScheduleController::class, 'index'])->name('schedules.index');
//     Route::get('/{staff}/unavailability', [StaffOwnUnavailabilityController::class, 'index'])->name('unavailability.index');
//     Route::get('/{staff}/available-slots', [StaffAvailabilityController::class, 'availableSlots'])->name('available-slots');
// });

// Staff Schedules — manage
// Route::middleware(['auth:sanctum'])->prefix('staff')->name('staff.')->group(function () {
//     Route::post('/schedules', [StaffOwnScheduleController::class, 'store'])->name('schedules.store');
//     Route::put('/schedules/{schedule}', [StaffOwnScheduleController::class, 'update'])->name('schedules.update');
//     Route::delete('/schedules/{schedule}', [StaffOwnScheduleController::class, 'destroy'])->name('schedules.destroy');
//     Route::post('/unavailability', [StaffOwnUnavailabilityController::class, 'store'])->name('unavailability.store');
//     Route::put('/unavailability/{unavailability}', [StaffOwnUnavailabilityController::class, 'update'])->name('unavailability.update');
//     Route::delete('/unavailability/{unavailability}', [StaffOwnUnavailabilityController::class, 'destroy'])->name('unavailability.destroy');
// });

Route::prefix('facilities/{facility}')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('schedules', StaffOwnScheduleController::class);
        Route::apiResource('unavailabilities', StaffOwnUnavailabilityController::class);
    });

// Staff Facilities
Route::middleware('auth:sanctum')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/facilities', [StaffFacilityController::class, 'index'])->name('facilities.index');
});

// Symptoms & Specialization Symptoms
Route::middleware('auth:sanctum')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/symptoms', [StaffSymptomController::class, 'index'])->name('symptoms.index');
});

Route::middleware('auth:sanctum')
    ->prefix('specializations')
    ->name('specializations.')
    ->group(function () {
        Route::put('{specialization}/symptoms', [StaffSymptomController::class, 'update'])->name('symptoms.update');
    });

Route::middleware(['auth:sanctum', 'dashboard.access:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        // Doctor dashboard routes are registered here.
    });

Route::middleware(['auth:sanctum', 'dashboard.access:patient'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function () {
        // Patient dashboard routes are registered here.
    });

// =============================================================================
// ADMIN ROUTES
// =============================================================================

Route::middleware(['auth:sanctum', ''])->prefix('dashboard')->group(function () {
    Route::get(
        'facilities/{facility:uuid}/review-stats',
        [FacilityReviewController::class, 'stats']
    )->name('facility-reviews.stats');
    Route::get('/{facility}/staffs', [StaffController::class, 'lookup']);

    Route::get('/prescriptions', [PrescriptionController::class, 'index']);
    Route::get('/prescriptions/analytics', [DashboardAnalyticsController::class, 'dashboard']);
    Route::get('/medication-requests', [App\Http\Controllers\Api\Dashboard\MedicationRequestController::class, 'index']);
    Route::get('/medication-requests/analytics', [DashboardAnalyticsController::class, 'requestAnalytics']);

    Route::get('/facilities/stats', [FacilityController::class, 'stats']);
    Route::get('/facilities/{facility}/edit', [FacilityController::class, 'edit']);
    Route::patch('/comments/{comment}/hide', [DashboardCommentController::class, 'hide']);
    Route::patch('/comments/{comment}/show', [DashboardCommentController::class, 'show']);
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

    Route::prefix('job-posts')->group(function () {
        require base_path('routes/api/dashboard/job-posts.php');
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
        'platform-reviews' => DashboardPlatformReviewController::class,
        'organization-users' => OrganizationUserController::class,
        'positions' => PositionController::class,
        'medicines' => MedicineController::class,
    ]);

    Route::post('platform-reviews/{platform_review}/reply', [DashboardPlatformReviewController::class, 'reply']);

    Route::prefix('symptoms')->group(function () {
        Route::get('/', [DashboardSymptomController::class, 'index']);
        Route::get('/stats', [DashboardSymptomController::class, 'stats']);
        Route::post('/', [DashboardSymptomController::class, 'store']);
        Route::get('{symptom}', [DashboardSymptomController::class, 'show']);
        Route::put('{symptom}', [DashboardSymptomController::class, 'update']);
        Route::delete('{symptom}', [DashboardSymptomController::class, 'destroy']);
    });

    Route::prefix('specializations')->group(function () {
        Route::get('lookup', [SpecializationController::class, 'lookup']);
        Route::get('/', [SpecializationController::class, 'index']);
        Route::post('/', [SpecializationController::class, 'store']);
        Route::get('{specialization}', [SpecializationController::class, 'show']);
        Route::put('{specialization}', [SpecializationController::class, 'update']);
        Route::delete('{specialization}', [SpecializationController::class, 'destroy']);
        Route::get('{specialization}/symptoms', [SpecializationController::class, 'listSymptoms']);
        Route::put('{specialization}/symptoms', [SpecializationController::class, 'syncSymptoms']);
        Route::post('{specialization}/symptoms', [SpecializationController::class, 'attachSymptoms']);
        Route::delete('{specialization}/symptoms/{symptom}', [SpecializationController::class, 'detachSymptom']);
    });

    Route::prefix('search-histories')->group(function () {
        Route::get('/', [SearchHistoryController::class, 'adminIndex']);
        Route::get('/trending', [SearchHistoryController::class, 'trending']);
    });

    Route::prefix('stories')->name('stories.')->group(function () {
        Route::get('/', [DashboardStoryController::class, 'index'])->name('index');
        Route::get('/trash', [DashboardStoryController::class, 'trash'])->name('trash');
        Route::get('/stats', [DashboardStoryController::class, 'stats'])->name('stats');
        Route::get('/{story}', [DashboardStoryController::class, 'show'])->name('show');
        Route::patch('/{story}/status', [DashboardStoryController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{story}', [DashboardStoryController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [DashboardStoryController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [DashboardStoryController::class, 'forceDelete'])->name('force-delete');
    });

    // Donations
    Route::prefix('donations')->name('donations.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\Dashboard\DonationController::class, 'index'])->name('index');
        Route::get('{donation}', [App\Http\Controllers\Api\Dashboard\DonationController::class, 'show'])->name('show');
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\Dashboard\PaymentController::class, 'index']);
    });

    // Invoices
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
    });
});

// =============================================================================
// UNIFIED APPOINTMENT ROUTES
// =============================================================================

Route::middleware(['auth:sanctum'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/stats', [AppointmentController::class, 'stats'])->name('stats');
        Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
        Route::get('/analytics', [AppointmentController::class, 'analytics'])->name('analytics');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
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
Route::get('/facility/{facility}/staff-lookup', StaffLookupController::class);

Route::apiResource('facility.staff', FacilityStaffController::class);
Route::apiResource('facility.departments', App\Http\Controllers\Api\Facility\DepartmentController::class);

Route::middleware(['auth:sanctum', 'dashboard.access:admin'])->prefix('admin')->group(function () {
    Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('admin.contact-messages.index');
    Route::get('/contact-messages/{contact_message}', [AdminContactMessageController::class, 'show'])->name('admin.contact-messages.show');
    Route::patch('/contact-messages/{contact_message}/status', [AdminContactMessageController::class, 'updateStatus'])->name('admin.contact-messages.update-status');
});

// =============================================================================
// FACILITY OWNER ROUTES
// =============================================================================

Route::prefix('facility/{facility}')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::apiResource('staff-schedules', FacilityStaffScheduleController::class);
    });
Route::prefix('facility/{facility}')
    ->group(function () {

        Route::get(
            'staff-unavailabilities',
            [StaffUnavailabilityController::class, 'index']
        );

        Route::get(
            'staff-unavailabilities/{staffUnavailability}',
            [StaffUnavailabilityController::class, 'show']
        );

        Route::patch(
            'staff-unavailabilities/{staffUnavailability}/approve',
            [StaffUnavailabilityController::class, 'approve']
        );

        Route::patch(
            'staff-unavailabilities/{staffUnavailability}/reject',
            [StaffUnavailabilityController::class, 'reject']
        );
    });
Route::middleware(['auth:sanctum', 'dashboard.access:facility'])->prefix('facility')->group(function () {
    // Patients
    Route::middleware('permission:patients.view')->group(function () {
        Route::get('/patients', [FacilityPatientController::class, 'index']);
        Route::get('/patients/{patient}', [FacilityPatientController::class, 'show']);
    });

    // Reviews
    Route::middleware('permission:reviews.view')
        ->get('/reviews', [FacilityPortalReviewController::class, 'index']);
    Route::middleware('permission:reviews.approve')
        ->post('/reviews/{review}/approve', [FacilityPortalReviewController::class, 'approve']);
    Route::middleware('permission:reviews.reject')
        ->post('/reviews/{review}/reject', [FacilityPortalReviewController::class, 'reject']);

    // Articles
    // Route::middleware('permission:articles.view')
    //     ->get('/articles', [FacilityArticleController::class, 'index']);

    // Job Posts
    Route::middleware('')
        ->prefix('{facility}/job-posts')
        ->group(function () {
            require base_path('routes/api/facility/job-posts.php');
        });

    // Symptoms
    Route::middleware('permission:symptoms.view')
        ->get('/symptoms', [FacilitySymptomController::class, 'index']);

    // Search Histories
    Route::prefix('search-histories')->group(function () {
        Route::get('/', [SearchHistoryController::class, 'adminIndex']);
    });

    // Notifications
    Route::middleware('permission:notifications.view')
        ->get('/notifications', [FacilityNotificationController::class, 'index']);
});

// =============================================================================
// PATIENT AI CONSULTATION
// =============================================================================

Route::middleware(['auth:sanctum'])
    ->prefix('patient/ai')
    ->name('patient.ai.')
    ->group(function () {
        Route::get('/conversations', [AiConversationController::class, 'index'])->name('conversations.index');
        Route::post('/conversations', [AiConversationController::class, 'store'])->name('conversations.store');
        Route::get('/conversations/{uuid}', [AiConversationController::class, 'show'])->name('conversations.show');
        Route::put('/conversations/{uuid}', [AiConversationController::class, 'update'])->name('conversations.update');
        Route::delete('/conversations/{uuid}', [AiConversationController::class, 'destroy'])->name('conversations.destroy');
        Route::post('/conversations/{uuid}/messages', [AiConversationController::class, 'storeMessage'])->name('conversations.messages.store');
        Route::post('/conversations/{uuid}/recommend-doctor', [AiConversationController::class, 'recommendDoctor'])->name('conversations.recommend-doctor');
    });

// =============================================================================
// AI ASSISTANT
// =============================================================================

Route::middleware(['auth:sanctum', 'dashboard.access:admin'])
    ->prefix('dashboard/ai')
    ->name('dashboard.ai.')
    ->group(function () {
        Route::post('/ask', [AiController::class, 'ask'])->name('ask');
        Route::get('/conversations', [AiController::class, 'conversations'])->name('conversations');
        Route::get('/conversations/{uuid}', [AiController::class, 'show'])->name('conversations.show');
        Route::patch('/conversations/{uuid}', [AiController::class, 'rename'])->name('conversations.rename');
        Route::delete('/conversations/{uuid}', [AiController::class, 'destroy'])->name('conversations.destroy');
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
        Route::get('/medication-requests', [MedicationRequestController::class, 'index']);
        Route::get('/medication-requests/{uuid}', [MedicationRequestController::class, 'show']);
        Route::patch('/medication-requests/{uuid}/cancel', [MedicationRequestController::class, 'cancel']);
    });

// =============================================================================
// SUPER ADMIN DASHBOARD & REPORTS
// =============================================================================

Route::middleware(['auth:sanctum', 'dashboard.access:admin'])
    ->prefix('dashboard')
    ->name('admin.dashboard.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('overview');
        Route::get('reports', [DashboardReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/excel', [DashboardReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('reports/export/pdf', [DashboardReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

// =============================================================================
// SUPER ADMIN - SYSTEM LEVEL
// =============================================================================

Route::middleware(['auth:sanctum', 'dashboard.access:admin'])
    ->prefix('dashboard')
    ->name('api.system.')
    ->group(function () {
        Route::get('users/trashed', [SuperAdminUsersController::class, 'trashed'])->name('users.trashed');
        Route::patch('users/{user}/toggle-status', [SuperAdminUsersController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{uuid}/restore', [SuperAdminUsersController::class, 'restore'])->name('users.restore');
        Route::apiResource('users', SuperAdminUsersController::class);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

// =============================================================================
// FACILITY DASHBOARD (scoped by facility UUID)
// =============================================================================

Route::middleware(['auth:sanctum'])
    ->prefix('facility/{facility:uuid}/dashboard')
    ->name('facility.dashboard.')
    ->group(function () {
        Route::get('/', [FacilityDashboardController::class, 'overview'])->name('overview');
        Route::get('/alerts', [FacilityDashboardController::class, 'alerts'])->name('alerts');
        Route::get('/analytics', [FacilityDashboardController::class, 'analytics'])->name('analytics');
        Route::get('/appointments/live', [FacilityDashboardController::class, 'liveAppointments'])->name('appointments.live');
        Route::get('/doctors-performance', [FacilityDashboardController::class, 'doctorsPerformance'])->name('doctors-performance');
        Route::get('/patients', [FacilityDashboardController::class, 'patients'])->name('patients');
        Route::get('/schedules', [FacilityDashboardController::class, 'schedules'])->name('schedules');
    });

// =============================================================================
// FACILITY OWNER - DASHBOARD & REPORTS
// =============================================================================

Route::middleware(['auth:sanctum', 'dashboard.access:facility'])
    ->prefix('facility')
    ->name('facility.')
    ->group(function () {
        Route::get('reports', [FacilityReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/excel', [FacilityReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('reports/export/pdf', [FacilityReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

// =============================================================================
// FACILITY OWNER - FACILITY LEVEL
// =============================================================================

Route::middleware(['auth:sanctum', 'dashboard.access:facility'])
    ->prefix('facility')
    ->name('api.facility.')
    ->group(function () {
        Route::get('{facility}/users', [FacilityUsersController::class, 'index'])->name('users.index');
        Route::get('{facility}/users/{user}', [FacilityUsersController::class, 'show'])->name('users.show');
    });

// =============================================================================
// NOTIFICATIONS
// =============================================================================

Route::middleware('auth:sanctum')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
    Route::get('/count', [NotificationController::class, 'count'])->name('count');
    Route::patch('/{notification}/read', [NotificationController::class, 'read'])->name('read');
    Route::patch('/read-all', [NotificationController::class, 'readAll'])->name('read-all');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
});

// =============================================================================
// STAFF CALENDAR
// =============================================================================

Route::middleware('auth:sanctum')->get('/calendar', [CalendarController::class, 'index']);
