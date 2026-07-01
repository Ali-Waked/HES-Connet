<?php

declare(strict_types=1);

return [
    'dashboard' => [
        [
            'key' => 'view_dashboard_statistics',
            'name' => [
                'en' => 'View Dashboard Statistics',
                'ar' => 'عرض إحصائيات لوحة التحكم',
            ],
            'description' => [
                'en' => 'Allows viewing dashboard statistics and analytics overview',
                'ar' => 'السماح بعرض إحصائيات لوحة التحكم ونظرة عامة على التحليلات',
            ],
        ],
    ],

    'users' => [
        ['key' => 'view_users', 'name' => ['en' => 'View Users', 'ar' => 'عرض المستخدمين'], 'description' => ['en' => 'Allows viewing list of users', 'ar' => 'السماح بعرض قائمة المستخدمين']],
        ['key' => 'show_user', 'name' => ['en' => 'Show User', 'ar' => 'عرض مستخدم'], 'description' => ['en' => 'Allows viewing user details', 'ar' => 'السماح بعرض تفاصيل المستخدم']],
        ['key' => 'create_user', 'name' => ['en' => 'Create User', 'ar' => 'إنشاء مستخدم'], 'description' => ['en' => 'Allows creating new users', 'ar' => 'السماح بإنشاء مستخدمين جدد']],
        ['key' => 'update_user', 'name' => ['en' => 'Update User', 'ar' => 'تحديث مستخدم'], 'description' => ['en' => 'Allows updating existing users', 'ar' => 'السماح بتحديث المستخدمين']],
        ['key' => 'delete_user', 'name' => ['en' => 'Delete User', 'ar' => 'حذف مستخدم'], 'description' => ['en' => 'Allows deleting users', 'ar' => 'السماح بحذف المستخدمين']],
    ],

    'roles' => [
        ['key' => 'view_roles', 'name' => ['en' => 'View Roles', 'ar' => 'عرض الأدوار'], 'description' => ['en' => 'Allows viewing list of roles', 'ar' => 'السماح بعرض قائمة الأدوار']],
        ['key' => 'show_role', 'name' => ['en' => 'Show Role', 'ar' => 'عرض دور'], 'description' => ['en' => 'Allows viewing role details', 'ar' => 'السماح بعرض تفاصيل الدور']],
        ['key' => 'create_role', 'name' => ['en' => 'Create Role', 'ar' => 'إنشاء دور'], 'description' => ['en' => 'Allows creating new roles', 'ar' => 'السماح بإنشاء أدوار جديدة']],
        ['key' => 'update_role', 'name' => ['en' => 'Update Role', 'ar' => 'تحديث دور'], 'description' => ['en' => 'Allows updating existing roles', 'ar' => 'السماح بتحديث الأدوار']],
        ['key' => 'delete_role', 'name' => ['en' => 'Delete Role', 'ar' => 'حذف دور'], 'description' => ['en' => 'Allows deleting roles', 'ar' => 'السماح بحذف الأدوار']],
    ],

    'permissions' => [
        ['key' => 'view_permissions', 'name' => ['en' => 'View Permissions', 'ar' => 'عرض الصلاحيات'], 'description' => ['en' => 'Allows viewing list of permissions', 'ar' => 'السماح بعرض قائمة الصلاحيات']],
        ['key' => 'show_permission', 'name' => ['en' => 'Show Permission', 'ar' => 'عرض صلاحية'], 'description' => ['en' => 'Allows viewing permission details', 'ar' => 'السماح بعرض تفاصيل الصلاحية']],
        ['key' => 'create_permission', 'name' => ['en' => 'Create Permission', 'ar' => 'إنشاء صلاحية'], 'description' => ['en' => 'Allows creating new permissions', 'ar' => 'السماح بإنشاء صلاحيات جديدة']],
        ['key' => 'update_permission', 'name' => ['en' => 'Update Permission', 'ar' => 'تحديث صلاحية'], 'description' => ['en' => 'Allows updating existing permissions', 'ar' => 'السماح بتحديث الصلاحيات']],
        ['key' => 'delete_permission', 'name' => ['en' => 'Delete Permission', 'ar' => 'حذف صلاحية'], 'description' => ['en' => 'Allows deleting permissions', 'ar' => 'السماح بحذف الصلاحيات']],
    ],

    'organizations' => [
        ['key' => 'view_organizations', 'name' => ['en' => 'View Organizations', 'ar' => 'عرض المؤسسات'], 'description' => ['en' => 'Allows viewing organizations', 'ar' => 'السماح بعرض المؤسسات']],
        ['key' => 'show_organization', 'name' => ['en' => 'Show Organization', 'ar' => 'عرض مؤسسة'], 'description' => ['en' => 'Allows viewing organization details', 'ar' => 'السماح بعرض تفاصيل المؤسسة']],
        ['key' => 'create_organization', 'name' => ['en' => 'Create Organization', 'ar' => 'إنشاء مؤسسة'], 'description' => ['en' => 'Allows creating organizations', 'ar' => 'السماح بإنشاء مؤسسات']],
        ['key' => 'update_organization', 'name' => ['en' => 'Update Organization', 'ar' => 'تحديث مؤسسة'], 'description' => ['en' => 'Allows updating organizations', 'ar' => 'السماح بتحديث المؤسسات']],
        ['key' => 'delete_organization', 'name' => ['en' => 'Delete Organization', 'ar' => 'حذف مؤسسة'], 'description' => ['en' => 'Allows deleting organizations', 'ar' => 'السماح بحذف المؤسسات']],
    ],

    'facilities' => [
        ['key' => 'view_facilities', 'name' => ['en' => 'View Facilities', 'ar' => 'عرض المرافق'], 'description' => ['en' => 'Allows viewing facilities', 'ar' => 'السماح بعرض المرافق']],
        ['key' => 'show_facility', 'name' => ['en' => 'Show Facility', 'ar' => 'عرض منشأة'], 'description' => ['en' => 'Allows viewing facility details', 'ar' => 'السماح بعرض تفاصيل المنشأة']],
        ['key' => 'create_facility', 'name' => ['en' => 'Create Facility', 'ar' => 'إنشاء منشأة'], 'description' => ['en' => 'Allows creating facilities', 'ar' => 'السماح بإنشاء مرافق']],
        ['key' => 'update_facility', 'name' => ['en' => 'Update Facility', 'ar' => 'تحديث منشأة'], 'description' => ['en' => 'Allows updating facilities', 'ar' => 'السماح بتحديث المرافق']],
        ['key' => 'delete_facility', 'name' => ['en' => 'Delete Facility', 'ar' => 'حذف منشأة'], 'description' => ['en' => 'Allows deleting facilities', 'ar' => 'السماح بحذف المرافق']],
    ],

    'departments' => [
        ['key' => 'view_departments', 'name' => ['en' => 'View Departments', 'ar' => 'عرض الأقسام'], 'description' => ['en' => 'Allows viewing departments', 'ar' => 'السماح بعرض الأقسام']],
        ['key' => 'show_department', 'name' => ['en' => 'Show Department', 'ar' => 'عرض قسم'], 'description' => ['en' => 'Allows viewing department details', 'ar' => 'السماح بعرض تفاصيل القسم']],
        ['key' => 'create_department', 'name' => ['en' => 'Create Department', 'ar' => 'إنشاء قسم'], 'description' => ['en' => 'Allows creating departments', 'ar' => 'السماح بإنشاء أقسام']],
        ['key' => 'update_department', 'name' => ['en' => 'Update Department', 'ar' => 'تحديث قسم'], 'description' => ['en' => 'Allows updating departments', 'ar' => 'السماح بتحديث الأقسام']],
        ['key' => 'delete_department', 'name' => ['en' => 'Delete Department', 'ar' => 'حذف قسم'], 'description' => ['en' => 'Allows deleting departments', 'ar' => 'السماح بحذف الأقسام']],
    ],

    'staff' => [
        ['key' => 'view_staff', 'name' => ['en' => 'View Staff', 'ar' => 'عرض الموظفين'], 'description' => ['en' => 'Allows viewing staff list', 'ar' => 'السماح بعرض قائمة الموظفين']],
        ['key' => 'show_staff', 'name' => ['en' => 'Show Staff', 'ar' => 'عرض موظف'], 'description' => ['en' => 'Allows viewing staff details', 'ar' => 'السماح بعرض تفاصيل الموظف']],
        ['key' => 'create_staff', 'name' => ['en' => 'Create Staff', 'ar' => 'إنشاء موظف'], 'description' => ['en' => 'Allows creating staff accounts', 'ar' => 'السماح بإنشاء حسابات الموظفين']],
        ['key' => 'update_staff', 'name' => ['en' => 'Update Staff', 'ar' => 'تحديث موظف'], 'description' => ['en' => 'Allows updating staff accounts', 'ar' => 'السماح بتحديث حسابات الموظفين']],
        ['key' => 'delete_staff', 'name' => ['en' => 'Delete Staff', 'ar' => 'حذف موظف'], 'description' => ['en' => 'Allows deleting staff accounts', 'ar' => 'السماح بحذف حسابات الموظفين']],
    ],

    'patients' => [
        ['key' => 'view_patients', 'name' => ['en' => 'View Patients', 'ar' => 'عرض المرضى'], 'description' => ['en' => 'Allows viewing patients list', 'ar' => 'السماح بعرض قائمة المرضى']],
        ['key' => 'show_patient', 'name' => ['en' => 'Show Patient', 'ar' => 'عرض مريض'], 'description' => ['en' => 'Allows viewing patient details', 'ar' => 'السماح بعرض تفاصيل المريض']],
        ['key' => 'create_patient', 'name' => ['en' => 'Create Patient', 'ar' => 'إنشاء مريض'], 'description' => ['en' => 'Allows creating patient records', 'ar' => 'السماح بإنشاء سجلات المرضى']],
        ['key' => 'update_patient', 'name' => ['en' => 'Update Patient', 'ar' => 'تحديث مريض'], 'description' => ['en' => 'Allows updating patient records', 'ar' => 'السماح بتحديث سجلات المرضى']],
        ['key' => 'delete_patient', 'name' => ['en' => 'Delete Patient', 'ar' => 'حذف مريض'], 'description' => ['en' => 'Allows deleting patient records', 'ar' => 'السماح بحذف سجلات المرضى']],
    ],

    'appointments' => [
        ['key' => 'view_appointments', 'name' => ['en' => 'View Appointments', 'ar' => 'عرض المواعيد'], 'description' => ['en' => 'Allows viewing appointments list', 'ar' => 'السماح بعرض قائمة المواعيد']],
        ['key' => 'show_appointment', 'name' => ['en' => 'Show Appointment', 'ar' => 'عرض موعد'], 'description' => ['en' => 'Allows viewing appointment details', 'ar' => 'السماح بعرض تفاصيل الموعد']],
        ['key' => 'create_appointment', 'name' => ['en' => 'Create Appointment', 'ar' => 'إنشاء موعد'], 'description' => ['en' => 'Allows creating appointments', 'ar' => 'السماح بإنشاء مواعيد']],
        ['key' => 'update_appointment', 'name' => ['en' => 'Update Appointment', 'ar' => 'تحديث موعد'], 'description' => ['en' => 'Allows updating appointments', 'ar' => 'السماح بتحديث المواعيد']],
        ['key' => 'cancel_appointment', 'name' => ['en' => 'Cancel Appointment', 'ar' => 'إلغاء موعد'], 'description' => ['en' => 'Allows cancelling appointments', 'ar' => 'السماح بإلغاء المواعيد']],
        ['key' => 'delete_appointment', 'name' => ['en' => 'Delete Appointment', 'ar' => 'حذف موعد'], 'description' => ['en' => 'Allows deleting appointments', 'ar' => 'السماح بحذف المواعيد']],
    ],

    'prescriptions' => [
        ['key' => 'view_prescriptions', 'name' => ['en' => 'View Prescriptions', 'ar' => 'عرض الوصفات الطبية'], 'description' => ['en' => 'Allows viewing prescriptions list', 'ar' => 'السماح بعرض قائمة الوصفات الطبية']],
        ['key' => 'show_prescription', 'name' => ['en' => 'Show Prescription', 'ar' => 'عرض وصفة طبية'], 'description' => ['en' => 'Allows viewing prescription details', 'ar' => 'السماح بعرض تفاصيل الوصفة الطبية']],
        ['key' => 'create_prescription', 'name' => ['en' => 'Create Prescription', 'ar' => 'إنشاء وصفة طبية'], 'description' => ['en' => 'Allows creating prescriptions', 'ar' => 'السماح بإنشاء وصفات طبية']],
        ['key' => 'update_prescription', 'name' => ['en' => 'Update Prescription', 'ar' => 'تحديث وصفة طبية'], 'description' => ['en' => 'Allows updating prescriptions', 'ar' => 'السماح بتحديث الوصفات الطبية']],
        ['key' => 'delete_prescription', 'name' => ['en' => 'Delete Prescription', 'ar' => 'حذف وصفة طبية'], 'description' => ['en' => 'Allows deleting prescriptions', 'ar' => 'السماح بحذف الوصفات الطبية']],
    ],

    'medication_requests' => [
        ['key' => 'view_medication_requests', 'name' => ['en' => 'View Medication Requests', 'ar' => 'عرض طلبات الأدوية'], 'description' => ['en' => 'Allows viewing medication requests', 'ar' => 'السماح بعرض طلبات الأدوية']],
        ['key' => 'show_medication_request', 'name' => ['en' => 'Show Medication Request', 'ar' => 'عرض طلب دواء'], 'description' => ['en' => 'Allows viewing medication request details', 'ar' => 'السماح بعرض تفاصيل طلب الدواء']],
        ['key' => 'create_medication_request', 'name' => ['en' => 'Create Medication Request', 'ar' => 'إنشاء طلب دواء'], 'description' => ['en' => 'Allows creating medication requests', 'ar' => 'السماح بإنشاء طلبات الأدوية']],
        ['key' => 'update_medication_request', 'name' => ['en' => 'Update Medication Request', 'ar' => 'تحديث طلب دواء'], 'description' => ['en' => 'Allows updating medication requests', 'ar' => 'السماح بتحديث طلبات الأدوية']],
        ['key' => 'approve_medication_request', 'name' => ['en' => 'Approve Medication Request', 'ar' => 'الموافقة على طلب دواء'], 'description' => ['en' => 'Allows approving medication requests', 'ar' => 'السماح بالموافقة على طلبات الأدوية']],
        ['key' => 'reject_medication_request', 'name' => ['en' => 'Reject Medication Request', 'ar' => 'رفض طلب دواء'], 'description' => ['en' => 'Allows rejecting medication requests', 'ar' => 'السماح برفض طلبات الأدوية']],
        ['key' => 'delete_medication_request', 'name' => ['en' => 'Delete Medication Request', 'ar' => 'حذف طلب دواء'], 'description' => ['en' => 'Allows deleting medication requests', 'ar' => 'السماح بحذف طلبات الأدوية']],
    ],

    'medical_records' => [
        ['key' => 'view_medical_records', 'name' => ['en' => 'View Medical Records', 'ar' => 'عرض السجلات الطبية'], 'description' => ['en' => 'Allows viewing medical records', 'ar' => 'السماح بعرض السجلات الطبية']],
        ['key' => 'show_medical_record', 'name' => ['en' => 'Show Medical Record', 'ar' => 'عرض سجل طبي'], 'description' => ['en' => 'Allows viewing medical record details', 'ar' => 'السماح بعرض تفاصيل السجل الطبي']],
        ['key' => 'create_medical_record', 'name' => ['en' => 'Create Medical Record', 'ar' => 'إنشاء سجل طبي'], 'description' => ['en' => 'Allows creating medical records', 'ar' => 'السماح بإنشاء سجلات طبية']],
        ['key' => 'update_medical_record', 'name' => ['en' => 'Update Medical Record', 'ar' => 'تحديث سجل طبي'], 'description' => ['en' => 'Allows updating medical records', 'ar' => 'السماح بتحديث السجلات الطبية']],
        ['key' => 'delete_medical_record', 'name' => ['en' => 'Delete Medical Record', 'ar' => 'حذف سجل طبي'], 'description' => ['en' => 'Allows deleting medical records', 'ar' => 'السماح بحذف السجلات الطبية']],
    ],

    'stories' => [
        ['key' => 'view_stories', 'name' => ['en' => 'View Stories', 'ar' => 'عرض القصص'], 'description' => ['en' => 'Allows viewing stories list', 'ar' => 'السماح بعرض قائمة القصص']],
        ['key' => 'show_story', 'name' => ['en' => 'Show Story', 'ar' => 'عرض قصة'], 'description' => ['en' => 'Allows viewing story details', 'ar' => 'السماح بعرض تفاصيل القصة']],
        ['key' => 'create_story', 'name' => ['en' => 'Create Story', 'ar' => 'إنشاء قصة'], 'description' => ['en' => 'Allows creating stories', 'ar' => 'السماح بإنشاء قصص']],
        ['key' => 'update_story', 'name' => ['en' => 'Update Story', 'ar' => 'تحديث قصة'], 'description' => ['en' => 'Allows updating stories', 'ar' => 'السماح بتحديث القصص']],
        ['key' => 'delete_story', 'name' => ['en' => 'Delete Story', 'ar' => 'حذف قصة'], 'description' => ['en' => 'Allows deleting stories', 'ar' => 'السماح بحذف القصص']],
    ],

    'articles' => [
        ['key' => 'view_articles', 'name' => ['en' => 'View Articles', 'ar' => 'عرض المقالات'], 'description' => ['en' => 'Allows viewing articles list', 'ar' => 'السماح بعرض قائمة المقالات']],
        ['key' => 'show_article', 'name' => ['en' => 'Show Article', 'ar' => 'عرض مقال'], 'description' => ['en' => 'Allows viewing article details', 'ar' => 'السماح بعرض تفاصيل المقال']],
        ['key' => 'create_article', 'name' => ['en' => 'Create Article', 'ar' => 'إنشاء مقال'], 'description' => ['en' => 'Allows creating articles', 'ar' => 'السماح بإنشاء مقالات']],
        ['key' => 'update_article', 'name' => ['en' => 'Update Article', 'ar' => 'تحديث مقال'], 'description' => ['en' => 'Allows updating articles', 'ar' => 'السماح بتحديث المقالات']],
        ['key' => 'delete_article', 'name' => ['en' => 'Delete Article', 'ar' => 'حذف مقال'], 'description' => ['en' => 'Allows deleting articles', 'ar' => 'السماح بحذف المقالات']],
    ],

    'settings' => [
        ['key' => 'view_settings', 'name' => ['en' => 'View Settings', 'ar' => 'عرض الإعدادات'], 'description' => ['en' => 'Allows viewing system settings', 'ar' => 'السماح بعرض إعدادات النظام']],
        ['key' => 'update_settings', 'name' => ['en' => 'Update Settings', 'ar' => 'تحديث الإعدادات'], 'description' => ['en' => 'Allows updating system settings', 'ar' => 'السماح بتحديث إعدادات النظام']],
    ],

    'medicines' => [
        ['key' => 'view_medicines', 'name' => ['en' => 'View Medicines', 'ar' => 'عرض الأدوية'], 'description' => ['en' => 'Allows viewing medicines list', 'ar' => 'السماح بعرض قائمة الأدوية']],
        ['key' => 'show_medicine', 'name' => ['en' => 'Show Medicine', 'ar' => 'عرض دواء'], 'description' => ['en' => 'Allows viewing medicine details', 'ar' => 'السماح بعرض تفاصيل الدواء']],
        ['key' => 'create_medicine', 'name' => ['en' => 'Create Medicine', 'ar' => 'إنشاء دواء'], 'description' => ['en' => 'Allows creating medicines', 'ar' => 'السماح بإنشاء أدوية']],
        ['key' => 'update_medicine', 'name' => ['en' => 'Update Medicine', 'ar' => 'تحديث دواء'], 'description' => ['en' => 'Allows updating medicines', 'ar' => 'السماح بتحديث الأدوية']],
        ['key' => 'delete_medicine', 'name' => ['en' => 'Delete Medicine', 'ar' => 'حذف دواء'], 'description' => ['en' => 'Allows deleting medicines', 'ar' => 'السماح بحذف الأدوية']],
    ],

    'staff_schedules' => [
        ['key' => 'view_staff_schedules', 'name' => ['en' => 'View Staff Schedules', 'ar' => 'عرض جداول الموظفين'], 'description' => ['en' => 'Allows viewing staff schedules', 'ar' => 'السماح بعرض جداول الموظفين']],
        ['key' => 'create_staff_schedule', 'name' => ['en' => 'Create Staff Schedule', 'ar' => 'إنشاء جدول موظف'], 'description' => ['en' => 'Allows creating staff schedules', 'ar' => 'السماح بإنشاء جداول الموظفين']],
        ['key' => 'update_staff_schedule', 'name' => ['en' => 'Update Staff Schedule', 'ar' => 'تحديث جدول موظف'], 'description' => ['en' => 'Allows updating staff schedules', 'ar' => 'السماح بتحديث جداول الموظفين']],
        ['key' => 'delete_staff_schedule', 'name' => ['en' => 'Delete Staff Schedule', 'ar' => 'حذف جدول موظف'], 'description' => ['en' => 'Allows deleting staff schedules', 'ar' => 'السماح بحذف جداول الموظفين']],
    ],

    'staff_unavailabilities' => [
        ['key' => 'view_staff_unavailabilities', 'name' => ['en' => 'View Staff Unavailability', 'ar' => 'عرض فترات عدم التوفر'], 'description' => ['en' => 'Allows viewing staff unavailability', 'ar' => 'السماح بعرض فترات عدم التوفر']],
        ['key' => 'create_staff_unavailability', 'name' => ['en' => 'Create Staff Unavailability', 'ar' => 'إنشاء فترة عدم توفر'], 'description' => ['en' => 'Allows creating staff unavailability', 'ar' => 'السماح بإنشاء فترات عدم التوفر']],
        ['key' => 'update_staff_unavailability', 'name' => ['en' => 'Update Staff Unavailability', 'ar' => 'تحديث فترة عدم توفر'], 'description' => ['en' => 'Allows updating staff unavailability', 'ar' => 'السماح بتحديث فترات عدم التوفر']],
        ['key' => 'delete_staff_unavailability', 'name' => ['en' => 'Delete Staff Unavailability', 'ar' => 'حذف فترة عدم توفر'], 'description' => ['en' => 'Allows deleting staff unavailability', 'ar' => 'السماح بحذف فترات عدم التوفر']],
    ],

    'reviews' => [
        ['key' => 'view_reviews', 'name' => ['en' => 'View Reviews', 'ar' => 'عرض التقييمات'], 'description' => ['en' => 'Allows viewing reviews', 'ar' => 'السماح بعرض التقييمات']],
        ['key' => 'approve_review', 'name' => ['en' => 'Approve Review', 'ar' => 'الموافقة على تقييم'], 'description' => ['en' => 'Allows approving reviews', 'ar' => 'السماح بالموافقة على التقييمات']],
        ['key' => 'reject_review', 'name' => ['en' => 'Reject Review', 'ar' => 'رفض تقييم'], 'description' => ['en' => 'Allows rejecting reviews', 'ar' => 'السماح برفض التقييمات']],
        ['key' => 'delete_review', 'name' => ['en' => 'Delete Review', 'ar' => 'حذف تقييم'], 'description' => ['en' => 'Allows deleting reviews', 'ar' => 'السماح بحذف التقييمات']],
    ],

    'contact_messages' => [
        ['key' => 'view_contact_messages', 'name' => ['en' => 'View Contact Messages', 'ar' => 'عرض رسائل الاتصال'], 'description' => ['en' => 'Allows viewing contact messages', 'ar' => 'السماح بعرض رسائل الاتصال']],
        ['key' => 'show_contact_message', 'name' => ['en' => 'Show Contact Message', 'ar' => 'عرض رسالة اتصال'], 'description' => ['en' => 'Allows viewing contact message details', 'ar' => 'السماح بعرض تفاصيل رسالة الاتصال']],
        ['key' => 'delete_contact_message', 'name' => ['en' => 'Delete Contact Message', 'ar' => 'حذف رسالة اتصال'], 'description' => ['en' => 'Allows deleting contact messages', 'ar' => 'السماح بحذف رسائل الاتصال']],
    ],

    'facility_documents' => [
        ['key' => 'view_facility_documents', 'name' => ['en' => 'View Facility Documents', 'ar' => 'عرض مستندات المنشأة'], 'description' => ['en' => 'Allows viewing facility documents', 'ar' => 'السماح بعرض مستندات المنشأة']],
        ['key' => 'upload_facility_document', 'name' => ['en' => 'Upload Facility Document', 'ar' => 'رفع مستند منشأة'], 'description' => ['en' => 'Allows uploading facility documents', 'ar' => 'السماح برفع مستندات المنشأة']],
        ['key' => 'approve_facility_document', 'name' => ['en' => 'Approve Facility Document', 'ar' => 'الموافقة على مستند منشأة'], 'description' => ['en' => 'Allows approving facility documents', 'ar' => 'السماح بالموافقة على مستندات المنشأة']],
        ['key' => 'reject_facility_document', 'name' => ['en' => 'Reject Facility Document', 'ar' => 'رفض مستند منشأة'], 'description' => ['en' => 'Allows rejecting facility documents', 'ar' => 'السماح برفض مستندات المنشأة']],
        ['key' => 'delete_facility_document', 'name' => ['en' => 'Delete Facility Document', 'ar' => 'حذف مستند منشأة'], 'description' => ['en' => 'Allows deleting facility documents', 'ar' => 'السماح بحذف مستندات المنشأة']],
    ],

    'facility_images' => [
        ['key' => 'view_facility_images', 'name' => ['en' => 'View Facility Images', 'ar' => 'عرض صور المنشأة'], 'description' => ['en' => 'Allows viewing facility images', 'ar' => 'السماح بعرض صور المنشأة']],
        ['key' => 'upload_facility_image', 'name' => ['en' => 'Upload Facility Image', 'ar' => 'رفع صورة منشأة'], 'description' => ['en' => 'Allows uploading facility images', 'ar' => 'السماح برفع صور المنشأة']],
        ['key' => 'delete_facility_image', 'name' => ['en' => 'Delete Facility Image', 'ar' => 'حذف صورة منشأة'], 'description' => ['en' => 'Allows deleting facility images', 'ar' => 'السماح بحذف صور المنشأة']],
    ],

    'reports' => [
        ['key' => 'view_reports', 'name' => ['en' => 'View Reports', 'ar' => 'عرض التقارير'], 'description' => ['en' => 'Allows viewing reports', 'ar' => 'السماح بعرض التقارير']],
        ['key' => 'export_reports', 'name' => ['en' => 'Export Reports', 'ar' => 'تصدير التقارير'], 'description' => ['en' => 'Allows exporting reports', 'ar' => 'السماح بتصدير التقارير']],
    ],

    'notifications' => [
        ['key' => 'view_notifications', 'name' => ['en' => 'View Notifications', 'ar' => 'عرض الإشعارات'], 'description' => ['en' => 'Allows viewing notifications', 'ar' => 'السماح بعرض الإشعارات']],
        ['key' => 'send_notification', 'name' => ['en' => 'Send Notification', 'ar' => 'إرسال إشعار'], 'description' => ['en' => 'Allows sending notifications', 'ar' => 'السماح بإرسال الإشعارات']],
        ['key' => 'delete_notification', 'name' => ['en' => 'Delete Notification', 'ar' => 'حذف إشعار'], 'description' => ['en' => 'Allows deleting notifications', 'ar' => 'السماح بحذف الإشعارات']],
    ],

    'activity_logs' => [
        ['key' => 'view_activity_logs', 'name' => ['en' => 'View Activity Logs', 'ar' => 'عرض سجلات النشاط'], 'description' => ['en' => 'Allows viewing activity logs', 'ar' => 'السماح بعرض سجلات النشاط']],
    ],

    'profile' => [
        ['key' => 'view_profile', 'name' => ['en' => 'View Profile', 'ar' => 'عرض الملف الشخصي'], 'description' => ['en' => 'Allows viewing own profile', 'ar' => 'السماح بعرض الملف الشخصي']],
        ['key' => 'update_profile', 'name' => ['en' => 'Update Profile', 'ar' => 'تحديث الملف الشخصي'], 'description' => ['en' => 'Allows updating own profile', 'ar' => 'السماح بتحديث الملف الشخصي']],
    ],
];
