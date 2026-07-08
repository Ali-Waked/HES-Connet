<?php

return [
    // Authentication
    'user.registered' => [
        'title' => 'Welcome to Health Ecosystem',
        'body' => 'Welcome :name! Your account has been created successfully.',
        'sms' => 'Welcome :name! Your account has been created.',
    ],
    'user.logged_in' => [
        'title' => 'New Login Detected',
        'body' => 'We detected a new login to your account.',
        'sms' => 'New login detected on your account.',
    ],
    'email.verified' => [
        'title' => 'Email Verified',
        'body' => 'Your email address has been verified successfully.',
        'sms' => 'Your email has been verified.',
    ],
    'password.reset' => [
        'title' => 'Password Reset Requested',
        'body' => 'A password reset was requested for your account.',
        'sms' => 'Password reset requested.',
    ],
    'password.changed' => [
        'title' => 'Password Changed',
        'body' => 'Your password has been changed successfully.',
        'sms' => 'Your password has been changed.',
    ],

    // Appointments
    'appointment.created' => [
        'title' => 'Appointment Scheduled',
        'body' => 'Your appointment with Dr. :doctor has been scheduled for :start_at.',
        'sms' => 'Appointment with Dr. :doctor on :start_at.',
    ],
    'appointment.confirmed' => [
        'title' => 'Appointment Confirmed',
        'body' => 'Your appointment with Dr. :doctor has been confirmed for :start_at.',
        'sms' => 'Appointment confirmed.',
    ],
    'appointment.cancelled' => [
        'title' => 'Appointment Cancelled',
        'body' => 'Your appointment with Dr. :doctor has been cancelled.',
        'sms' => 'Appointment cancelled.',
    ],
    'appointment.completed' => [
        'title' => 'Appointment Completed',
        'body' => 'Your appointment with Dr. :doctor has been completed.',
        'sms' => 'Appointment completed.',
    ],
    'appointment.rescheduled' => [
        'title' => 'Appointment Rescheduled',
        'body' => 'Your appointment with Dr. :doctor has been rescheduled to :new_start.',
        'sms' => 'Appointment rescheduled.',
    ],
    'appointment.reminder_24h' => [
        'title' => 'Appointment Reminder (24h)',
        'body' => 'You have an appointment with Dr. :doctor tomorrow at :start_at.',
        'sms' => 'Appointment with Dr. :doctor tomorrow at :start_at.',
    ],
    'appointment.reminder_1h' => [
        'title' => 'Appointment Reminder (1h)',
        'body' => 'You have an appointment with Dr. :doctor in 1 hour at :start_at.',
        'sms' => 'Appointment with Dr. :doctor in 1 hour.',
    ],
    'appointment.no_show' => [
        'title' => 'Patient Did Not Show',
        'body' => 'Patient :patient did not show for their appointment.',
        'sms' => 'Patient :patient was a no-show.',
    ],

    // Doctors / Staff
    'doctor.approved' => [
        'title' => 'Doctor Approved',
        'body' => 'Your doctor profile has been approved.',
        'sms' => 'Doctor profile approved.',
    ],
    'doctor.rejected' => [
        'title' => 'Doctor Rejected',
        'body' => 'Your doctor profile has been rejected. Reason: :reason',
        'sms' => 'Doctor profile rejected.',
    ],
    'doctor.reviewed' => [
        'title' => 'New Doctor Review',
        'body' => ':patient reviewed Dr. :doctor with :rating/5.',
        'sms' => ':patient reviewed Dr. :doctor with :rating/5.',
    ],
    'staff.assigned' => [
        'title' => 'Facility Assignment',
        'body' => 'You have been assigned to :facility as :position.',
        'sms' => 'You were assigned to :facility as :position.',
    ],
    'staff.unassigned' => [
        'title' => 'Facility Unassignment',
        'body' => 'You have been unassigned from :facility as :position.',
        'sms' => 'You were unassigned from :facility as :position.',
    ],
    'unavailability.approved' => [
        'title' => 'Unavailability Approved',
        'body' => 'Your leave request has been approved.',
        'sms' => 'Leave request approved.',
    ],
    'unavailability.rejected' => [
        'title' => 'Unavailability Rejected',
        'body' => 'Your leave request has been rejected.',
        'sms' => 'Leave request rejected.',
    ],

    // Patients
    'review.replied' => [
        'title' => 'Review Reply',
        'body' => 'A reply has been added to your review.',
        'sms' => 'Your review has been replied to.',
    ],
    'prescription.created' => [
        'title' => 'Prescription Issued',
        'body' => 'A new prescription has been issued by Dr. :doctor.',
        'sms' => 'New prescription from Dr. :doctor.',
    ],
    'prescription.status_changed' => [
        'title' => 'Prescription Updated',
        'body' => 'Your prescription status changed to :status.',
        'sms' => 'Prescription: :status.',
    ],
    'medicine.request.created' => [
        'title' => 'Medicine Request',
        'body' => ':patient has requested medicine at :facility.',
        'sms' => 'Medicine request from :patient.',
    ],
    'medicine.request.status_changed' => [
        'title' => 'Medicine Request Updated',
        'body' => 'Your medicine request status changed to :status.',
        'sms' => 'Medicine request: :status.',
    ],

    // Facilities
    'facility.registered' => [
        'title' => 'New Facility Registration',
        'body' => ':facility has registered on the platform.',
        'sms' => 'New facility: :facility registered.',
    ],
    'facility.approved' => [
        'title' => 'Facility Approved',
        'body' => ':facility has been approved.',
        'sms' => ':facility approved.',
    ],
    'facility.rejected' => [
        'title' => 'Facility Rejected',
        'body' => ':facility has been rejected. Reason: :reason',
        'sms' => ':facility rejected.',
    ],
    'facility.suspended' => [
        'title' => 'Facility Suspended',
        'body' => ':facility has been suspended.',
        'sms' => ':facility suspended.',
    ],
    'facility.reviewed' => [
        'title' => 'New Facility Review',
        'body' => ':patient rated :facility :rating/5.',
        'sms' => ':patient rated :facility :rating/5.',
    ],

    // Platform Reviews
    'platform.review.submitted' => [
        'title' => 'New Platform Review',
        'body' => ':user submitted a platform review with :rating/5 rating.',
        'sms' => 'New platform review from :user.',
    ],
    'platform.review.replied' => [
        'title' => 'Review Reply Received',
        'body' => 'An admin has replied to your platform review.',
        'sms' => 'An admin replied to your review.',
    ],

    // Content
    'article.created' => [
        'title' => 'New Article Published',
        'body' => ':author has created a new article ":title".',
        'sms' => 'New article ":title" has been created.',
    ],
    'article.approved' => [
        'title' => 'Article Approved',
        'body' => 'Your article ":title" has been approved and is now live.',
        'sms' => 'Your article ":title" has been approved.',
    ],
    'article.rejected' => [
        'title' => 'Article Rejected',
        'body' => 'Your article ":title" has been rejected. Reason: :reason',
        'sms' => 'Your article ":title" was rejected.',
    ],
    'comment.added' => [
        'title' => 'New Comment',
        'body' => ':author commented on your article ":article".',
        'sms' => ':author commented on ":article".',
    ],
    'story.created' => [
        'title' => 'New Story Shared',
        'body' => 'A new story ":title" has been shared.',
        'sms' => 'New story ":title" has been shared.',
    ],
    'story.approved' => [
        'title' => 'Story Approved',
        'body' => 'Your story ":title" has been approved and is now live.',
        'sms' => 'Your story ":title" has been approved.',
    ],
    'story.rejected' => [
        'title' => 'Story Rejected',
        'body' => 'Your story ":title" has been rejected.',
        'sms' => 'Your story ":title" was rejected.',
    ],

    // Donations & Payments
    'donation.created' => [
        'title' => 'Donation Initiated',
        'body' => ':name initiated a donation of :amount.',
        'sms' => ':name initiated a donation.',
    ],
    'donation.made' => [
        'title' => 'Donation Received',
        'body' => ':name donated :amount to :campaign.',
        'sms' => ':name donated :amount.',
    ],
    'donation.completed' => [
        'title' => 'Donation Completed',
        'body' => 'Your donation of :amount to :story has been successfully completed.',
        'sms' => 'Donation completed.',
    ],
    'payment.processed' => [
        'title' => 'Payment Processed',
        'body' => 'A payment of :amount has been processed successfully.',
        'sms' => 'Payment of :amount processed.',
    ],
    'payment.failed' => [
        'title' => 'Payment Failed',
        'body' => 'A payment of :amount has failed. Reason: :error',
        'sms' => 'Payment of :amount failed.',
    ],
    'invoice.generated' => [
        'title' => 'Invoice Generated',
        'body' => 'Invoice #:invoice_number for :total_amount :currency has been generated.',
        'sms' => 'Invoice for :total_amount generated.',
    ],

    // Jobs
    'job.posted' => [
        'title' => 'New Job Opportunity',
        'body' => 'A new job ":title" has been posted at :facility.',
        'sms' => 'New job ":title" posted at :facility.',
    ],
    'job.approved' => [
        'title' => 'Job Post Approved',
        'body' => 'Your job posting ":title" has been approved and is now live.',
        'sms' => 'Your job ":title" has been approved.',
    ],
    'job.rejected' => [
        'title' => 'Job Post Rejected',
        'body' => 'Your job posting ":title" has been rejected. Reason: :reason',
        'sms' => 'Your job ":title" was rejected.',
    ],

    // Contact
    'contact.submitted' => [
        'title' => 'New Contact Message',
        'body' => ':name (:email) sent a new message.',
        'sms' => 'New contact message from :name.',
    ],

    // AI
    'ai.prompted' => [
        'title' => 'AI Assistant Used',
        'body' => 'An AI query was made by :user.',
        'sms' => 'AI query by :user.',
    ],
    'ai.conversation_completed' => [
        'title' => 'AI Consultation Completed',
        'body' => 'Your AI consultation has been completed.',
        'sms' => 'AI consultation completed.',
    ],
    'ai.recommendation_available' => [
        'title' => 'Doctor Recommendation Available',
        'body' => 'A doctor recommendation is available based on your consultation.',
        'sms' => 'Doctor recommendation available.',
    ],

    // System
    'subscription.created' => [
        'title' => 'Subscription Created',
        'body' => 'Your subscription has been created successfully.',
        'sms' => 'Subscription created.',
    ],
    'subscription.expiring' => [
        'title' => 'Subscription Expiring Soon',
        'body' => 'Your subscription will expire on :date.',
        'sms' => 'Subscription expiring.',
    ],
    'system.maintenance' => [
        'title' => 'Maintenance Notice',
        'body' => 'The system will be under maintenance on :date from :start to :end.',
        'sms' => 'System maintenance scheduled.',
    ],
    'system.version' => [
        'title' => 'Version Announcement',
        'body' => 'The platform has been updated to version :version.',
        'sms' => 'Platform updated to v:version.',
    ],
    'system.broadcast' => [
        'title' => ':title',
        'body' => ':message',
        'sms' => ':message',
    ],

    // Admin CRUD (keep existing)
    'category.created' => [
        'title' => 'New Category Created',
        'body' => 'A new category ":name" has been created.',
        'sms' => 'New category ":name" created.',
    ],
    'category.updated' => [
        'title' => 'Category Updated',
        'body' => 'The category ":name" has been updated.',
        'sms' => 'Category ":name" updated.',
    ],
    'category.deleted' => [
        'title' => 'Category Deleted',
        'body' => 'The category ":name" has been deleted.',
        'sms' => 'Category ":name" deleted.',
    ],
    'tag.created' => [
        'title' => 'New Tag Created',
        'body' => 'A new tag ":name" has been created.',
        'sms' => 'New tag ":name" created.',
    ],
    'tag.updated' => [
        'title' => 'Tag Updated',
        'body' => 'The tag ":name" has been updated.',
        'sms' => 'Tag ":name" updated.',
    ],
    'tag.deleted' => [
        'title' => 'Tag Deleted',
        'body' => 'The tag ":name" has been deleted.',
        'sms' => 'Tag ":name" deleted.',
    ],
    'symptom.created' => [
        'title' => 'New Symptom Created',
        'body' => 'A new symptom ":name" has been added.',
        'sms' => 'New symptom ":name" added.',
    ],
    'symptom.updated' => [
        'title' => 'Symptom Updated',
        'body' => 'The symptom ":name" has been updated.',
        'sms' => 'Symptom ":name" updated.',
    ],
    'symptom.deleted' => [
        'title' => 'Symptom Deleted',
        'body' => 'The symptom ":name" has been removed.',
        'sms' => 'Symptom ":name" removed.',
    ],
];
