<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FacilityCollection extends ResourceCollection
{
    public $collects = FacilityResource::class;
}
