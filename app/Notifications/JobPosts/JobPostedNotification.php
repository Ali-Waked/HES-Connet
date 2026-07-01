<?php

declare(strict_types=1);

namespace App\Notifications\JobPosts;

use App\Models\JobPost;
use App\Notifications\BaseNotification;

class JobPostedNotification extends BaseNotification
{
    public static function forAdmin(JobPost $jobPost, ?string $locale = null): static
    {
        return new static(
            event: 'job.posted',
            role: 'admin',
            data: [
                'title' => $jobPost->title,
                'facility' => $jobPost->facility?->name ?? 'Our facility',
                'action_text' => 'View Job',
                'action_url' => route('job-posts.show', $jobPost),
            ],
            locale: $locale,
        );
    }
}
