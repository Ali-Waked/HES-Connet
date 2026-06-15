<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Page\StorePageRequest;
use App\Http\Requests\Page\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function __construct(
        private readonly PageService $page_service
    ) {}

    public function index()
    {
        return PageResource::collection(
            $this->page_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('status')
            )
        );
    }

    public function store(StorePageRequest $request): JsonResponse
    {
        $page = $this->page_service->create(
            $request->validated()
        );

        return response()->json([
            'message' => __('Page created successfully.'),
            'data' => new PageResource($page),
        ], 201);
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json([
            'data' => new PageResource($page),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $page = $this->page_service->update(
            $page,
            $request->validated()
        );

        return response()->json([
            'message' => __('Page updated successfully.'),
            'data' => new PageResource($page),
        ]);
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->page_service->destroy($page);

        return response()->json([
            'message' => __('Page deleted successfully.'),
        ]);
    }
}
