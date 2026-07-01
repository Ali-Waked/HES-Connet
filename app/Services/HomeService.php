<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FacilityType;
use App\Enums\PageStatus;
use App\Enums\StoryStatus;
use App\Models\Article;
use App\Models\Facility;
use App\Models\JobPost;
use App\Models\Organization;
use App\Models\Page;
use App\Models\PlatformReview;
use App\Models\Staff;
use App\Models\Story;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomeService
{
    private const CACHE_KEY = 'homepage_data';

    private const CACHE_TTL = 600;

    public function getHomeData(): array
    {
        info([
            'statistics' => $this->getStatistics(),
            'featured_facilities' => $this->getFeaturedFacilities(),
            'facility_categories' => $this->getFacilityCategories(),
            'latest_articles' => $this->getLatestArticles(),
            'latest_stories' => $this->getLatestStories(),
            'latest_jobs' => $this->getLatestJobs(),
            'about_preview' => $this->getAboutPreview(),
            'platform_reviews' => $this->getFeaturedPlatformReviews(),
        ]);

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return [
                'statistics' => $this->getStatistics(),
                'featured_facilities' => $this->getFeaturedFacilities(),
                'facility_categories' => $this->getFacilityCategories(),
                'latest_articles' => $this->getLatestArticles(),
                'latest_stories' => $this->getLatestStories(),
                'latest_jobs' => $this->getLatestJobs(),
                'about_preview' => $this->getAboutPreview(),
                'platform_reviews' => $this->getFeaturedPlatformReviews(),
            ];
        });
    }

    private function approvedFacilities()
    {
        return Facility::approved();
    }

    private function getStatistics(): array
    {
        return [
            'facilities_count' => $this->approvedFacilities()->count(),
            'doctors_count' => Staff::doctors()->count(),
            'organizations_count' => Organization::count(),
            'articles_count' => Article::published()->count(),
            'stories_count' => Story::where('status', StoryStatus::APPROVED)->count(),
            'jobs_count' => JobPost::approved()->published()->count(),
        ];
    }

    private function getFeaturedFacilities(): array
    {
        return $this->approvedFacilities()
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Facility $facility) => [
                'uuid' => $facility->uuid,
                'name' => $facility->getTranslation('name', app()->getLocale()),
                'description' => $facility->getTranslation('description', app()->getLocale()),
                'facility_type' => $facility->facility_type?->value,
                'cover_image' => $facility->cover_image,
            ])
            ->all();
    }

    private function getFacilityCategories(): array
    {
        $counts = $this->approvedFacilities()
            ->selectRaw('facility_type, COUNT(*) as count')
            ->groupBy('facility_type')
            ->pluck('count', 'facility_type');

        return collect(FacilityType::cases())
            ->map(fn (FacilityType $type) => [
                'type' => $type->value,
                'count' => (int) ($counts[$type->value] ?? 0),
            ])
            ->all();
    }

    private function getLatestArticles(): array
    {
        return Article::published()
            ->with([
                'author:id,uuid,name',
                'category:id,uuid,name',
            ])
            ->latest('published_at')
            ->take(6)
            ->get()
            ->map(fn (Article $article) => [
                'uuid' => $article->uuid,
                'title' => $article->getTranslation('title', app()->getLocale()),
                'cover_image' => $article->cover_image,
                'published_at' => $article->published_at,
                'views' => $article->views,
                'category' => $article->category ? [
                    'uuid' => $article->category->uuid,
                    'name' => $article->category->getTranslation('name', app()->getLocale()),
                ] : null,
                'author' => $article->author ? [
                    'uuid' => $article->author->uuid,
                    'name' => $article->author->getTranslation('name', app()->getLocale()),
                ] : null,
            ])
            ->all();
    }

    private function getLatestStories(): array
    {
        return Story::where('status', StoryStatus::APPROVED)
            ->with([
                'patient.user:id,uuid,name',
                'category:id,uuid,name',
            ])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Story $story) => [
                'uuid' => $story->uuid,
                'title' => $story->getTranslation('title', app()->getLocale()),
                'cover_image' => $story->cover_image,
                'is_fundraising' => $story->is_fundraising,
                'category' => $story->category ? [
                    'uuid' => $story->category->uuid,
                    'name' => $story->category->getTranslation('name', app()->getLocale()),
                ] : null,
                'patient' => $story->patient?->user ? [
                    'uuid' => $story->patient->user->uuid,
                    'name' => $story->patient->user->getTranslation('name', app()->getLocale()),
                ] : null,
            ])
            ->all();
    }

    private function getLatestJobs(): array
    {
        return JobPost::approved()
            ->published()
            ->with([
                'facility:id,uuid,name',
                'category:id,uuid,name',
            ])
            ->latest('published_at')
            ->take(6)
            ->get()
            ->map(fn (JobPost $job) => [
                'uuid' => $job->uuid,
                'title' => $job->getTranslation('title', app()->getLocale()),
                'cover_image' => $job->cover_image,
                'facility' => $job->facility ? [
                    'uuid' => $job->facility->uuid,
                    'name' => $job->facility->getTranslation('name', app()->getLocale()),
                ] : null,
                'category' => $job->category ? [
                    'uuid' => $job->category->uuid,
                    'name' => $job->category->getTranslation('name', app()->getLocale()),
                ] : null,
                'employment_type' => $job->employment_type?->value,
                'experience_level' => $job->experience_level?->value,
                'location' => $job->location,
                'published_at' => $job->published_at,
            ])
            ->all();
    }

    private function getAboutPreview(): ?array
    {
        $page = Page::query()
            ->where('slug', 'about-us')
            ->where('status', PageStatus::PUBLISHED)
            ->first();

        if (! $page) {
            return null;
        }

        $content = $page->getTranslations('content');
        $locale = app()->getLocale();

        $body = $content[$locale] ?? ($content['en'] ?? '');

        return [
            'title' => $page->getTranslation('title', $locale),
            'excerpt' => Str::limit(strip_tags($body), 200),
        ];
    }

    private function getFeaturedPlatformReviews(): array
    {
        return PlatformReview::approved()
            ->where('is_featured', true)
            ->with(['user:id,uuid,name', 'user.profile'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($review) => [
                'rating' => $review->rating,
                'comment' => $review->comment,
                'user' => [
                    'name' => $review->user->name,
                    'avatar' => $review->user->avatar,
                ],
            ])
            ->all();
    }
}
