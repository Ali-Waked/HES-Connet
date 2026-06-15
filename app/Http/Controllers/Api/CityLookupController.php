<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;

class CityLookupController extends Controller
{
    public function index()
    {
        return City::where('is_active', true)
            ->orderBy('name->ar')
            ->get()
            ->map(fn (City $city) => [
                'uuid' => $city->uuid,
                'name' => $city->getTranslations('name'),
            ]);
    }
}
