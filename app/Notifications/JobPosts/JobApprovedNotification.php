<?php

declare(strict_types=1);

namespace App\Notifications\JobPosts;

use App\Models\JobPost;
use App\Notifications\BaseNotification;

class JobApprovedNotification extends BaseNotification
{
    public static function forOwner(JobPost $jobPost, ?string $locale = null): static
    {
        return new static(
            event: 'job.approved',
            role: 'owner',
            data: [
                'title' => $jobPost->getTranslations('title')['en'] ?? $jobPost->title,
                'facility' => $jobPost->facility?->name ?? 'Our facility',
                'action_text' => 'View Job',
                'action_url' => route('dashboard.job-posts.show', $jobPost),
            ],
            locale: $locale,
        );
    }
}
