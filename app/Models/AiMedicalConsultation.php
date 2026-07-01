<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMedicalConsultation extends Model
{
    use Auditable;

    protected $fillable = [
        'patient_id',
        'symptoms',
        'analysis',
        'urgency',
        'recommended_specialties',
        'recommended_doctors',
        'follow_up_questions',
    ];

    protected function casts(): array
    {
        return [
            'recommended_specialties' => 'json',
            'recommended_doctors' => 'json',
            'follow_up_questions' => 'json',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
