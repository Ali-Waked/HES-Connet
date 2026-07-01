<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Resources\SymptomResource;
use App\Services\SymptomService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SymptomController extends Controller
{
    public function __construct(
        private readonly SymptomService $symptom_service,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return SymptomResource::collection(
            $this->symptom_service->getActive(
                search: request('search'),
                perPage: (int) request('per_page', 50),
            )
        );
    }
}
