<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $comment_service,
    ) {}

    public function hide(Comment $comment): JsonResponse
    {
        $comment = $this->comment_service->hide($comment);

        return response()->json([
            'message' => __('Comment hidden successfully.'),
            'data' => new CommentResource($comment),
        ]);
    }

    public function show(Comment $comment): JsonResponse
    {
        $comment = $this->comment_service->show($comment);

        return response()->json([
            'message' => __('Comment visible successfully.'),
            'data' => new CommentResource($comment),
        ]);
    }
}
