<?php

declare(strict_types=1);

return [
    'dashboard' => [
        [
            'key' => 'dashboard.view',
            'name' => [
                'en' => 'View Dashboard',
                'ar' => 'عرض لوحة التحكم',
            ],
            'description' => [
                'en' => 'Allows access to the admin dashboard',
                'ar' => 'السماح بالوصول إلى لوحة التحكم',
            ],
        ],
    ],

    'organizations' => [
        [
            'key' => 'organizations.view',
            'name' => [
                'en' => 'View Organizations',
                'ar' => 'عرض المؤسسات',
            ],
            'description' => [
                'en' => 'Allows viewing organization records',
                'ar' => 'السماح بعرض سجلات المؤسسات',
            ],
        ],
        [
            'key' => 'organizations.manage',
            'name' => [
                'en' => 'Manage Organizations',
                'ar' => 'إدارة المؤسسات',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting organizations',
                'ar' => 'السماح بإنشاء وتحديث وحذف المؤسسات',
            ],
        ],
        [
            'key' => 'organizations.approve',
            'name' => [
                'en' => 'Approve Organizations',
                'ar' => 'الموافقة على المؤسسات',
            ],
            'description' => [
                'en' => 'Allows approving pending organizations',
                'ar' => 'السماح بالموافقة على المؤسسات المعلقة',
            ],
        ],
        [
            'key' => 'organizations.reject',
            'name' => [
                'en' => 'Reject Organizations',
                'ar' => 'رفض المؤسسات',
            ],
            'description' => [
                'en' => 'Allows rejecting organization applications',
                'ar' => 'السماح برفض طلبات المؤسسات',
            ],
        ],
    ],

    'facilities' => [
        [
            'key' => 'facilities.view',
            'name' => [
                'en' => 'View Facilities',
                'ar' => 'عرض المرافق',
            ],
            'description' => [
                'en' => 'Allows viewing facility records',
                'ar' => 'السماح بعرض سجلات المرافق',
            ],
        ],
        [
            'key' => 'facilities.manage',
            'name' => [
                'en' => 'Manage Facilities',
                'ar' => 'إدارة المرافق',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting facilities',
                'ar' => 'السماح بإنشاء وتحديث وحذف المرافق',
            ],
        ],
        [
            'key' => 'facilities.approve',
            'name' => [
                'en' => 'Approve Facilities',
                'ar' => 'الموافقة على المرافق',
            ],
            'description' => [
                'en' => 'Allows approving pending facilities',
                'ar' => 'السماح بالموافقة على المرافق المعلقة',
            ],
        ],
        [
            'key' => 'facilities.reject',
            'name' => [
                'en' => 'Reject Facilities',
                'ar' => 'رفض المرافق',
            ],
            'description' => [
                'en' => 'Allows rejecting facility applications',
                'ar' => 'السماح برفض طلبات المرافق',
            ],
        ],
    ],

    'departments' => [
        [
            'key' => 'departments.view',
            'name' => [
                'en' => 'View Departments',
                'ar' => 'عرض الأقسام',
            ],
            'description' => [
                'en' => 'Allows viewing department records',
                'ar' => 'السماح بعرض سجلات الأقسام',
            ],
        ],
        [
            'key' => 'departments.manage',
            'name' => [
                'en' => 'Manage Departments',
                'ar' => 'إدارة الأقسام',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting departments',
                'ar' => 'السماح بإنشاء وتحديث وحذف الأقسام',
            ],
        ],
    ],

    'staff' => [
        [
            'key' => 'staff.view',
            'name' => [
                'en' => 'View Staff',
                'ar' => 'عرض الموظفين',
            ],
            'description' => [
                'en' => 'Allows viewing staff records',
                'ar' => 'السماح بعرض سجلات الموظفين',
            ],
        ],
        [
            'key' => 'staff.manage',
            'name' => [
                'en' => 'Manage Staff',
                'ar' => 'إدارة الموظفين',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting staff accounts',
                'ar' => 'السماح بإنشاء وتحديث وحذف حسابات الموظفين',
            ],
        ],
    ],

    'patients' => [
        [
            'key' => 'patients.view',
            'name' => [
                'en' => 'View Patients',
                'ar' => 'عرض المرضى',
            ],
            'description' => [
                'en' => 'Allows viewing patient records',
                'ar' => 'السماح بعرض سجلات المرضى',
            ],
        ],
        [
            'key' => 'patients.manage',
            'name' => [
                'en' => 'Manage Patients',
                'ar' => 'إدارة المرضى',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting patient records',
                'ar' => 'السماح بإنشاء وتحديث وحذف سجلات المرضى',
            ],
        ],
    ],

    'users' => [
        [
            'key' => 'users.view',
            'name' => [
                'en' => 'View Users',
                'ar' => 'عرض المستخدمين',
            ],
            'description' => [
                'en' => 'Allows viewing user accounts',
                'ar' => 'السماح بعرض حسابات المستخدمين',
            ],
        ],
        [
            'key' => 'users.manage',
            'name' => [
                'en' => 'Manage Users',
                'ar' => 'إدارة المستخدمين',
            ],
            'description' => [
                'en' => 'Allows managing user accounts',
                'ar' => 'السماح بإدارة حسابات المستخدمين',
            ],
        ],
    ],

    'roles' => [
        [
            'key' => 'roles.view',
            'name' => [
                'en' => 'View Roles',
                'ar' => 'عرض الأدوار',
            ],
            'description' => [
                'en' => 'Allows viewing role records',
                'ar' => 'السماح بعرض سجلات الأدوار',
            ],
        ],
        [
            'key' => 'roles.manage',
            'name' => [
                'en' => 'Manage Roles',
                'ar' => 'إدارة الأدوار',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting roles',
                'ar' => 'السماح بإنشاء وتحديث وحذف الأدوار',
            ],
        ],
        [
            'key' => 'roles.assign',
            'name' => [
                'en' => 'Assign Roles',
                'ar' => 'تعيين الأدوار',
            ],
            'description' => [
                'en' => 'Allows assigning roles to users',
                'ar' => 'السماح بتعيين الأدوار للمستخدمين',
            ],
        ],
    ],

    'permissions' => [
        [
            'key' => 'permissions.view',
            'name' => [
                'en' => 'View Permissions',
                'ar' => 'عرض الصلاحيات',
            ],
            'description' => [
                'en' => 'Allows viewing permission records',
                'ar' => 'السماح بعرض سجلات الصلاحيات',
            ],
        ],
    ],

    'facility_documents' => [
        [
            'key' => 'facility_documents.view',
            'name' => [
                'en' => 'View Facility Documents',
                'ar' => 'عرض مستندات المنشأة',
            ],
            'description' => [
                'en' => 'Allows viewing facility documents',
                'ar' => 'السماح بعرض مستندات المنشأة',
            ],
        ],
        [
            'key' => 'facility_documents.manage',
            'name' => [
                'en' => 'Manage Facility Documents',
                'ar' => 'إدارة مستندات المنشأة',
            ],
            'description' => [
                'en' => 'Allows uploading and deleting facility documents',
                'ar' => 'السماح برفع وحذف مستندات المنشأة',
            ],
        ],
        [
            'key' => 'facility_documents.approve',
            'name' => [
                'en' => 'Approve Facility Documents',
                'ar' => 'الموافقة على مستندات المنشأة',
            ],
            'description' => [
                'en' => 'Allows approving facility documents',
                'ar' => 'السماح بالموافقة على مستندات المنشأة',
            ],
        ],
        [
            'key' => 'facility_documents.reject',
            'name' => [
                'en' => 'Reject Facility Documents',
                'ar' => 'رفض مستندات المنشأة',
            ],
            'description' => [
                'en' => 'Allows rejecting facility documents',
                'ar' => 'السماح برفض مستندات المنشأة',
            ],
        ],
    ],

    'facility_images' => [
        [
            'key' => 'facility_images.view',
            'name' => [
                'en' => 'View Facility Images',
                'ar' => 'عرض صور المنشأة',
            ],
            'description' => [
                'en' => 'Allows viewing facility images',
                'ar' => 'السماح بعرض صور المنشأة',
            ],
        ],
        [
            'key' => 'facility_images.manage',
            'name' => [
                'en' => 'Manage Facility Images',
                'ar' => 'إدارة صور المنشأة',
            ],
            'description' => [
                'en' => 'Allows uploading and deleting facility images',
                'ar' => 'السماح برفع وحذف صور المنشأة',
            ],
        ],
    ],

    'articles' => [
        [
            'key' => 'articles.view',
            'name' => [
                'en' => 'View Articles',
                'ar' => 'عرض المقالات',
            ],
            'description' => [
                'en' => 'Allows viewing articles',
                'ar' => 'السماح بعرض المقالات',
            ],
        ],
        [
            'key' => 'articles.manage',
            'name' => [
                'en' => 'Manage Articles',
                'ar' => 'إدارة المقالات',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting articles',
                'ar' => 'السماح بإنشاء وتحديث وحذف المقالات',
            ],
        ],
        [
            'key' => 'articles.publish',
            'name' => [
                'en' => 'Publish Articles',
                'ar' => 'نشر المقالات',
            ],
            'description' => [
                'en' => 'Allows publishing and unpublishing articles',
                'ar' => 'السماح بنشر وإلغاء نشر المقالات',
            ],
        ],
    ],

    'stories' => [
        [
            'key' => 'stories.view',
            'name' => [
                'en' => 'View Stories',
                'ar' => 'عرض القصص',
            ],
            'description' => [
                'en' => 'Allows viewing stories',
                'ar' => 'السماح بعرض القصص',
            ],
        ],
        [
            'key' => 'stories.manage',
            'name' => [
                'en' => 'Manage Stories',
                'ar' => 'إدارة القصص',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting stories',
                'ar' => 'السماح بإنشاء وتحديث وحذف القصص',
            ],
        ],
    ],

    'jobs' => [
        [
            'key' => 'jobs.view',
            'name' => [
                'en' => 'View Jobs',
                'ar' => 'عرض الوظائف',
            ],
            'description' => [
                'en' => 'Allows viewing job posts',
                'ar' => 'السماح بعرض الوظائف',
            ],
        ],
        [
            'key' => 'jobs.manage',
            'name' => [
                'en' => 'Manage Jobs',
                'ar' => 'إدارة الوظائف',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting job posts',
                'ar' => 'السماح بإنشاء وتحديث وحذف الوظائف',
            ],
        ],
        [
            'key' => 'jobs.publish',
            'name' => [
                'en' => 'Publish Jobs',
                'ar' => 'نشر الوظائف',
            ],
            'description' => [
                'en' => 'Allows publishing and unpublishing job posts',
                'ar' => 'السماح بنشر وإلغاء نشر الوظائف',
            ],
        ],
    ],

    'cities' => [
        [
            'key' => 'cities.view',
            'name' => [
                'en' => 'View Cities',
                'ar' => 'عرض المدن',
            ],
            'description' => [
                'en' => 'Allows viewing city records',
                'ar' => 'السماح بعرض سجلات المدن',
            ],
        ],
        [
            'key' => 'cities.manage',
            'name' => [
                'en' => 'Manage Cities',
                'ar' => 'إدارة المدن',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting cities',
                'ar' => 'السماح بإنشاء وتحديث وحذف المدن',
            ],
        ],
    ],

    'pages' => [
        [
            'key' => 'pages.view',
            'name' => [
                'en' => 'View Pages',
                'ar' => 'عرض الصفحات',
            ],
            'description' => [
                'en' => 'Allows viewing CMS pages',
                'ar' => 'السماح بعرض صفحات CMS',
            ],
        ],
        [
            'key' => 'pages.manage',
            'name' => [
                'en' => 'Manage Pages',
                'ar' => 'إدارة الصفحات',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting CMS pages',
                'ar' => 'السماح بإنشاء وتحديث وحذف صفحات CMS',
            ],
        ],
    ],

    'staff_positions' => [
        [
            'key' => 'staff_positions.view',
            'name' => [
                'en' => 'View Staff Positions',
                'ar' => 'عرض المناصب الوظيفية',
            ],
            'description' => [
                'en' => 'Allows viewing staff positions',
                'ar' => 'السماح بعرض المناصب الوظيفية',
            ],
        ],
        [
            'key' => 'staff_positions.manage',
            'name' => [
                'en' => 'Manage Staff Positions',
                'ar' => 'إدارة المناصب الوظيفية',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting staff positions',
                'ar' => 'السماح بإنشاء وتحديث وحذف المناصب الوظيفية',
            ],
        ],
    ],

    'appointments' => [
        [
            'key' => 'appointments.view',
            'name' => [
                'en' => 'View Appointments',
                'ar' => 'عرض المواعيد',
            ],
            'description' => [
                'en' => 'Allows viewing appointments',
                'ar' => 'السماح بعرض المواعيد',
            ],
        ],
        [
            'key' => 'appointments.manage',
            'name' => [
                'en' => 'Manage Appointments',
                'ar' => 'إدارة المواعيد',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting appointments',
                'ar' => 'السماح بإنشاء وتحديث وحذف المواعيد',
            ],
        ],
    ],

    'staff_schedules' => [
        [
            'key' => 'staff_schedules.view',
            'name' => [
                'en' => 'View Staff Schedules',
                'ar' => 'عرض جداول الموظفين',
            ],
            'description' => [
                'en' => 'Allows viewing staff schedules',
                'ar' => 'السماح بعرض جداول الموظفين',
            ],
        ],
        [
            'key' => 'staff_schedules.manage',
            'name' => [
                'en' => 'Manage Staff Schedules',
                'ar' => 'إدارة جداول الموظفين',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting staff schedules',
                'ar' => 'السماح بإنشاء وتحديث وحذف جداول الموظفين',
            ],
        ],
    ],

    'staff_unavailabilities' => [
        [
            'key' => 'staff_unavailabilities.view',
            'name' => [
                'en' => 'View Staff Unavailability',
                'ar' => 'عرض فترات عدم التوفر',
            ],
            'description' => [
                'en' => 'Allows viewing staff unavailable periods',
                'ar' => 'السماح بعرض فترات عدم التوفر',
            ],
        ],
        [
            'key' => 'staff_unavailabilities.manage',
            'name' => [
                'en' => 'Manage Staff Unavailability',
                'ar' => 'إدارة فترات عدم التوفر',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting staff unavailable periods',
                'ar' => 'السماح بإنشاء وتحديث وحذف فترات عدم التوفر',
            ],
        ],
    ],

    'prescriptions' => [
        [
            'key' => 'prescriptions.view',
            'name' => [
                'en' => 'View Prescriptions',
                'ar' => 'عرض الوصفات الطبية',
            ],
            'description' => [
                'en' => 'Allows viewing prescriptions',
                'ar' => 'السماح بعرض الوصفات الطبية',
            ],
        ],
        [
            'key' => 'prescriptions.manage',
            'name' => [
                'en' => 'Manage Prescriptions',
                'ar' => 'إدارة الوصفات الطبية',
            ],
            'description' => [
                'en' => 'Allows creating, updating, and deleting prescriptions',
                'ar' => 'السماح بإنشاء وتحديث وحذف الوصفات الطبية',
            ],
        ],
    ],

    'contact_messages' => [
        [
            'key' => 'contact_messages.view',
            'name' => [
                'en' => 'View Contact Messages',
                'ar' => 'عرض رسائل الاتصال',
            ],
            'description' => [
                'en' => 'Allows viewing contact messages',
                'ar' => 'السماح بعرض رسائل الاتصال',
            ],
        ],
        [
            'key' => 'contact_messages.manage',
            'name' => [
                'en' => 'Manage Contact Messages',
                'ar' => 'إدارة رسائل الاتصال',
            ],
            'description' => [
                'en' => 'Allows updating contact message statuses',
                'ar' => 'السماح بتحديث حالات رسائل الاتصال',
            ],
        ],
    ],

    'profile' => [
        [
            'key' => 'profile.view',
            'name' => [
                'en' => 'View Profile',
                'ar' => 'عرض الملف الشخصي',
            ],
            'description' => [
                'en' => 'Allows viewing own profile',
                'ar' => 'السماح بعرض الملف الشخصي',
            ],
        ],
        [
            'key' => 'profile.update',
            'name' => [
                'en' => 'Update Profile',
                'ar' => 'تحديث الملف الشخصي',
            ],
            'description' => [
                'en' => 'Allows updating own profile information',
                'ar' => 'السماح بتحديث معلومات الملف الشخصي',
            ],
        ],
        ],
];
