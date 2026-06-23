<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Services\FacilityPortalService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private readonly FacilityPortalService $portal_service,
    ) {}

    public function index(Request $request)
    {
        $articles = $this->portal_service->articlesPaginate(
            (int) $request->get('per_page', 15),
            $request->get('search'),
        );

        return response()->json($articles);
    }
}
