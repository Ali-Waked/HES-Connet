<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\AiPrompted;
use App\Events\AppointmentCreated;
use App\Events\AppointmentStatusChanged;
use App\Events\ArticleApproved;
use App\Events\ArticleCreated;
use App\Events\ArticleRejected;
use App\Events\CategoryCreated;
use App\Events\CategoryDeleted;
use App\Events\CategoryUpdated;
use App\Events\CommentAdded;
use App\Events\DoctorReviewed;
use App\Events\DonationCompleted;
use App\Events\DonationCreated;
use App\Events\DonationMade;
use App\Events\FacilityReviewed;
use App\Events\InvoiceGenerated;
use App\Events\JobApproved;
use App\Events\JobPosted;
use App\Events\JobRejected;
use App\Events\MedicineRequestCreated;
use App\Events\MedicineRequestStatusChanged;
use App\Events\PaymentProcessed;
use App\Events\PlatformReviewReplied;
use App\Events\PlatformReviewSubmitted;
use App\Events\PrescriptionCreated;
use App\Events\PrescriptionStatusChanged;
use App\Events\ReviewReplied;
use App\Events\StaffAssigned;
use App\Events\StaffUnassigned;
use App\Events\StoryApproved;
use App\Events\StoryCreated;
use App\Events\StoryRejected;
use App\Events\SymptomCreated;
use App\Events\SymptomDeleted;
use App\Events\SymptomUpdated;
use App\Events\TagCreated;
use App\Events\TagDeleted;
use App\Events\TagUpdated;
use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Listeners\LogAiPrompt;
use App\Listeners\NotifyAppointmentCreated;
use App\Listeners\NotifyAppointmentStatusChanged;
use App\Listeners\NotifyArticleApproved;
use App\Listeners\NotifyArticleCreated;
use App\Listeners\NotifyArticleRejected;
use App\Listeners\NotifyCategoryCreated;
use App\Listeners\NotifyCategoryDeleted;
use App\Listeners\NotifyCategoryUpdated;
use App\Listeners\NotifyCommentAdded;
use App\Listeners\NotifyDoctorReviewed;
use App\Listeners\NotifyDonationCompleted;
use App\Listeners\NotifyDonationCreated;
use App\Listeners\NotifyDonationMade;
use App\Listeners\NotifyFacilityReviewed;
use App\Listeners\NotifyInvoiceGenerated;
use App\Listeners\NotifyJobApproved;
use App\Listeners\NotifyJobPosted;
use App\Listeners\NotifyJobRejected;
use App\Listeners\NotifyMedicineRequestCreated;
use App\Listeners\NotifyMedicineRequestStatusChanged;
use App\Listeners\NotifyPaymentProcessed;
use App\Listeners\NotifyPlatformReviewReplied;
use App\Listeners\NotifyPlatformReviewSubmitted;
use App\Listeners\NotifyPrescriptionCreated;
use App\Listeners\NotifyPrescriptionStatusChanged;
use App\Listeners\NotifyReviewReplied;
use App\Listeners\NotifyStaffAssigned;
use App\Listeners\NotifyStaffUnassigned;
use App\Listeners\NotifyStoryApproved;
use App\Listeners\NotifyStoryCreated;
use App\Listeners\NotifyStoryRejected;
use App\Listeners\NotifySymptomCreated;
use App\Listeners\NotifySymptomDeleted;
use App\Listeners\NotifySymptomUpdated;
use App\Listeners\NotifyTagCreated;
use App\Listeners\NotifyTagDeleted;
use App\Listeners\NotifyTagUpdated;
use App\Listeners\Audit\LogBusinessEvent;
use App\Listeners\Audit\LogUserAuth;
use App\Listeners\NotifyUserLogin;
use App\Listeners\NotifyUserRegistered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        LogUserAuth::class,
        LogBusinessEvent::class,
    ];

    protected $listen = [
        AiPrompted::class => [
            LogAiPrompt::class,
        ],
        UserLoggedIn::class => [
            NotifyUserLogin::class,
        ],
        UserRegistered::class => [
            NotifyUserRegistered::class,
        ],
        ArticleCreated::class => [
            NotifyArticleCreated::class,
        ],
        ArticleApproved::class => [
            NotifyArticleApproved::class,
        ],
        ArticleRejected::class => [
            NotifyArticleRejected::class,
        ],
        StoryCreated::class => [
            NotifyStoryCreated::class,
        ],
        StoryApproved::class => [
            NotifyStoryApproved::class,
        ],
        JobPosted::class => [
            NotifyJobPosted::class,
        ],
        CommentAdded::class => [
            NotifyCommentAdded::class,
        ],
        DonationMade::class => [
            NotifyDonationMade::class,
        ],
        StaffAssigned::class => [
            NotifyStaffAssigned::class,
        ],
        CategoryCreated::class => [
            NotifyCategoryCreated::class,
        ],
        CategoryUpdated::class => [
            NotifyCategoryUpdated::class,
        ],
        CategoryDeleted::class => [
            NotifyCategoryDeleted::class,
        ],
        TagCreated::class => [
            NotifyTagCreated::class,
        ],
        TagUpdated::class => [
            NotifyTagUpdated::class,
        ],
        TagDeleted::class => [
            NotifyTagDeleted::class,
        ],
        SymptomCreated::class => [
            NotifySymptomCreated::class,
        ],
        SymptomUpdated::class => [
            NotifySymptomUpdated::class,
        ],
        SymptomDeleted::class => [
            NotifySymptomDeleted::class,
        ],
        FacilityReviewed::class => [
            NotifyFacilityReviewed::class,
        ],
        DoctorReviewed::class => [
            NotifyDoctorReviewed::class,
        ],
        PlatformReviewSubmitted::class => [
            NotifyPlatformReviewSubmitted::class,
        ],
        PlatformReviewReplied::class => [
            NotifyPlatformReviewReplied::class,
        ],
        ReviewReplied::class => [
            NotifyReviewReplied::class,
        ],
        AppointmentCreated::class => [
            NotifyAppointmentCreated::class,
        ],
        AppointmentStatusChanged::class => [
            NotifyAppointmentStatusChanged::class,
        ],
        PrescriptionCreated::class => [
            NotifyPrescriptionCreated::class,
        ],
        PrescriptionStatusChanged::class => [
            NotifyPrescriptionStatusChanged::class,
        ],
        MedicineRequestCreated::class => [
            NotifyMedicineRequestCreated::class,
        ],
        MedicineRequestStatusChanged::class => [
            NotifyMedicineRequestStatusChanged::class,
        ],
        StaffUnassigned::class => [
            NotifyStaffUnassigned::class,
        ],
        StoryRejected::class => [
            NotifyStoryRejected::class,
        ],
        JobApproved::class => [
            NotifyJobApproved::class,
        ],
        JobRejected::class => [
            NotifyJobRejected::class,
        ],
        DonationCompleted::class => [
            NotifyDonationCompleted::class,
        ],

        DonationCreated::class => [
            NotifyDonationCreated::class,
        ],
        InvoiceGenerated::class => [
            NotifyInvoiceGenerated::class,
        ],
        PaymentProcessed::class => [
            NotifyPaymentProcessed::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
