<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $comment_service,
    ) {}

    public function index(Article $article)
    {
        $comments = $this->comment_service->paginateByArticle($article);

        return CommentResource::collection($comments);
    }

    public function store(Article $article, StoreCommentRequest $request): JsonResponse
    {
        $comment = $this->comment_service->create(
            $article,
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => __('Comment created successfully.'),
            'data' => new CommentResource($comment->load(['user', 'user.profile'])),
        ], 201);
    }

    public function update(Article $article, Comment $comment, UpdateCommentRequest $request): JsonResponse
    {
        if ($comment->user_id !== $request->user()->id) {
            abort(403, __('You can only edit your own comments.'));
        }

        $comment = $this->comment_service->update($comment, $request->validated());

        return response()->json([
            'message' => __('Comment updated successfully.'),
            'data' => new CommentResource($comment),
        ]);
    }

    public function destroy(Article $article, Comment $comment): JsonResponse
    {
        $user = request()->user();

        if ($comment->user_id !== $user->id && ! $user->hasSystemRole('super_admin')) {
            abort(403, __('Unauthorized.'));
        }

        $this->comment_service->destroy($comment);

        return response()->json([
            'message' => __('Comment deleted successfully.'),
        ]);
    }
}
