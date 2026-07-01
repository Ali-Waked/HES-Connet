<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\CommentAdded;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function paginateByArticle(Article $article, int $perPage = 20, bool $includeHidden = false): LengthAwarePaginator
    {
        return $article->comments()
            ->with(['user', 'user.profile'])
            ->when(! $includeHidden, fn ($q) => $q->visible())
            ->latest()
            ->paginate($perPage);
    }

    public function create(Article $article, User $user, array $data): Comment
    {
        return DB::transaction(function () use ($article, $user, $data) {
            $comment = $article->comments()->create([
                'user_id' => $user->id,
                'content' => $data['content'],
            ]);

            event(new CommentAdded($comment));

            return $comment;
        });
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update([
            'content' => $data['content'],
        ]);

        return $comment->fresh(['user', 'user.profile']);
    }

    public function destroy(Comment $comment): void
    {
        $comment->delete();
    }

    public function hide(Comment $comment): Comment
    {
        $comment->update(['is_hidden' => true]);

        return $comment->fresh(['user', 'user.profile']);
    }

    public function show(Comment $comment): Comment
    {
        $comment->update(['is_hidden' => false]);

        return $comment->fresh(['user', 'user.profile']);
    }
}
