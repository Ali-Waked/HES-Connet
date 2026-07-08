<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\NotificationType;
use App\Events\AiPrompted;
use App\Events\AppointmentCreated;
use App\Events\AppointmentStatusChanged;
use App\Events\ArticleApproved;
use App\Events\ArticleCreated;
use App\Events\ArticleRejected;
use App\Events\BroadcastNotification;
use App\Events\CategoryCreated;
use App\Events\CategoryDeleted;
use App\Events\CategoryUpdated;
use App\Events\CommentAdded;
use App\Events\ContactMessageSubmitted;
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
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Carbon;

class DispatchNotification
{
    private array $eventMap = [];

    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
        $this->eventMap = [
            // Authentication
            UserRegistered::class => 'handleUserRegistered',
            UserLoggedIn::class => 'handleUserLoggedIn',

            // Appointments
            AppointmentCreated::class => 'handleAppointmentCreated',
            AppointmentStatusChanged::class => 'handleAppointmentStatusChanged',

            // Content
            ArticleCreated::class => 'handleArticleCreated',
            ArticleApproved::class => 'handleArticleApproved',
            ArticleRejected::class => 'handleArticleRejected',
            CommentAdded::class => 'handleCommentAdded',
            StoryCreated::class => 'handleStoryCreated',
            StoryApproved::class => 'handleStoryApproved',
            StoryRejected::class => 'handleStoryRejected',

            // Staff
            StaffAssigned::class => 'handleStaffAssigned',
            StaffUnassigned::class => 'handleStaffUnassigned',

            // Reviews
            DoctorReviewed::class => 'handleDoctorReviewed',
            FacilityReviewed::class => 'handleFacilityReviewed',
            ReviewReplied::class => 'handleReviewReplied',
            PlatformReviewSubmitted::class => 'handlePlatformReviewSubmitted',
            PlatformReviewReplied::class => 'handlePlatformReviewReplied',

            // Prescriptions & Medication
            PrescriptionCreated::class => 'handlePrescriptionCreated',
            PrescriptionStatusChanged::class => 'handlePrescriptionStatusChanged',
            MedicineRequestCreated::class => 'handleMedicineRequestCreated',
            MedicineRequestStatusChanged::class => 'handleMedicineRequestStatusChanged',

            // Donations & Payments
            DonationCreated::class => 'handleDonationCreated',
            DonationMade::class => 'handleDonationMade',
            DonationCompleted::class => 'handleDonationCompleted',
            PaymentProcessed::class => 'handlePaymentProcessed',
            InvoiceGenerated::class => 'handleInvoiceGenerated',

            // Jobs
            JobPosted::class => 'handleJobPosted',
            JobApproved::class => 'handleJobApproved',
            JobRejected::class => 'handleJobRejected',

            // Contact
            ContactMessageSubmitted::class => 'handleContactMessageSubmitted',

            // AI
            AiPrompted::class => 'handleAiPrompted',

            // Admin CRUD
            CategoryCreated::class => 'handleCategoryCreated',
            CategoryUpdated::class => 'handleCategoryUpdated',
            CategoryDeleted::class => 'handleCategoryDeleted',
            TagCreated::class => 'handleTagCreated',
            TagUpdated::class => 'handleTagUpdated',
            TagDeleted::class => 'handleTagDeleted',
            SymptomCreated::class => 'handleSymptomCreated',
            SymptomUpdated::class => 'handleSymptomUpdated',
            SymptomDeleted::class => 'handleSymptomDeleted',

            // System
            BroadcastNotification::class => 'handleBroadcastNotification',
        ];
    }

    public function subscribe(Dispatcher $events): void
    {
        foreach (array_keys($this->eventMap) as $eventClass) {
            $events->listen($eventClass, self::class);
        }
    }

    public function handle(object $event): void
    {
        $handler = $this->eventMap[$event::class] ?? null;

        if ($handler !== null) {
            $this->$handler($event);
        }
    }

    // =========================================================================
    // Authentication
    // =========================================================================

    private function handleUserRegistered(UserRegistered $event): void
    {
        $user = $event->user;

        $this->notificationService->notify(
            $user,
            NotificationType::USER_REGISTERED,
            __('notifications.user.registered.title', [], $user->locale?->value ?? app()->getLocale()),
            __('notifications.user.registered.body', ['name' => $user->name], $user->locale?->value ?? app()->getLocale()),
            null,
            $user->uuid,
        );

        foreach ($this->getSuperAdmins() as $admin) {
            $this->notificationService->notify(
                $admin,
                NotificationType::USER_REGISTERED,
                'New User Registration',
                "New user {$user->name} ({$user->email}) has registered.",
                null,
                $user->uuid,
                'system',
            );
        }
    }

    private function handleUserLoggedIn(UserLoggedIn $event): void
    {
        $user = $event->user;

        $this->notificationService->notify(
            $user,
            NotificationType::USER_LOGGED_IN,
            'New Login Detected',
            'We detected a new login to your account.',
            null,
            $user->uuid,
        );
    }

    // =========================================================================
    // Appointments
    // =========================================================================

    private function handleAppointmentCreated(AppointmentCreated $event): void
    {
        $appointment = $event->appointment;
        $date = $appointment->start_at instanceof Carbon
            ? $appointment->start_at->format('Y-m-d H:i')
            : $appointment->start_at;

        $patient = $appointment->patient?->user;
        if ($patient) {
            $this->notificationService->notify(
                $patient,
                NotificationType::APPOINTMENT_CREATED,
                'Appointment Scheduled',
                "Your appointment with Dr. {$appointment->facilityStaff?->staff?->user?->name} has been scheduled for {$date}.",
                route('dashboard.appointments.show', $appointment),
                $appointment->uuid,
            );
        }

        $doctor = $appointment->facilityStaff?->staff?->user;
        if ($doctor) {
            $this->notificationService->notify(
                $doctor,
                NotificationType::APPOINTMENT_CREATED,
                'New Appointment',
                "A new appointment with {$appointment->patient?->user?->name} on {$date}.",
                route('dashboard.appointments.show', $appointment),
                $appointment->uuid,
            );
        }
    }

    private function handleAppointmentStatusChanged(AppointmentStatusChanged $event): void
    {
        $appointment = $event->appointment;

        $type = match ($appointment->status->value) {
            'confirmed' => NotificationType::APPOINTMENT_CONFIRMED,
            'cancelled' => NotificationType::APPOINTMENT_CANCELLED,
            'completed' => NotificationType::APPOINTMENT_COMPLETED,
            'rescheduled' => NotificationType::APPOINTMENT_RESCHEDULED,
            'no_show' => NotificationType::APPOINTMENT_NO_SHOW,
            default => NotificationType::APPOINTMENT_CREATED,
        };

        $title = match ($appointment->status->value) {
            'confirmed' => 'Appointment Confirmed',
            'cancelled' => 'Appointment Cancelled',
            'completed' => 'Appointment Completed',
            'rescheduled' => 'Appointment Rescheduled',
            'no_show' => 'Patient Did Not Show',
            default => 'Appointment Updated',
        };

        $patient = $appointment->patient?->user;
        if ($patient) {
            $this->notificationService->notify(
                $patient,
                $type,
                $title,
                "Your appointment with Dr. {$appointment->facilityStaff?->staff?->user?->name} has been {$appointment->status->value}.",
                route('dashboard.appointments.show', $appointment),
                $appointment->uuid,
            );
        }

        $doctor = $appointment->facilityStaff?->staff?->user;
        if ($doctor) {
            $this->notificationService->notify(
                $doctor,
                $type,
                $title,
                "Appointment with {$appointment->patient?->user?->name} has been {$appointment->status->value}.",
                route('dashboard.appointments.show', $appointment),
                $appointment->uuid,
            );
        }
    }

    // =========================================================================
    // Articles
    // =========================================================================

    private function handleArticleCreated(ArticleCreated $event): void
    {
        $article = $event->article;

        if ($article->author) {
            $this->notificationService->notify(
                $article->author,
                NotificationType::ARTICLE_CREATED,
                'Article Published',
                "Your article \"{$article->title}\" has been published.",
                route('dashboard.articles.show', $article),
                $article->uuid,
            );
        }

        $this->notificationService->notifyAdmins(
            NotificationType::ARTICLE_CREATED,
            'New Article Published',
            "{$article->author?->name} has created a new article \"{$article->title}\".",
            route('dashboard.articles.show', $article),
            $article->uuid,
        );
    }

    private function handleArticleApproved(ArticleApproved $event): void
    {
        $article = $event->article;

        if ($article->author) {
            $this->notificationService->notify(
                $article->author,
                NotificationType::ARTICLE_APPROVED,
                'Article Approved',
                "Your article \"{$article->title}\" has been approved and is now live.",
                route('dashboard.articles.show', $article),
                $article->uuid,
            );
        }
    }

    private function handleArticleRejected(ArticleRejected $event): void
    {
        $article = $event->article;

        if ($article->author) {
            $this->notificationService->notify(
                $article->author,
                NotificationType::ARTICLE_REJECTED,
                'Article Rejected',
                "Your article \"{$article->title}\" has been rejected.",
                route('dashboard.articles.show', $article),
                $article->uuid,
            );
        }
    }

    // =========================================================================
    // Comments
    // =========================================================================

    private function handleCommentAdded(CommentAdded $event): void
    {
        $comment = $event->comment;
        $article = $comment->article;

        if ($article?->author) {
            $this->notificationService->notify(
                $article->author,
                NotificationType::COMMENT_ADDED,
                'New Comment',
                "{$comment->user?->name} commented on your article \"{$article->title}\".",
                route('dashboard.articles.show', $article),
                $article->uuid,
            );
        }
    }

    // =========================================================================
    // Stories
    // =========================================================================

    private function handleStoryCreated(StoryCreated $event): void
    {
        $story = $event->story;

        $this->notificationService->notifyAdmins(
            NotificationType::STORY_CREATED,
            'New Story Shared',
            "A new story \"{$story->title}\" has been shared.",
            route('dashboard.stories.show', $story),
            $story->uuid,
        );

        $user = $story->patient?->user;
        if ($user) {
            $this->notificationService->notify(
                $user,
                NotificationType::STORY_CREATED,
                'Story Created',
                "Your story \"{$story->title}\" has been submitted for review.",
                route('dashboard.stories.show', $story),
                $story->uuid,
            );
        }
    }

    private function handleStoryApproved(StoryApproved $event): void
    {
        $story = $event->story;

        $user = $story->patient?->user;
        if ($user) {
            $this->notificationService->notify(
                $user,
                NotificationType::STORY_APPROVED,
                'Story Approved',
                "Your story \"{$story->title}\" has been approved and is now live.",
                route('dashboard.stories.show', $story),
                $story->uuid,
            );
        }
    }

    private function handleStoryRejected(StoryRejected $event): void
    {
        $story = $event->story;

        $user = $story->patient?->user;
        if ($user) {
            $this->notificationService->notify(
                $user,
                NotificationType::STORY_REJECTED,
                'Story Rejected',
                "Your story \"{$story->title}\" has been rejected.",
                route('dashboard.stories.show', $story),
                $story->uuid,
            );
        }
    }

    // =========================================================================
    // Staff
    // =========================================================================

    private function handleStaffAssigned(StaffAssigned $event): void
    {
        $facilityStaff = $event->facilityStaff;
        $user = $facilityStaff->staff?->user;
        $facility = $facilityStaff->facility;

        if ($user) {
            $this->notificationService->notify(
                $user,
                NotificationType::STAFF_ASSIGNED,
                'Facility Assignment',
                "You have been assigned to {$facility?->name} as {$facilityStaff->position}.",
                route('dashboard.facilities.show', $facility),
                $facility?->uuid,
            );
        }
    }

    private function handleStaffUnassigned(StaffUnassigned $event): void
    {
        $facilityStaff = $event->facilityStaff;
        $user = $facilityStaff->staff?->user;
        $facility = $facilityStaff->facility;

        if ($user) {
            $this->notificationService->notify(
                $user,
                NotificationType::STAFF_UNASSIGNED,
                'Facility Unassignment',
                "You have been unassigned from {$facility?->name}.",
                null,
                $facility?->uuid,
            );
        }
    }

    // =========================================================================
    // Reviews
    // =========================================================================

    private function handleDoctorReviewed(DoctorReviewed $event): void
    {
        $review = $event->review;
        $doctor = $review?->appointment?->facilityStaff?->staff?->user;
        $patient = $review->patient?->user;

        if ($doctor) {
            $this->notificationService->notify(
                $doctor,
                NotificationType::DOCTOR_REVIEWED,
                'New Review Received',
                "{$patient?->name} reviewed you with {$review->rating}/5.",
                null,
                $review->uuid,
            );
        }
    }

    private function handleFacilityReviewed(FacilityReviewed $event): void
    {
        $facilityReview = $event->facilityReview;
        $facility = $facilityReview->facility;

        $this->notificationService->notifyFacilityAdmins(
            $facility->id,
            NotificationType::FACILITY_REVIEWED,
            'New Facility Review',
            "{$facilityReview->patient?->user?->name} rated {$facility->name} {$facilityReview->rating}/5.",
            null,
            $facility->uuid,
        );

        $this->notificationService->notifyAdmins(
            NotificationType::FACILITY_REVIEWED,
            'New Facility Review',
            "{$facilityReview->patient?->user?->name} rated {$facility->name} {$facilityReview->rating}/5.",
            null,
            $facility->uuid,
        );
    }

    private function handleReviewReplied(ReviewReplied $event): void
    {
        $reviewReply = $event->reviewReply;
        $review = $reviewReply->review;
        $patient = $review?->patient?->user;

        if ($patient) {
            $this->notificationService->notify(
                $patient,
                NotificationType::REVIEW_REPLIED,
                'Review Reply',
                'A reply has been added to your review.',
                null,
                $review->uuid,
            );
        }
    }

    private function handlePlatformReviewSubmitted(PlatformReviewSubmitted $event): void
    {
        $platformReview = $event->platformReview;

        $this->notificationService->notifyAdmins(
            NotificationType::PLATFORM_REVIEW_SUBMITTED,
            'New Platform Review',
            "{$platformReview->user?->name} submitted a platform review with {$platformReview->rating}/5 rating.",
            null,
            $platformReview->uuid,
        );
    }

    private function handlePlatformReviewReplied(PlatformReviewReplied $event): void
    {
        $platformReview = $event->platformReview;

        if ($platformReview->user) {
            $this->notificationService->notify(
                $platformReview->user,
                NotificationType::PLATFORM_REVIEW_REPLIED,
                'Review Reply Received',
                'An admin has replied to your platform review.',
                null,
                $platformReview->uuid,
            );
        }
    }

    // =========================================================================
    // Prescriptions & Medication
    // =========================================================================

    private function handlePrescriptionCreated(PrescriptionCreated $event): void
    {
        $prescription = $event->prescription;
        $patient = $prescription->appointment?->patient?->user;

        if ($patient) {
            $this->notificationService->notify(
                $patient,
                NotificationType::PRESCRIPTION_CREATED,
                'Prescription Issued',
                'A new prescription has been issued for you.',
                null,
                $prescription->uuid,
            );
        }
    }

    private function handlePrescriptionStatusChanged(PrescriptionStatusChanged $event): void
    {
        $prescription = $event->prescription;
        $patient = $prescription->appointment?->patient?->user;

        if ($patient) {
            $this->notificationService->notify(
                $patient,
                NotificationType::PRESCRIPTION_STATUS_CHANGED,
                'Prescription Updated',
                "Your prescription status changed to {$prescription->status->value}.",
                null,
                $prescription->uuid,
            );
        }
    }

    private function handleMedicineRequestCreated(MedicineRequestCreated $event): void
    {
        $request = $event->medicationRequest;
        $patient = $request->patient?->user;

        if ($patient) {
            $this->notificationService->notify(
                $patient,
                NotificationType::MEDICINE_REQUEST_CREATED,
                'Medicine Request Created',
                "Your medicine request has been created at {$request->facility?->name}.",
                null,
                $request->uuid,
            );
        }
    }

    private function handleMedicineRequestStatusChanged(MedicineRequestStatusChanged $event): void
    {
        $request = $event->medicationRequest;
        $patient = $request->patient?->user;

        if ($patient) {
            $this->notificationService->notify(
                $patient,
                NotificationType::MEDICINE_REQUEST_STATUS_CHANGED,
                'Medicine Request Updated',
                "Your medicine request status changed to {$request->status->value}.",
                null,
                $request->uuid,
            );
        }
    }

    // =========================================================================
    // Donations & Payments
    // =========================================================================

    private function handleDonationCreated(DonationCreated $event): void
    {
        $donation = $event->donation;

        $this->notificationService->notifyAdmins(
            NotificationType::DONATION_CREATED,
            'Donation Initiated',
            "{$donation->donor?->name} initiated a donation of {$donation->amount} {$donation->currency}.",
            null,
            $donation->uuid,
        );
    }

    private function handleDonationMade(DonationMade $event): void
    {
        $campaign = $event->campaign ?? 'a campaign';

        $this->notificationService->notifyAdmins(
            NotificationType::DONATION_MADE,
            'Donation Received',
            "{$event->donorName} donated {$event->amount} to {$campaign}.",
        );
    }

    private function handleDonationCompleted(DonationCompleted $event): void
    {
        $donation = $event->donation;
        $story = $donation->story;

        $donor = $donation->donor;
        if ($donor) {
            $this->notificationService->notify(
                $donor,
                NotificationType::DONATION_COMPLETED,
                'Donation Completed',
                "Your donation of {$donation->amount} {$donation->currency} to {$story?->title} has been completed.",
                null,
                $donation->uuid,
            );
        }

        $patient = $story?->patient?->user;
        if ($patient) {
            $this->notificationService->notify(
                $patient,
                NotificationType::DONATION_COMPLETED,
                'Donation Received',
                "You received a donation of {$donation->amount} {$donation->currency} for your story \"{$story?->title}\".",
                null,
                $donation->uuid,
            );
        }
    }

    private function handlePaymentProcessed(PaymentProcessed $event): void
    {
        $payment = $event->payment;

        $this->notificationService->notifyAdmins(
            NotificationType::PAYMENT_PROCESSED,
            'Payment Processed',
            "A payment of {$payment->amount} has been processed successfully.",
            null,
            $payment->uuid,
        );
    }

    private function handleInvoiceGenerated(InvoiceGenerated $event): void
    {
        $invoice = $event->invoice;

        $this->notificationService->notify(
            User::find($invoice->user_id),
            NotificationType::INVOICE_GENERATED,
            'Invoice Generated',
            "Invoice #{$invoice->id} for {$invoice->total_amount} {$invoice->currency} has been generated.",
            null,
            $invoice->uuid,
        );
    }

    // =========================================================================
    // Jobs
    // =========================================================================

    private function handleJobPosted(JobPosted $event): void
    {
        $jobPost = $event->jobPost;

        $this->notificationService->notifyAdmins(
            NotificationType::JOB_POSTED,
            'New Job Opportunity',
            "A new job \"{$jobPost->title}\" has been posted at {$jobPost->facility?->name}.",
            null,
            $jobPost->uuid,
        );
    }

    private function handleJobApproved(JobApproved $event): void
    {
        $jobPost = $event->jobPost;

        if ($jobPost->user) {
            $this->notificationService->notify(
                $jobPost->user,
                NotificationType::JOB_APPROVED,
                'Job Post Approved',
                "Your job posting \"{$jobPost->title}\" has been approved and is now live.",
                null,
                $jobPost->uuid,
            );
        }
    }

    private function handleJobRejected(JobRejected $event): void
    {
        $jobPost = $event->jobPost;

        if ($jobPost->user) {
            $this->notificationService->notify(
                $jobPost->user,
                NotificationType::JOB_REJECTED,
                'Job Post Rejected',
                "Your job posting \"{$jobPost->title}\" has been rejected.".($event->reason ? " Reason: {$event->reason}" : ''),
                null,
                $jobPost->uuid,
            );
        }
    }

    // =========================================================================
    // Contact
    // =========================================================================

    private function handleContactMessageSubmitted(ContactMessageSubmitted $event): void
    {
        $message = $event->contactMessage;

        $this->notificationService->notifyAdmins(
            NotificationType::CONTACT_SUBMITTED,
            'New Contact Message',
            "{$message->name} ({$message->email}) sent a new message.",
        );
    }

    // =========================================================================
    // AI
    // =========================================================================

    private function handleAiPrompted(AiPrompted $event): void
    {
        $user = $event->userId ? User::find($event->userId) : null;
        $name = $user?->name ?? 'Unknown';

        $this->notificationService->notifyAdmins(
            NotificationType::AI_PROMPTED,
            'AI Assistant Used',
            "An AI query was made by {$name}.",
        );
    }

    // =========================================================================
    // Admin CRUD
    // =========================================================================

    private function handleCategoryCreated(CategoryCreated $event): void
    {
        $category = $event->category;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('category.created'),
            'New Category Created',
            "A new category \"{$category->name}\" has been created.",
        );
    }

    private function handleCategoryUpdated(CategoryUpdated $event): void
    {
        $category = $event->category;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('category.updated'),
            'Category Updated',
            "The category \"{$category->name}\" has been updated.",
        );
    }

    private function handleCategoryDeleted(CategoryDeleted $event): void
    {
        $category = $event->category;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('category.deleted'),
            'Category Deleted',
            "The category \"{$category->name}\" has been deleted.",
        );
    }

    private function handleTagCreated(TagCreated $event): void
    {
        $tag = $event->tag;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('tag.created'),
            'New Tag Created',
            "A new tag \"{$tag->name}\" has been created.",
        );
    }

    private function handleTagUpdated(TagUpdated $event): void
    {
        $tag = $event->tag;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('tag.updated'),
            'Tag Updated',
            "The tag \"{$tag->name}\" has been updated.",
        );
    }

    private function handleTagDeleted(TagDeleted $event): void
    {
        $tag = $event->tag;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('tag.deleted'),
            'Tag Deleted',
            "The tag \"{$tag->name}\" has been deleted.",
        );
    }

    private function handleSymptomCreated(SymptomCreated $event): void
    {
        $symptom = $event->symptom;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('symptom.created'),
            'New Symptom Created',
            "A new symptom \"{$symptom->name}\" has been added.",
        );
    }

    private function handleSymptomUpdated(SymptomUpdated $event): void
    {
        $symptom = $event->symptom;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('symptom.updated'),
            'Symptom Updated',
            "The symptom \"{$symptom->name}\" has been updated.",
        );
    }

    private function handleSymptomDeleted(SymptomDeleted $event): void
    {
        $symptom = $event->symptom;

        $this->notificationService->notifyAdmins(
            NotificationType::fromEvent('symptom.deleted'),
            'Symptom Deleted',
            "The symptom \"{$symptom->name}\" has been removed.",
        );
    }

    // =========================================================================
    // System
    // =========================================================================

    private function handleBroadcastNotification(BroadcastNotification $event): void
    {
        $this->notificationService->notifyAdmins(
            NotificationType::BROADCAST_NOTIFICATION,
            $event->title,
            $event->message,
            $event->actionUrl,
            $event->entityUuid,
        );
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function getSuperAdmins(): iterable
    {
        return User::whereHas('systemRoles', fn ($q) => $q->where('slug', 'super_admin'))->get();
    }
}
