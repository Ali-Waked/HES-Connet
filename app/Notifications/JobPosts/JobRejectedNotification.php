<?php

declare(strict_types=1);

namespace App\Notifications\JobPosts;

use App\Models\JobPost;
use App\Notifications\BaseNotification;

class JobRejectedNotification extends BaseNotification
{
    public static function forOwner(JobPost $jobPost, ?string $locale = null): static
    {
        return new static(
            event: 'job.rejected',
            role: 'owner',
            data: [
                'title' => $jobPost->getTranslations('title')['en'] ?? $jobPost->title,
                'reason' => $jobPost->rejected_reason ?? '',
                'facility' => $jobPost->facility?->name ?? 'Our facility',
                'action_text' => 'Edit Job',
                'action_url' => route('dashboard.job-posts.show', $jobPost),
            ],
            locale: $locale,
        );
    }
}
