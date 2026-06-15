<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ArticleCollection extends ResourceCollection
{
    public $collects = ArticleResource::class;
}
