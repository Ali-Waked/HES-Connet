<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class TriageResultDto
{
    public function __construct(
        public string $specialty,
        public string $urgency,
        public float $confidence,
        /** @var string[] */
        public array $symptoms,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            specialty: $data['specialty'] ?? 'General Practice',
            urgency: in_array($data['urgency'] ?? '', ['low', 'medium', 'high'], true)
                ? $data['urgency']
                : 'low',
            confidence: (float) ($data['confidence'] ?? 0.5),
            symptoms: (array) ($data['symptoms'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'specialty' => $this->specialty,
            'urgency' => $this->urgency,
            'confidence' => $this->confidence,
            'symptoms' => $this->symptoms,
        ];
    }
}
