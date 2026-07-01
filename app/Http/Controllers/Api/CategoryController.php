<?php

namespace App\Http\Controllers\Api;

use App\Enums\CategoriesType;
use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function __invoke(CategoriesType $type)
    {
        return Category::where('type', $type)->get();
    }
}
