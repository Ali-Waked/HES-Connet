<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('articles.view');
    }

    public function view(User $user, Article $article): bool
    {
        return $user->hasPermission('articles.view') && $article->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('articles.manage');
    }

    public function update(User $user, Article $article): bool
    {
        return $user->hasPermission('articles.manage') && $article->author_id === $user->id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasPermission('articles.manage') && $article->author_id === $user->id;
    }
}
