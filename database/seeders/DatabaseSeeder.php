<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // System foundation
            ProfessionsSeeder::class,
            PermissionSeeder::class,
            RolesSeeder::class,
            RolePermissionSeeder::class,
            AdminSeeder::class,
            CitySeeder::class,
            PageSeeder::class,
            StaffPositionSeeder::class,

            // Core data
            UserSeeder::class,
            OrganizationSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            SymptomSeeder::class,
            MedicineSeeder::class,

            // Facilities and staff
            FacilitySeeder::class,
            StaffSeeder::class,
            PatientSeeder::class,
            DepartmentSeeder::class,
            FacilityStaffSeeder::class,
            FacilityStaffPermissionSeeder::class,
            FacilityImageSeeder::class,
            FacilityDocumentSeeder::class,
            PharmacyMedicineSeeder::class,

            // Content
            ArticleSeeder::class,
            StorySeeder::class,
            JobPostSeeder::class,

            // Engagement
            CommentSeeder::class,
            PlatformReviewSeeder::class,
            FacilityReviewSeeder::class,
            ReviewNotificationSeeder::class,

            // Appointments and operations
            AppointmentSeeder::class,

            // Reviews depend on completed appointments
            ReviewSeeder::class,

            // Financial
            DonationSeeder::class,

            // User engagement
            SubscriptionSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
