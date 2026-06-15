<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DoctorCollection extends ResourceCollection
{
    public $collects = DoctorResource::class;
}
