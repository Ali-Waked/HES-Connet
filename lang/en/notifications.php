<?php

return [
    'user.logged_in' => [
        'title' => 'New Login Detected',
        'body' => 'We detected a new login to your account.',
        'sms' => 'New login detected on your account.',
    ],

    'user.registered' => [
        'title' => 'Welcome to Health Ecosystem',
        'body' => 'Welcome :name! Your account has been created successfully.',
        'sms' => 'Welcome :name! Your account has been created.',
    ],

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

    'job.posted' => [
        'title' => 'New Job Opportunity',
        'body' => 'A new job ":title" has been posted at :facility.',
        'sms' => 'New job ":title" posted at :facility.',
    ],

    'comment.added' => [
        'title' => 'New Comment',
        'body' => ':author commented on your article ":article".',
        'sms' => ':author commented on ":article".',
    ],

    'donation.completed.donor' => [
        'title' => 'Donation Completed',
        'body' => 'Your donation of :amount to :story has been successfully completed.',
    ],

    'donation.completed.patient' => [
        'title' => 'New Donation Received',
        'body' => 'You received a donation of :amount for your story ":story".',
    ],

    'donation.completed.admin' => [
        'title' => 'Donation Completed',
        'body' => ':name donated :amount to :story.',
    ],

    'donation.made' => [
        'title' => 'Donation Received',
        'body' => ':name donated :amount to :campaign.',
        'sms' => ':name donated :amount.',
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
    'facility.reviewed' => [
        'title' => 'New Facility Review',
        'body' => ':patient rated :facility :rating/5.',
        'sms' => ':patient rated :facility :rating/5.',
    ],
    'doctor.reviewed' => [
        'title' => 'New Doctor Review',
        'body' => ':patient reviewed Dr. :doctor with :rating/5.',
        'sms' => ':patient reviewed Dr. :doctor with :rating/5.',
    ],
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
    'review.replied' => [
        'title' => 'Review Reply',
        'body' => 'A reply has been added to your review.',
        'sms' => 'Your review has been replied to.',
    ],
    'appointment.created' => [
        'title' => 'Appointment Scheduled',
        'body' => 'Your appointment with Dr. :doctor has been scheduled for :start_at.',
        'sms' => 'Appointment with Dr. :doctor on :start_at.',
    ],
    'appointment.status_changed' => [
        'title' => 'Appointment Status Updated',
        'body' => 'Your appointment with Dr. :doctor status changed to :status.',
        'sms' => 'Appointment status: :status.',
    ],
    'prescription.created' => [
        'title' => 'Prescription Issued',
        'body' => 'A new prescription has been issued by Dr. :doctor.',
        'sms' => 'New prescription from Dr. :doctor.',
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
    'story.rejected' => [
        'title' => 'Story Rejected',
        'body' => 'Your story ":title" has been rejected.',
        'sms' => 'Your story ":title" was rejected.',
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
    'donation.created' => [
        'title' => 'Donation Initiated',
        'body' => ':name initiated a donation of :amount.',
        'sms' => ':name initiated a donation.',
    ],
    'invoice.generated' => [
        'title' => 'Invoice Generated',
        'body' => 'Invoice #:invoice_number for :total_amount :currency has been generated.',
        'sms' => 'Invoice for :total_amount generated.',
    ],
    'payment.processed' => [
        'title' => 'Payment Processed',
        'body' => 'A payment of :amount has been processed successfully.',
        'sms' => 'Payment of :amount processed.',
    ],

    'ai.prompted' => [
        'title' => 'AI Assistant Used',
        'body' => 'An AI query was made by :user.',
        'sms' => 'AI query by :user.',
    ],
];
