<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Article;
use App\Models\Facility;
use App\Models\JobPost;
use App\Models\Staff;
use App\Models\Story;

enum FavoriteType: string
{
    case Facility = 'facility';
    case Staff = 'staff';
    case Article = 'article';
    case JobPost = 'job_post';
    case Story = 'story';

    public function model(): string
    {
        return match ($this) {
            self::Facility => Facility::class,
            self::Staff => Staff::class,
            self::Article => Article::class,
            self::JobPost => JobPost::class,
            self::Story => Story::class,
        };
    }
}
