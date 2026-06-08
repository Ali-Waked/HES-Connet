<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleService $article_service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index():LengthAwarePaginator
    {
        return $this->article_service->paginate(
            request('per_page', 15),
            request('search'),
            request('category_id'),
            request('auth_id')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article):Article
    {
        return $this->article_service->show($article);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $this->article_service->destroy($article);
        return response()->json([
            'message'=> __('Article deleted successfully.')
        ]);
    }
}
