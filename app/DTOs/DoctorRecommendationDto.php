<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class DoctorRecommendationDto
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $specialty,
        public string $department,
        public string $facilityName,
        public string $facilityUuid,
        public ?string $photo,
        public ?int $experienceYears,
        public bool $isAvailable,
        public ?string $nextAvailableAppointment,
    ) {}

    public static function fromStaff($staff, string $facilityName, string $facilityUuid): self
    {
        $userName = $staff->user?->getTranslation('name', app()->getLocale()) ?? '';
        $specializationName = $staff->specialization?->getTranslation('name', app()->getLocale()) ?? '';
        $departmentName = $staff->departments->first()?->getTranslation('name', app()->getLocale()) ?? '';

        return new self(
            uuid: $staff->uuid,
            name: $userName,
            specialty: $specializationName,
            department: $departmentName,
            facilityName: $facilityName,
            facilityUuid: $facilityUuid,
            photo: $staff->user?->avatar ?? null,
            experienceYears: $staff->experience_years,
            isAvailable: $staff->status->value === 'active',
            nextAvailableAppointment: null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'specialty' => $this->specialty,
            'department' => $this->department,
            'facility_name' => $this->facilityName,
            'facility_uuid' => $this->facilityUuid,
            'photo' => $this->photo,
            'experience_years' => $this->experienceYears,
            'is_available' => $this->isAvailable,
            'next_available_appointment' => $this->nextAvailableAppointment,
        ];
    }
}
