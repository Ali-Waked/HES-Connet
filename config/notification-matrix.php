<?php

return [
    // Authentication
    'user.registered' => [
        'owner' => ['database'],
        'admin' => ['database'],
    ],
    'user.logged_in' => [
        'owner' => ['database'],
    ],
    'email.verified' => [
        'owner' => ['database'],
    ],
    'password.reset' => [
        'owner' => ['database'],
    ],
    'password.changed' => [
        'owner' => ['database'],
    ],

    // Appointments
    'appointment.created' => [
        'patient' => ['database'],
        'doctor' => ['database'],
    ],
    'appointment.confirmed' => [
        'patient' => ['database'],
        'doctor' => ['database'],
    ],
    'appointment.cancelled' => [
        'patient' => ['database'],
        'doctor' => ['database'],
    ],
    'appointment.completed' => [
        'patient' => ['database'],
        'doctor' => ['database'],
    ],
    'appointment.rescheduled' => [
        'patient' => ['database'],
        'doctor' => ['database'],
    ],
    'appointment.reminder_24h' => [
        'patient' => ['database'],
    ],
    'appointment.reminder_1h' => [
        'patient' => ['database'],
    ],
    'appointment.no_show' => [
        'doctor' => ['database'],
    ],

    // Doctors / Staff
    'doctor.approved' => [
        'doctor' => ['database'],
    ],
    'doctor.rejected' => [
        'doctor' => ['database'],
    ],
    'doctor.reviewed' => [
        'doctor' => ['database'],
        'admin' => ['database'],
    ],
    'staff.assigned' => [
        'staff' => ['database'],
        'facility_admins' => ['database'],
    ],
    'staff.unassigned' => [
        'staff' => ['database'],
        'facility_admins' => ['database'],
    ],
    'unavailability.approved' => [
        'staff' => ['database'],
    ],
    'unavailability.rejected' => [
        'staff' => ['database'],
    ],

    // Patients
    'review.replied' => [
        'patient' => ['database'],
    ],
    'prescription.created' => [
        'patient' => ['database'],
    ],
    'prescription.status_changed' => [
        'patient' => ['database'],
    ],
    'medicine.request.created' => [
        'patient' => ['database'],
        'pharmacist' => ['database'],
        'admin' => ['database'],
    ],
    'medicine.request.status_changed' => [
        'patient' => ['database'],
        'pharmacist' => ['database'],
    ],

    // Facilities
    'facility.registered' => [
        'admin' => ['database'],
    ],
    'facility.approved' => [
        'owner' => ['database'],
        'admin' => ['database'],
    ],
    'facility.rejected' => [
        'owner' => ['database'],
        'admin' => ['database'],
    ],
    'facility.suspended' => [
        'owner' => ['database'],
        'admin' => ['database'],
    ],
    'facility.reviewed' => [
        'facility_admin' => ['database'],
        'admin' => ['database'],
    ],

    // Platform Reviews
    'platform.review.submitted' => [
        'admin' => ['database'],
        'owner' => ['database'],
    ],
    'platform.review.replied' => [
        'owner' => ['database'],
    ],

    // Content
    'article.created' => [
        'admin' => ['database'],
        'author' => ['database'],
    ],
    'article.approved' => [
        'author' => ['database'],
    ],
    'article.rejected' => [
        'author' => ['database'],
    ],
    'comment.added' => [
        'owner' => ['database'],
    ],
    'story.created' => [
        'admin' => ['database'],
        'patient' => ['database'],
    ],
    'story.approved' => [
        'patient' => ['database'],
    ],
    'story.rejected' => [
        'patient' => ['database'],
    ],

    // Donations & Payments
    'donation.created' => [
        'admin' => ['database'],
    ],
    'donation.made' => [
        'admin' => ['database'],
    ],
    'donation.completed' => [
        'donor' => ['database'],
        'patient' => ['database'],
        'admin' => ['database'],
    ],
    'payment.processed' => [
        'admin' => ['database'],
    ],
    'payment.failed' => [
        'admin' => ['database'],
    ],
    'invoice.generated' => [
        'owner' => ['database'],
    ],

    // Jobs
    'job.posted' => [
        'admin' => ['database'],
    ],
    'job.approved' => [
        'owner' => ['database'],
    ],
    'job.rejected' => [
        'owner' => ['database'],
    ],

    // Contact
    'contact.submitted' => [
        'admin' => ['database'],
    ],

    // AI
    'ai.prompted' => [
        'admin' => ['database'],
    ],
    'ai.conversation_completed' => [
        'patient' => ['database'],
    ],
    'ai.recommendation_available' => [
        'patient' => ['database'],
    ],

    // System
    'subscription.created' => [
        'owner' => ['database'],
    ],
    'subscription.expiring' => [
        'owner' => ['database'],
    ],
    'system.maintenance' => [
        'admin' => ['database'],
    ],
    'system.version' => [
        'admin' => ['database'],
    ],
    'system.broadcast' => [
        'all' => ['database'],
    ],
];
