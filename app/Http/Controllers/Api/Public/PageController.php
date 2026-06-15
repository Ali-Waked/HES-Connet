<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug): PageResource
    {
        $page = Page::where('slug', $slug)
            ->where('status', PageStatus::PUBLISHED)
            ->firstOrFail();

        return new PageResource($page);
    }
}
