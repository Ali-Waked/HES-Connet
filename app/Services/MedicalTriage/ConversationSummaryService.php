<?php

declare(strict_types=1);

namespace App\Services\MedicalTriage;

use App\Models\AiMedicalConversation;

class ConversationSummaryService
{
    private const MIN_MESSAGES_FOR_SUMMARY = 6;

    public function shouldUpdateSummary(AiMedicalConversation $conversation): bool
    {
        return $conversation->messages()->count() >= self::MIN_MESSAGES_FOR_SUMMARY;
    }

    public function extractSummary(AiMedicalConversation $conversation): array
    {
        $userMessages = $conversation->messages()
            ->where('role', 'user')
            ->orderBy('created_at', 'asc')
            ->get();

        $summary = [
            'symptoms' => [],
            'duration' => null,
            'pain_level' => null,
            'specialty' => $conversation->estimated_specialty,
            'urgency' => $conversation->urgency,
        ];

        foreach ($userMessages as $msg) {
            $content = mb_strtolower($msg->content);

            $this->extractPainLevel($content, $summary);
            $this->extractDuration($content, $summary);
        }

        if ($conversation->extracted_symptoms) {
            $summary['symptoms'] = $conversation->extracted_symptoms;
        }

        return $summary;
    }

    public function buildSummaryText(array $summary): string
    {
        $lines = [];

        if (! empty($summary['symptoms'])) {
            $lines[] = 'Symptoms: '.implode(', ', $summary['symptoms']);
        }

        if ($summary['duration']) {
            $lines[] = 'Duration: '.$summary['duration'];
        }

        if ($summary['pain_level']) {
            $lines[] = 'Pain Level: '.$summary['pain_level'].'/10';
        }

        if ($summary['specialty']) {
            $lines[] = 'Suggested Specialty: '.$summary['specialty'];
        }

        if ($summary['urgency']) {
            $lines[] = 'Urgency: '.ucfirst($summary['urgency']);
        }

        return implode("\n", $lines);
    }

    private function extractPainLevel(string $content, array &$summary): void
    {
        if (preg_match('/(\d+)\s*(?:out of 10|\/10|من\s*10|على\s*10)/u', $content, $matches)) {
            $level = (int) $matches[1];
            if ($level >= 1 && $level <= 10) {
                $summary['pain_level'] = $level;
            }
        }
    }

    private function extractDuration(string $content, array &$summary): void
    {
        $durationPatterns = [
            '/(?:since|for|durante|منذ|لحظة|قبل)\s+(.+?)(?:\.|,|$)/u',
            '/(\d+\s*(?:days?|hours?|weeks?|months?|days|hours|weeks|months))/u',
            '/(\d+\s*(?:يوم|أيام|ساعة|ساعات|أسبوع|أسابيع|شهر|أشهر))/u',
        ];

        foreach ($durationPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $summary['duration'] = trim($matches[1]);

                return;
            }
        }
    }
}
