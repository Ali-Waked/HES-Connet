<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\FacilityStaff;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patientIds = Patient::pluck('id')->toArray();

        $reasons = [
            'Regular checkup', 'Follow-up visit', 'Consultation for persistent cough',
            'Blood pressure monitoring', 'Vaccination', 'Annual physical examination',
            'Skin rash evaluation', 'Abdominal pain', 'Eye examination',
            'Dental checkup', 'Prenatal visit', 'Allergy consultation',
            'Joint pain evaluation', 'Hearing test', 'Diabetes management',
            'Thyroid function check', 'Heart rhythm evaluation', 'Migraine treatment',
            'Respiratory infection', 'Digestive issues',
        ];

        $statuses = [
            AppointmentStatus::COMPLETED, AppointmentStatus::COMPLETED,
            AppointmentStatus::COMPLETED, AppointmentStatus::COMPLETED,
            AppointmentStatus::SCHEDULED, AppointmentStatus::SCHEDULED,
            AppointmentStatus::CONFIRMED, AppointmentStatus::CANCELLED,
            AppointmentStatus::NO_SHOW, AppointmentStatus::IN_PROGRESS,
        ];

        FacilityStaff::chunkById(200, function ($facilityStaffRecords) use ($patientIds, $reasons, $statuses) {
            $records = [];
            foreach ($facilityStaffRecords as $fs) {
                $numAppointments = fake()->numberBetween(1, 5);
                for ($i = 0; $i < $numAppointments; $i++) {
                    $patientId = $patientIds[array_rand($patientIds)];
                    $status = $statuses[array_rand($statuses)];
                    $startAt = fake()->dateTimeBetween('-3 months', '+1 month');
                    $endAt = (clone $startAt)->modify('+'.fake()->numberBetween(15, 60).' minutes');

                    $records[] = [
                        'uuid' => Str::uuid(),
                        'facility_staff_id' => $fs->id,
                        'patient_id' => $patientId,
                        'start_at' => $startAt->format('Y-m-d H:i:s'),
                        'end_at' => $endAt->format('Y-m-d H:i:s'),
                        'status' => $status,
                        'reason' => $reasons[array_rand($reasons)],
                        'notes' => in_array($status, [AppointmentStatus::COMPLETED, AppointmentStatus::CONFIRMED])
                            ? fake()->optional(0.6)->sentence()
                            : null,
                        'cancellation_reason' => $status === AppointmentStatus::CANCELLED
                            ? fake()->randomElement(['Patient cancelled', 'Emergency', 'Schedule conflict', 'Patient no-show'])
                            : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            Appointment::insert($records);
        });
    }
}
