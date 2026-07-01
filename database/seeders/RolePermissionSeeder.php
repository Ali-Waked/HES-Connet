<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $byKey = Permission::all()->keyBy('key');

        $this->assignSuperAdmin($byKey);
        $this->assignOrganizationAdmin($byKey);
        $this->assignHospitalAdmin($byKey);
        $this->assignClinicAdmin($byKey);
        $this->assignDepartmentManager($byKey);
        $this->assignContentManager($byKey);
        $this->assignFinanceManager($byKey);
        $this->assignDoctorPortalUser($byKey);
        $this->assignPharmacyPortalUser($byKey);
        $this->assignPatientPortalUser($byKey);
    }

    private function assignSuperAdmin(Collection $byKey): void
    {
        $role = Role::where('slug', 'super_admin')->first();
        if ($role) {
            $role->permissions()->sync($byKey->pluck('id')->values()->toArray());
        }
    }

    private function assignOrganizationAdmin(Collection $byKey): void
    {
        $role = Role::where('slug', 'organization_admin')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_dashboard_statistics',
            'view_organizations', 'show_organization', 'create_organization', 'update_organization', 'delete_organization',
            'view_facilities', 'show_facility', 'create_facility', 'update_facility', 'delete_facility',
            'view_departments', 'show_department',
            'view_staff', 'show_staff', 'create_staff', 'update_staff', 'delete_staff',
            'view_patients', 'show_patient',
            'view_users', 'show_user',
            'view_roles', 'show_role',
            'view_permissions', 'show_permission',
            'view_reports', 'export_reports',
            'view_notifications', 'send_notification',
            'view_activity_logs',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }

    private function assignHospitalAdmin(Collection $byKey): void
    {
        $role = Role::where('slug', 'hospital_admin')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_dashboard_statistics',
            'view_facilities', 'show_facility', 'update_facility',
            'view_departments', 'show_department', 'create_department', 'update_department', 'delete_department',
            'view_staff', 'show_staff', 'create_staff', 'update_staff', 'delete_staff',
            'view_patients', 'show_patient', 'create_patient', 'update_patient',
            'view_appointments', 'show_appointment', 'create_appointment', 'update_appointment', 'cancel_appointment',
            'view_prescriptions', 'show_prescription', 'create_prescription', 'update_prescription',
            'view_medication_requests', 'show_medication_request',
            'view_medical_records', 'show_medical_record',
            'view_staff_schedules', 'create_staff_schedule', 'update_staff_schedule', 'delete_staff_schedule',
            'view_staff_unavailabilities', 'create_staff_unavailability', 'update_staff_unavailability', 'delete_staff_unavailability',
            'view_reviews',
            'view_facility_documents', 'upload_facility_document', 'delete_facility_document',
            'view_facility_images', 'upload_facility_image', 'delete_facility_image',
            'view_reports', 'export_reports',
            'view_notifications', 'send_notification',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }

    private function assignClinicAdmin(Collection $byKey): void
    {
        $role = Role::where('slug', 'clinic_admin')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_dashboard_statistics',
            'view_facilities', 'show_facility', 'update_facility',
            'view_departments', 'show_department',
            'view_staff', 'show_staff', 'create_staff', 'update_staff', 'delete_staff',
            'view_patients', 'show_patient', 'create_patient', 'update_patient',
            'view_appointments', 'show_appointment', 'create_appointment', 'update_appointment', 'cancel_appointment',
            'view_prescriptions', 'show_prescription',
            'view_medication_requests', 'show_medication_request',
            'view_medical_records', 'show_medical_record',
            'view_staff_schedules',
            'view_reviews',
            'view_notifications', 'send_notification',
            'view_reports',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }

    private function assignDepartmentManager(Collection $byKey): void
    {
        $role = Role::where('slug', 'department_manager')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_dashboard_statistics',
            'view_departments', 'show_department',
            'view_staff', 'show_staff',
            'view_patients', 'show_patient',
            'view_appointments', 'show_appointment',
            'view_staff_schedules',
            'view_notifications',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }

    private function assignContentManager(Collection $byKey): void
    {
        $role = Role::where('slug', 'content_manager')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_articles', 'show_article', 'create_article', 'update_article', 'delete_article',
            'view_stories', 'show_story', 'create_story', 'update_story', 'delete_story',
            'view_contact_messages', 'show_contact_message', 'delete_contact_message',
            'view_notifications', 'send_notification',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }

    private function assignFinanceManager(Collection $byKey): void
    {
        $role = Role::where('slug', 'finance_manager')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_dashboard_statistics',
            'view_appointments', 'show_appointment',
            'view_reports', 'export_reports',
            'view_notifications',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }

    private function assignDoctorPortalUser(Collection $byKey): void
    {
        $role = Role::where('slug', 'doctor_portal_user')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_patients', 'show_patient',
            'view_appointments', 'show_appointment', 'create_appointment', 'update_appointment', 'cancel_appointment',
            'view_prescriptions', 'show_prescription', 'create_prescription', 'update_prescription',
            'view_medication_requests', 'show_medication_request', 'create_medication_request', 'update_medication_request',
            'view_medical_records', 'show_medical_record', 'create_medical_record', 'update_medical_record',
            'view_reviews',
            'view_notifications',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }

    private function assignPharmacyPortalUser(Collection $byKey): void
    {
        $role = Role::where('slug', 'pharmacy_portal_user')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_prescriptions', 'show_prescription',
            'view_medication_requests', 'show_medication_request', 'update_medication_request',
            'approve_medication_request', 'reject_medication_request',
            'view_medicines', 'show_medicine',
            'view_notifications',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }

    private function assignPatientPortalUser(Collection $byKey): void
    {
        $role = Role::where('slug', 'patient_portal_user')->first();
        if (! $role) {
            return;
        }

        $role->permissions()->sync(collect([
            'view_appointments', 'show_appointment', 'create_appointment', 'cancel_appointment',
            'view_prescriptions', 'show_prescription',
            'view_medication_requests', 'show_medication_request', 'create_medication_request',
            'view_medical_records', 'show_medical_record',
            'view_profile', 'update_profile',
        ])->map(fn (string $key) => $byKey->get($key)?->id)->filter()->values()->toArray());
    }
}
