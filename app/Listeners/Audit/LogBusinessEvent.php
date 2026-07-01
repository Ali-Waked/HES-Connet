<?php

declare(strict_types=1);

namespace App\Listeners\Audit;

use App\Events\AppointmentCreated;
use App\Events\AppointmentStatusChanged;
use App\Events\ArticleApproved;
use App\Events\ArticleCreated;
use App\Events\ArticleRejected;
use App\Events\CommentAdded;
use App\Events\DonationCompleted;
use App\Events\DonationCreated;
use App\Events\DonationMade;
use App\Events\InvoiceGenerated;
use App\Events\JobApproved;
use App\Events\JobPosted;
use App\Events\JobRejected;
use App\Events\PaymentProcessed;
use App\Events\ReviewReplied;
use App\Events\StaffAssigned;
use App\Events\StaffUnassigned;
use App\Events\StoryApproved;
use App\Events\StoryCreated;
use App\Events\StoryRejected;
use App\Services\AuditLogService;
use Illuminate\Events\Dispatcher;

class LogBusinessEvent
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function handleDonationCreated(DonationCreated $event): void
    {
        $this->auditLogService->logBusiness('donation_created', $event->donation);
    }

    public function handleDonationCompleted(DonationCompleted $event): void
    {
        $this->auditLogService->logBusiness('donation_completed', $event->donation);
    }

    public function handleDonationMade(DonationMade $event): void
    {
        $this->auditLogService->log(
            action: 'donation_made',
            tableName: 'donations',
            newValues: [
                'donor_name' => $event->donorName,
                'amount' => $event->amount,
                'campaign' => $event->campaign,
            ],
        );
    }

    public function handleStoryCreated(StoryCreated $event): void
    {
        $this->auditLogService->logBusiness('story_created', $event->story);
    }

    public function handleStoryApproved(StoryApproved $event): void
    {
        $this->auditLogService->logBusiness('story_approved', $event->story);
    }

    public function handleStoryRejected(StoryRejected $event): void
    {
        $this->auditLogService->logBusiness('story_rejected', $event->story);
    }

    public function handleAppointmentCreated(AppointmentCreated $event): void
    {
        $this->auditLogService->logBusiness('appointment_created', $event->appointment);
    }

    public function handleAppointmentStatusChanged(AppointmentStatusChanged $event): void
    {
        $this->auditLogService->logBusiness('appointment_status_changed', $event->appointment);
    }

    public function handleArticleCreated(ArticleCreated $event): void
    {
        $this->auditLogService->logBusiness('article_created', $event->article);
    }

    public function handleArticleApproved(ArticleApproved $event): void
    {
        $this->auditLogService->logBusiness('article_approved', $event->article);
    }

    public function handleArticleRejected(ArticleRejected $event): void
    {
        $this->auditLogService->logBusiness('article_rejected', $event->article);
    }

    public function handleCommentAdded(CommentAdded $event): void
    {
        $this->auditLogService->logBusiness('comment_added', $event->comment);
    }

    public function handleJobPosted(JobPosted $event): void
    {
        $this->auditLogService->logBusiness('job_posted', $event->jobPost);
    }

    public function handleJobApproved(JobApproved $event): void
    {
        $this->auditLogService->logBusiness('job_approved', $event->jobPost);
    }

    public function handleJobRejected(JobRejected $event): void
    {
        $this->auditLogService->logBusiness('job_rejected', $event->jobPost);
    }

    public function handleStaffAssigned(StaffAssigned $event): void
    {
        $this->auditLogService->logBusiness('staff_assigned', $event->facilityStaff);
    }

    public function handleStaffUnassigned(StaffUnassigned $event): void
    {
        $this->auditLogService->logBusiness('staff_unassigned', $event->facilityStaff);
    }

    public function handleInvoiceGenerated(InvoiceGenerated $event): void
    {
        $this->auditLogService->logBusiness('invoice_generated', $event->invoice);
    }

    public function handlePaymentProcessed(PaymentProcessed $event): void
    {
        $this->auditLogService->logBusiness('payment_processed', $event->payment);
    }

    public function handleReviewReplied(ReviewReplied $event): void
    {
        $this->auditLogService->logBusiness('review_replied', $event->reviewReply);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(DonationCreated::class, [self::class, 'handleDonationCreated']);
        $events->listen(DonationCompleted::class, [self::class, 'handleDonationCompleted']);
        $events->listen(DonationMade::class, [self::class, 'handleDonationMade']);
        $events->listen(StoryCreated::class, [self::class, 'handleStoryCreated']);
        $events->listen(StoryApproved::class, [self::class, 'handleStoryApproved']);
        $events->listen(StoryRejected::class, [self::class, 'handleStoryRejected']);
        $events->listen(AppointmentCreated::class, [self::class, 'handleAppointmentCreated']);
        $events->listen(AppointmentStatusChanged::class, [self::class, 'handleAppointmentStatusChanged']);
        $events->listen(ArticleCreated::class, [self::class, 'handleArticleCreated']);
        $events->listen(ArticleApproved::class, [self::class, 'handleArticleApproved']);
        $events->listen(ArticleRejected::class, [self::class, 'handleArticleRejected']);
        $events->listen(CommentAdded::class, [self::class, 'handleCommentAdded']);
        $events->listen(JobPosted::class, [self::class, 'handleJobPosted']);
        $events->listen(JobApproved::class, [self::class, 'handleJobApproved']);
        $events->listen(JobRejected::class, [self::class, 'handleJobRejected']);
        $events->listen(StaffAssigned::class, [self::class, 'handleStaffAssigned']);
        $events->listen(StaffUnassigned::class, [self::class, 'handleStaffUnassigned']);
        $events->listen(InvoiceGenerated::class, [self::class, 'handleInvoiceGenerated']);
        $events->listen(PaymentProcessed::class, [self::class, 'handlePaymentProcessed']);
        $events->listen(ReviewReplied::class, [self::class, 'handleReviewReplied']);
    }
}
