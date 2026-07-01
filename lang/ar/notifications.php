<?php

return [
    'user.logged_in' => [
        'title' => 'تم اكتشاف تسجيل دخول جديد',
        'body' => 'تم اكتشاف تسجيل دخول جديد إلى حسابك.',
        'sms' => 'تم اكتشاف تسجيل دخول جديد إلى حسابك.',
    ],

    'user.registered' => [
        'title' => 'مرحباً بك في النظام الصحي',
        'body' => 'مرحباً :name! تم إنشاء حسابك بنجاح.',
        'sms' => 'مرحباً :name! تم إنشاء حسابك.',
    ],

    'article.created' => [
        'title' => 'تم نشر مقال جديد',
        'body' => 'قام :author بإنشاء مقال جديد بعنوان ":title".',
        'sms' => 'تم إنشاء مقال جديد ":title".',
    ],

    'article.approved' => [
        'title' => 'تمت الموافقة على المقال',
        'body' => 'تمت الموافقة على مقالك ":title" وهو متاح الآن.',
        'sms' => 'تمت الموافقة على مقالك ":title".',
    ],

    'article.rejected' => [
        'title' => 'تم رفض المقال',
        'body' => 'تم رفض مقالك ":title". السبب: :reason',
        'sms' => 'تم رفض مقالك ":title".',
    ],

    'story.created' => [
        'title' => 'قصة جديدة مشاركة',
        'body' => 'تم مشاركة قصة جديدة بعنوان ":title".',
        'sms' => 'تم مشاركة قصة جديدة ":title".',
    ],

    'story.approved' => [
        'title' => 'تمت الموافقة على القصة',
        'body' => 'تمت الموافقة على قصتك ":title" وهي متاحة الآن.',
        'sms' => 'تمت الموافقة على قصتك ":title".',
    ],

    'job.posted' => [
        'title' => 'فرصة عمل جديدة',
        'body' => 'تم نشر وظيفة ":title" في :facility.',
        'sms' => 'وظيفة جديدة ":title" في :facility.',
    ],

    'comment.added' => [
        'title' => 'تعليق جديد',
        'body' => 'علق :author على مقالك ":article".',
        'sms' => 'علق :author على ":article".',
    ],

    'donation.completed.donor' => [
        'title' => 'تم اكتمال التبرع',
        'body' => 'تم اكتمال تبرعك بمبلغ :amount لـ :story بنجاح.',
    ],

    'donation.completed.patient' => [
        'title' => 'تم استلام تبرع جديد',
        'body' => 'لقد استلمت تبرعاً بمبلغ :amount لقصة ":story".',
    ],

    'donation.completed.admin' => [
        'title' => 'تم اكتمال التبرع',
        'body' => 'تبرع :name بمبلغ :amount لـ :story.',
    ],

    'donation.made' => [
        'title' => 'تم استلام تبرع',
        'body' => 'تبرع :name بمبلغ :amount لـ :campaign.',
        'sms' => 'تبرع :name بمبلغ :amount.',
    ],

    'staff.assigned' => [
        'title' => 'تعيين في المنشأة',
        'body' => 'تم تعيينك في :facility بمنصب :position.',
        'sms' => 'تم تعيينك في :facility بمنصب :position.',
    ],

    'staff.unassigned' => [
        'title' => 'إلغاء تعيين من المنشأة',
        'body' => 'تم إلغاء تعيينك من :facility بمنصب :position.',
        'sms' => 'تم إلغاء تعيينك من :facility بمنصب :position.',
    ],

    'category.created' => [
        'title' => 'تم إنشاء تصنيف جديد',
        'body' => 'تم إنشاء تصنيف جديد ":name".',
        'sms' => 'تم إنشاء تصنيف ":name".',
    ],
    'category.updated' => [
        'title' => 'تم تحديث التصنيف',
        'body' => 'تم تحديث التصنيف ":name".',
        'sms' => 'تم تحديث التصنيف ":name".',
    ],
    'category.deleted' => [
        'title' => 'تم حذف التصنيف',
        'body' => 'تم حذف التصنيف ":name".',
        'sms' => 'تم حذف التصنيف ":name".',
    ],
    'tag.created' => [
        'title' => 'تم إنشاء وسم جديد',
        'body' => 'تم إنشاء وسم جديد ":name".',
        'sms' => 'تم إنشاء وسم ":name".',
    ],
    'tag.updated' => [
        'title' => 'تم تحديث الوسم',
        'body' => 'تم تحديث الوسم ":name".',
        'sms' => 'تم تحديث الوسم ":name".',
    ],
    'tag.deleted' => [
        'title' => 'تم حذف الوسم',
        'body' => 'تم حذف الوسم ":name".',
        'sms' => 'تم حذف الوسم ":name".',
    ],
    'symptom.created' => [
        'title' => 'تم إضافة عرض جديد',
        'body' => 'تم إضافة عرض جديد ":name".',
        'sms' => 'تم إضافة عرض ":name".',
    ],
    'symptom.updated' => [
        'title' => 'تم تحديث العرض',
        'body' => 'تم تحديث العرض ":name".',
        'sms' => 'تم تحديث العرض ":name".',
    ],
    'symptom.deleted' => [
        'title' => 'تم حذف العرض',
        'body' => 'تم حذف العرض ":name".',
        'sms' => 'تم حذف العرض ":name".',
    ],
    'facility.reviewed' => [
        'title' => 'تقييم جديد للمنشأة',
        'body' => 'قام :patient بتقييم :facility ب :rating/5.',
        'sms' => 'تقييم :facility ب :rating/5.',
    ],
    'doctor.reviewed' => [
        'title' => 'تقييم جديد للطبيب',
        'body' => 'قام :patient بتقييم د. :doctor ب :rating/5.',
        'sms' => 'تقييم د. :doctor ب :rating/5.',
    ],
    'platform.review.submitted' => [
        'title' => 'تقييم جديد للمنصة',
        'body' => 'قدم :user تقييماً للمنصة بتقييم :rating/5.',
        'sms' => 'تقييم جديد للمنصة من :user.',
    ],
    'platform.review.replied' => [
        'title' => 'تم الرد على تقييمك',
        'body' => 'قام المشرف بالرد على تقييمك للمنصة.',
        'sms' => 'تم الرد على تقييمك.',
    ],
    'review.replied' => [
        'title' => 'رد على التقييم',
        'body' => 'تمت إضافة رد على تقييمك.',
        'sms' => 'تم الرد على تقييمك.',
    ],
    'appointment.created' => [
        'title' => 'تم جدولة الموعد',
        'body' => 'تم جدولة موعدك مع د. :doctor في :start_at.',
        'sms' => 'موعد مع د. :doctor في :start_at.',
    ],
    'appointment.status_changed' => [
        'title' => 'تحديث حالة الموعد',
        'body' => 'تغيرت حالة موعدك مع د. :doctor إلى :status.',
        'sms' => 'حالة الموعد: :status.',
    ],
    'prescription.created' => [
        'title' => 'تم إصدار وصفة طبية',
        'body' => 'تم إصدار وصفة طبية جديدة من د. :doctor.',
        'sms' => 'وصفة طبية جديدة من د. :doctor.',
    ],
    'medicine.request.created' => [
        'title' => 'طلب دواء',
        'body' => 'طلب :patient دواء في :facility.',
        'sms' => 'طلب دواء من :patient.',
    ],
    'medicine.request.status_changed' => [
        'title' => 'تحديث طلب الدواء',
        'body' => 'تغيرت حالة طلب الدواء إلى :status.',
        'sms' => 'طلب الدواء: :status.',
    ],
    'story.rejected' => [
        'title' => 'تم رفض القصة',
        'body' => 'تم رفض قصتك ":title".',
        'sms' => 'تم رفض قصتك ":title".',
    ],
    'job.approved' => [
        'title' => 'تم اعتماد الوظيفة',
        'body' => 'تم اعتماد وظيفتك ":title" وهي متاحة الآن.',
        'sms' => 'تم اعتماد وظيفتك ":title".',
    ],
    'job.rejected' => [
        'title' => 'تم رفض الوظيفة',
        'body' => 'تم رفض وظيفتك ":title". السبب: :reason',
        'sms' => 'تم رفض وظيفتك ":title".',
    ],
    'donation.created' => [
        'title' => 'تم بدء تبرع',
        'body' => 'بدأ :name تبرعاً بمبلغ :amount.',
        'sms' => 'بدأ :name تبرعاً.',
    ],
    'invoice.generated' => [
        'title' => 'تم إنشاء فاتورة',
        'body' => 'تم إنشاء الفاتورة #:invoice_number بمبلغ :total_amount :currency.',
        'sms' => 'فاتورة بمبلغ :total_amount.',
    ],
    'payment.processed' => [
        'title' => 'تمت معالجة الدفع',
        'body' => 'تمت معالجة دفعة بمبلغ :amount بنجاح.',
        'sms' => 'تمت معالجة دفعة بمبلغ :amount.',
    ],

    'ai.prompted' => [
        'title' => 'تم استخدام المساعد الذكي',
        'body' => 'تم إجراء استعلام ذكي بواسطة :user.',
        'sms' => 'استعلام ذكي بواسطة :user.',
    ],
];
