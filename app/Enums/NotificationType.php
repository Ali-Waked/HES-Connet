<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    // Authentication
    case USER_REGISTERED = 'user.registered';
    case USER_LOGGED_IN = 'user.logged_in';
    case EMAIL_VERIFIED = 'email.verified';
    case PASSWORD_RESET = 'password.reset';
    case PASSWORD_CHANGED = 'password.changed';

    // Appointments
    case APPOINTMENT_CREATED = 'appointment.created';
    case APPOINTMENT_CONFIRMED = 'appointment.confirmed';
    case APPOINTMENT_CANCELLED = 'appointment.cancelled';
    case APPOINTMENT_COMPLETED = 'appointment.completed';
    case APPOINTMENT_RESCHEDULED = 'appointment.rescheduled';
    case APPOINTMENT_REMINDER_24H = 'appointment.reminder_24h';
    case APPOINTMENT_REMINDER_1H = 'appointment.reminder_1h';
    case APPOINTMENT_NO_SHOW = 'appointment.no_show';

    // Doctors / Staff
    case DOCTOR_APPROVED = 'doctor.approved';
    case DOCTOR_REJECTED = 'doctor.rejected';
    case DOCTOR_REVIEWED = 'doctor.reviewed';
    case STAFF_ASSIGNED = 'staff.assigned';
    case STAFF_UNASSIGNED = 'staff.unassigned';
    case UNAVAILABILITY_APPROVED = 'unavailability.approved';
    case UNAVAILABILITY_REJECTED = 'unavailability.rejected';

    // Patients
    case REVIEW_REPLIED = 'review.replied';
    case PRESCRIPTION_CREATED = 'prescription.created';
    case PRESCRIPTION_STATUS_CHANGED = 'prescription.status_changed';
    case MEDICINE_REQUEST_CREATED = 'medicine.request.created';
    case MEDICINE_REQUEST_STATUS_CHANGED = 'medicine.request.status_changed';

    // Facilities
    case FACILITY_REGISTERED = 'facility.registered';
    case FACILITY_APPROVED = 'facility.approved';
    case FACILITY_REJECTED = 'facility.rejected';
    case FACILITY_SUSPENDED = 'facility.suspended';
    case FACILITY_REVIEWED = 'facility.reviewed';

    // Reviews
    case PLATFORM_REVIEW_SUBMITTED = 'platform.review.submitted';
    case PLATFORM_REVIEW_REPLIED = 'platform.review.replied';

    // Content
    case ARTICLE_CREATED = 'article.created';
    case ARTICLE_APPROVED = 'article.approved';
    case ARTICLE_REJECTED = 'article.rejected';
    case COMMENT_ADDED = 'comment.added';
    case STORY_CREATED = 'story.created';
    case STORY_APPROVED = 'story.approved';
    case STORY_REJECTED = 'story.rejected';

    // Donations & Payments
    case DONATION_CREATED = 'donation.created';
    case DONATION_MADE = 'donation.made';
    case DONATION_COMPLETED = 'donation.completed';
    case PAYMENT_PROCESSED = 'payment.processed';
    case PAYMENT_FAILED = 'payment.failed';
    case INVOICE_GENERATED = 'invoice.generated';

    // Jobs
    case JOB_POSTED = 'job.posted';
    case JOB_APPROVED = 'job.approved';
    case JOB_REJECTED = 'job.rejected';

    // Contact
    case CONTACT_SUBMITTED = 'contact.submitted';

    // AI
    case AI_PROMPTED = 'ai.prompted';
    case AI_CONVERSATION_COMPLETED = 'ai.conversation_completed';
    case AI_RECOMMENDATION_AVAILABLE = 'ai.recommendation_available';

    // System
    case SUBSCRIPTION_CREATED = 'subscription.created';
    case SUBSCRIPTION_EXPIRING = 'subscription.expiring';
    case MAINTENANCE_NOTICE = 'system.maintenance';
    case VERSION_ANNOUNCEMENT = 'system.version';
    case BROADCAST_NOTIFICATION = 'system.broadcast';

    public function icon(): string
    {
        return match ($this) {
            // Authentication
            self::USER_REGISTERED => 'user-plus',
            self::USER_LOGGED_IN => 'log-in',
            self::EMAIL_VERIFIED => 'mail-check',
            self::PASSWORD_RESET => 'key',
            self::PASSWORD_CHANGED => 'shield',

            // Appointments
            self::APPOINTMENT_CREATED => 'calendar-plus',
            self::APPOINTMENT_CONFIRMED => 'calendar-check',
            self::APPOINTMENT_CANCELLED => 'calendar-x',
            self::APPOINTMENT_COMPLETED => 'calendar-check',
            self::APPOINTMENT_RESCHEDULED => 'calendar-refresh',
            self::APPOINTMENT_REMINDER_24H => 'clock',
            self::APPOINTMENT_REMINDER_1H => 'bell',
            self::APPOINTMENT_NO_SHOW => 'user-x',

            // Doctors / Staff
            self::DOCTOR_APPROVED => 'user-check',
            self::DOCTOR_REJECTED => 'user-x',
            self::DOCTOR_REVIEWED => 'star',
            self::STAFF_ASSIGNED => 'user-plus',
            self::STAFF_UNASSIGNED => 'user-minus',
            self::UNAVAILABILITY_APPROVED => 'check-circle',
            self::UNAVAILABILITY_REJECTED => 'x-circle',

            // Patients
            self::REVIEW_REPLIED => 'message-square',
            self::PRESCRIPTION_CREATED => 'file-text',
            self::PRESCRIPTION_STATUS_CHANGED => 'file-text',
            self::MEDICINE_REQUEST_CREATED => 'pill',
            self::MEDICINE_REQUEST_STATUS_CHANGED => 'pill',

            // Facilities
            self::FACILITY_REGISTERED => 'building',
            self::FACILITY_APPROVED => 'building',
            self::FACILITY_REJECTED => 'building',
            self::FACILITY_SUSPENDED => 'building',
            self::FACILITY_REVIEWED => 'star',

            // Reviews
            self::PLATFORM_REVIEW_SUBMITTED => 'message-star',
            self::PLATFORM_REVIEW_REPLIED => 'message-reply',

            // Content
            self::ARTICLE_CREATED => 'file-plus',
            self::ARTICLE_APPROVED => 'file-check',
            self::ARTICLE_REJECTED => 'file-x',
            self::COMMENT_ADDED => 'message-circle',
            self::STORY_CREATED => 'book-plus',
            self::STORY_APPROVED => 'book-check',
            self::STORY_REJECTED => 'book-x',

            // Donations & Payments
            self::DONATION_CREATED => 'heart',
            self::DONATION_MADE => 'heart',
            self::DONATION_COMPLETED => 'heart',
            self::PAYMENT_PROCESSED => 'credit-card',
            self::PAYMENT_FAILED => 'credit-card',
            self::INVOICE_GENERATED => 'file-invoice',

            // Jobs
            self::JOB_POSTED => 'briefcase',
            self::JOB_APPROVED => 'briefcase',
            self::JOB_REJECTED => 'briefcase',

            // Contact
            self::CONTACT_SUBMITTED => 'mail',

            // AI
            self::AI_PROMPTED => 'cpu',
            self::AI_CONVERSATION_COMPLETED => 'bot',
            self::AI_RECOMMENDATION_AVAILABLE => 'bot',

            // System
            self::SUBSCRIPTION_CREATED => 'bell',
            self::SUBSCRIPTION_EXPIRING => 'bell',
            self::MAINTENANCE_NOTICE => 'settings',
            self::VERSION_ANNOUNCEMENT => 'megaphone',
            self::BROADCAST_NOTIFICATION => 'broadcast',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::USER_REGISTERED, self::EMAIL_VERIFIED, self::DOCTOR_APPROVED,
            self::FACILITY_APPROVED, self::STORY_APPROVED, self::ARTICLE_APPROVED,
            self::JOB_APPROVED, self::APPOINTMENT_CONFIRMED, self::APPOINTMENT_COMPLETED,
            self::PAYMENT_PROCESSED, self::DONATION_COMPLETED, self::UNAVAILABILITY_APPROVED => 'success',

            self::USER_LOGGED_IN, self::PASSWORD_RESET, self::PASSWORD_CHANGED,
            self::APPOINTMENT_REMINDER_24H, self::APPOINTMENT_REMINDER_1H,
            self::SUBSCRIPTION_EXPIRING, self::MAINTENANCE_NOTICE => 'warning',

            self::APPOINTMENT_CANCELLED, self::APPOINTMENT_NO_SHOW,
            self::DOCTOR_REJECTED, self::FACILITY_REJECTED, self::FACILITY_SUSPENDED,
            self::ARTICLE_REJECTED, self::STORY_REJECTED, self::JOB_REJECTED,
            self::PAYMENT_FAILED, self::UNAVAILABILITY_REJECTED => 'danger',

            default => 'primary',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::USER_REGISTERED, self::USER_LOGGED_IN, self::EMAIL_VERIFIED,
            self::PASSWORD_RESET, self::PASSWORD_CHANGED => 'authentication',

            self::APPOINTMENT_CREATED, self::APPOINTMENT_CONFIRMED,
            self::APPOINTMENT_CANCELLED, self::APPOINTMENT_COMPLETED,
            self::APPOINTMENT_RESCHEDULED, self::APPOINTMENT_REMINDER_24H,
            self::APPOINTMENT_REMINDER_1H, self::APPOINTMENT_NO_SHOW => 'appointments',

            self::DOCTOR_APPROVED, self::DOCTOR_REJECTED, self::DOCTOR_REVIEWED,
            self::STAFF_ASSIGNED, self::STAFF_UNASSIGNED,
            self::UNAVAILABILITY_APPROVED, self::UNAVAILABILITY_REJECTED => 'staff',

            self::REVIEW_REPLIED, self::PRESCRIPTION_CREATED,
            self::PRESCRIPTION_STATUS_CHANGED, self::MEDICINE_REQUEST_CREATED,
            self::MEDICINE_REQUEST_STATUS_CHANGED => 'patients',

            self::FACILITY_REGISTERED, self::FACILITY_APPROVED,
            self::FACILITY_REJECTED, self::FACILITY_SUSPENDED, self::FACILITY_REVIEWED => 'facilities',

            self::PLATFORM_REVIEW_SUBMITTED, self::PLATFORM_REVIEW_REPLIED => 'reviews',

            self::ARTICLE_CREATED, self::ARTICLE_APPROVED, self::ARTICLE_REJECTED,
            self::COMMENT_ADDED, self::STORY_CREATED, self::STORY_APPROVED,
            self::STORY_REJECTED => 'content',

            self::DONATION_CREATED, self::DONATION_MADE, self::DONATION_COMPLETED,
            self::PAYMENT_PROCESSED, self::PAYMENT_FAILED, self::INVOICE_GENERATED => 'finance',

            self::JOB_POSTED, self::JOB_APPROVED, self::JOB_REJECTED => 'jobs',

            self::CONTACT_SUBMITTED => 'contact',

            self::AI_PROMPTED, self::AI_CONVERSATION_COMPLETED,
            self::AI_RECOMMENDATION_AVAILABLE => 'ai',

            self::SUBSCRIPTION_CREATED, self::SUBSCRIPTION_EXPIRING,
            self::MAINTENANCE_NOTICE, self::VERSION_ANNOUNCEMENT,
            self::BROADCAST_NOTIFICATION => 'system',
        };
    }

    public function label(): string
    {
        return __("notifications.{$this->value}.title", [], 'en')
            ?: str($this->value)->replace('.', ' ')->title()->toString();
    }

    public function actionType(): string
    {
        return match ($this) {
            self::APPOINTMENT_CREATED, self::APPOINTMENT_CONFIRMED,
            self::APPOINTMENT_CANCELLED, self::APPOINTMENT_COMPLETED,
            self::APPOINTMENT_RESCHEDULED, self::APPOINTMENT_REMINDER_24H,
            self::APPOINTMENT_REMINDER_1H, self::APPOINTMENT_NO_SHOW => 'appointment',

            self::DOCTOR_REVIEWED => 'doctor',
            self::FACILITY_REVIEWED => 'facility',
            self::PRESCRIPTION_CREATED, self::PRESCRIPTION_STATUS_CHANGED => 'prescription',
            self::MEDICINE_REQUEST_CREATED, self::MEDICINE_REQUEST_STATUS_CHANGED => 'medication_request',

            self::ARTICLE_CREATED, self::ARTICLE_APPROVED, self::ARTICLE_REJECTED,
            self::COMMENT_ADDED => 'article',

            self::STORY_CREATED, self::STORY_APPROVED, self::STORY_REJECTED => 'story',

            self::DONATION_CREATED, self::DONATION_MADE, self::DONATION_COMPLETED => 'donation',

            self::PAYMENT_PROCESSED, self::PAYMENT_FAILED => 'payment',
            self::INVOICE_GENERATED => 'invoice',
            self::JOB_POSTED, self::JOB_APPROVED, self::JOB_REJECTED => 'job',
            self::PLATFORM_REVIEW_SUBMITTED, self::PLATFORM_REVIEW_REPLIED => 'platform_review',
            self::REVIEW_REPLIED => 'review',
            self::CONTACT_SUBMITTED => 'contact',
            self::AI_CONVERSATION_COMPLETED, self::AI_RECOMMENDATION_AVAILABLE => 'ai',
            self::BROADCAST_NOTIFICATION => 'broadcast',
            default => 'general',
        };
    }

    public static function fromEvent(string $event): self
    {
        return self::tryFrom($event) ?? self::BROADCAST_NOTIFICATION;
    }
}
