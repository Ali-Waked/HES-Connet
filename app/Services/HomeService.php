<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FacilityApprovalStatus;
use App\Enums\FacilityStatus;
use App\Enums\FacilityType;
use App\Enums\PageStatus;
use App\Models\Article;
use App\Models\Facility;
use App\Models\Organization;
use App\Models\Page;
use App\Models\Staff;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeService
{
    public function getHomeData(): array
    {
        return Cache::remember('homepage_data', 600, function () {
            return [
                'statistics' => $this->getStatistics(),
                'featured_facilities' => $this->getFeaturedFacilities(),
                'facility_categories' => $this->getFacilityCategories(),
                'latest_articles' => $this->getLatestArticles(),
                'about_preview' => $this->getAboutPreview(),
            ];
        });
    }

    private function getStatistics(): array
    {
        return [
            'facilities_count' => Facility::where('status', FacilityStatus::ACTIVE)
                ->where('approval_status', FacilityApprovalStatus::APPROVED)
                ->count(),
            'doctors_count' => Staff::doctors()->count(),
            'organizations_count' => Organization::count(),
            'articles_count' => Article::published()->count(),
        ];
    }

    private function getFeaturedFacilities(): array
    {
        return Facility::where('status', FacilityStatus::ACTIVE)
            ->where('approval_status', FacilityApprovalStatus::APPROVED)
            ->with(['facilityImages', 'organization'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Facility $facility) => [
                'uuid' => $facility->uuid,
                'name' => $facility->getTranslations('name'),
                'description' => $facility->getTranslations('description'),
                'facility_type' => $facility->facility_type?->value,
                'cover_image' => $facility->cover_image
                    ? Storage::disk('public')->url($facility->cover_image)
                    : null,
            ])
            ->all();
    }

    private function getFacilityCategories(): array
    {
        $counts = Facility::where('status', FacilityStatus::ACTIVE)
            ->where('approval_status', FacilityApprovalStatus::APPROVED)
            ->selectRaw('facility_type, count(*) as count')
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
            ->with(['author', 'category'])
            ->latest('published_at')
            ->take(6)
            ->get()
            ->map(fn (Article $article) => [
                'uuid' => $article->uuid,
                'title' => $article->getTranslations('title'),
                'cover_image' => $article->cover_image,
                'published_at' => $article->published_at,
                'views' => $article->views,
                'category' => $article->category ? [
                    'uuid' => $article->category->uuid,
                    'name' => $article->category->getTranslations('name'),
                ] : null,
                'author' => $article->author ? [
                    'uuid' => $article->author->uuid,
                    'name' => $article->author->getTranslations('name'),
                ] : null,
            ])
            ->all();
    }

    private function getAboutPreview(): ?array
    {
        $page = Page::where('slug', 'about-us')
            ->where('status', PageStatus::PUBLISHED)
            ->first();

        if (! $page) {
            return null;
        }

        $locale = app()->getLocale();
        $content = $page->getTranslations('content');
        $body = $content[$locale] ?? ($content['en'] ?? '');

        return [
            'title' => $page->getTranslations('title'),
            'excerpt' => Str::limit(strip_tags($body), 200),
        ];
    }
}
