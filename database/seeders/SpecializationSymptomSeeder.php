<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Specialization;
use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SpecializationSymptomSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = [
            'Fever' => ['General Medicine', 'Infectious Diseases', 'Emergency Medicine'],
            'Cough' => ['General Medicine', 'Pulmonology', 'Family Medicine'],
            'Headache' => ['General Medicine', 'Neurology', 'Emergency Medicine'],
            'Fatigue' => ['General Medicine', 'Internal Medicine', 'Family Medicine'],
            'Nausea' => ['General Medicine', 'Gastroenterology', 'Emergency Medicine'],
            'Dizziness' => ['General Medicine', 'Neurology', 'Emergency Medicine', 'Ear, Nose and Throat'],
            'Chest Pain' => ['Cardiology', 'Emergency Medicine', 'Pulmonology'],
            'Shortness of Breath' => ['Pulmonology', 'Cardiology', 'Emergency Medicine'],
            'Sore Throat' => ['General Medicine', 'Ear, Nose and Throat', 'Family Medicine'],
            'Muscle Ache' => ['General Medicine', 'Orthopedics', 'Rheumatology', 'Sports Medicine'],
            'Back Pain' => ['Orthopedics', 'General Medicine', 'Neurosurgery', 'Sports Medicine'],
            'Abdominal Pain' => ['Gastroenterology', 'General Surgery', 'General Medicine', 'Emergency Medicine'],
            'Diarrhea' => ['Gastroenterology', 'General Medicine', 'Infectious Diseases'],
            'Constipation' => ['Gastroenterology', 'General Medicine'],
            'Skin Rash' => ['Dermatology', 'Allergy and Immunology', 'General Medicine'],
            'Joint Pain' => ['Orthopedics', 'Rheumatology', 'Sports Medicine', 'General Medicine'],
            'Loss of Appetite' => ['General Medicine', 'Internal Medicine', 'Gastroenterology'],
            'Blurred Vision' => ['Ophthalmology', 'Neurology', 'General Medicine'],
            'Ear Pain' => ['Ear, Nose and Throat', 'General Medicine', 'Pediatrics'],
            'Numbness' => ['Neurology', 'Orthopedics', 'Neurosurgery'],
        ];

        foreach ($mappings as $symptomName => $specializationNames) {
            $symptom = Symptom::where('name->en', $symptomName)->first();

            if (! $symptom) {
                continue;
            }

            foreach ($specializationNames as $specName) {
                $specialization = Specialization::where('name->en', $specName)->first();

                if (! $specialization) {
                    continue;
                }

                $symptom->specializations()->syncWithoutDetaching([$specialization->id]);
            }
        }
    }
}
