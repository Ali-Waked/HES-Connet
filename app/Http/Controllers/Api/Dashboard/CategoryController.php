<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $category_service
    ) {}

    public function index()
    {
        return CategoryResource::collection(
            $this->category_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('type'),
                request('is_active') !== null ? filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN) : null
            )
        );
    }

    public function store(StoreCategoryRequest $request): Category
    {
        $category = $this->category_service->create(
            $request->validated()
        );

        return $category;
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => [
                'name' => $category->getTranslations('name'),
                'description' => $category->getTranslations('description'),
                'is_active' => $category->is_active,
                'type' => $category->type,
            ],
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->category_service->update(
            $category,
            $request->validated()
        );

        return response()->json([
            'message' => __('Category updated successfully.'),
            'data' => new CategoryResource($category),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->category_service->destroy($category);

        return response()->json([
            'message' => __('Category deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->category_service->getStats()
        );
    }
}
