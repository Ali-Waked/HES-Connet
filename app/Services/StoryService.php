<?php

namespace App\Services;

use App\Enums\StoryStatus;
use App\Models\Patient;
use App\Models\Story;

class StoryService
{
    public function create(Patient $patient, array $data): Story
    {
        return Story::create([
            'patient_id' => $patient->id,
            'content' => $data['content'],
            'status' => StoryStatus::PENDING->value,
            'is_fundraising' => $data['is_fundraising'] ?? false,
            'target_amount' => $data['target_amount'] ?? null,
            'collected_amount' => 0,
        ]);
    }
}
