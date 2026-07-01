<?php

return [
    'user.logged_in' => [
        'owner' => ['database', 'broadcast'],
    ],

    'user.registered' => [
        'owner' => ['database', 'mail'],
        'admin' => ['database', 'broadcast'],
    ],

    'article.created' => [
        'admin' => ['database', 'broadcast'],
        'author' => ['database', 'mail'],
    ],

    'article.approved' => [
        'admin' => ['database'],
        'author' => ['database', 'mail'],
    ],

    'article.rejected' => [
        'admin' => ['database'],
        'author' => ['database', 'mail'],
    ],

    'story.created' => [
        'admin' => ['database', 'broadcast'],
        'patient' => ['database', 'mail'],
    ],

    'story.approved' => [
        'admin' => ['database'],
        'patient' => ['database', 'mail'],
    ],

    'job.posted' => [
        'admin' => ['database', 'broadcast'],
    ],

    'comment.added' => [
        'owner' => ['database', 'mail'],
        'admin' => ['database'],
    ],

    'donation.completed.donor' => [
        'donor' => ['database', 'mail'],
    ],

    'donation.completed.patient' => [
        'patient' => ['database', 'mail', 'broadcast'],
    ],

    'donation.completed.admin' => [
        'admin' => ['database'],
    ],

    'donation.made' => [
        'admin' => ['database'],
    ],

    'staff.assigned' => [
        'staff' => ['database', 'mail', 'broadcast'],
        'facility_admins' => ['database'],
    ],

    'staff.unassigned' => [
        'staff' => ['database', 'mail', 'broadcast'],
        'facility_admins' => ['database'],
    ],

    'category.created' => [
        'admin' => ['database', 'broadcast'],
    ],
    'category.updated' => [
        'admin' => ['database'],
    ],
    'category.deleted' => [
        'admin' => ['database'],
    ],
    'tag.created' => [
        'admin' => ['database', 'broadcast'],
    ],
    'tag.updated' => [
        'admin' => ['database'],
    ],
    'tag.deleted' => [
        'admin' => ['database'],
    ],
    'symptom.created' => [
        'admin' => ['database', 'broadcast'],
    ],
    'symptom.updated' => [
        'admin' => ['database'],
    ],
    'symptom.deleted' => [
        'admin' => ['database'],
    ],
    'facility.reviewed' => [
        'admin' => ['database', 'broadcast'],
        'facility_admin' => ['database'],
    ],
    'doctor.reviewed' => [
        'admin' => ['database', 'broadcast'],
        'doctor' => ['database', 'mail'],
    ],
    'platform.review.submitted' => [
        'admin' => ['database', 'broadcast'],
    ],
    'platform.review.replied' => [
        'owner' => ['database', 'mail'],
    ],
    'review.replied' => [
        'patient' => ['database', 'mail'],
    ],
    'appointment.created' => [
        'patient' => ['database', 'mail'],
        'doctor' => ['database', 'broadcast'],
    ],
    'appointment.status_changed' => [
        'patient' => ['database', 'mail'],
        'doctor' => ['database', 'broadcast'],
    ],
    'prescription.created' => [
        'patient' => ['database', 'mail'],
    ],
    'medicine.request.created' => [
        'pharmacist' => ['database', 'broadcast'],
        'admin' => ['database'],
    ],
    'medicine.request.status_changed' => [
        'patient' => ['database', 'mail'],
        'pharmacist' => ['database'],
    ],
    'story.rejected' => [
        'patient' => ['database', 'mail'],
        'admin' => ['database'],
    ],
    'job.approved' => [
        'owner' => ['database', 'mail'],
    ],
    'job.rejected' => [
        'owner' => ['database', 'mail'],
    ],
    'donation.created' => [
        'admin' => ['database'],
    ],
    'invoice.generated' => [
        'owner' => ['database', 'mail'],
    ],
    'payment.processed' => [
        'admin' => ['database', 'broadcast'],
    ],

    'ai.prompted' => [
        'admin' => ['database'],
    ],
];
