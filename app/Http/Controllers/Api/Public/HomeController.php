<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\HomeResource;
use App\Services\HomeService;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $homeService
    ) {}

    public function __invoke(): HomeResource
    {
        info('hi');
        // info( new HomeResource(
        //     $this->homeService->getHomeData()
        // )->toArray());
        info($this->homeService->getHomeData());

        return new HomeResource(
            $this->homeService->getHomeData()
        );
    }
}
