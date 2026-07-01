<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\Article;
use App\Models\Comment;
use App\Models\FacilityStaff;
use App\Models\JobPost;
use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRecipientResolver
{
    public function admins(): Collection
    {
        return User::whereHas('systemRoles', fn ($q) => $q->where('slug', 'super_admin'))
            ->get();
    }

    public function articleAuthor(Article $article): ?User
    {
        return $article->author;
    }

    public function articleAuthorAndAdmins(Article $article): Collection
    {
        return collect()
            ->when($article->author, fn ($c, $author) => $c->push($author))
            ->merge($this->admins())
            ->unique('id');
    }

    public function storyOwner(Story $story): ?User
    {
        return $story->patient?->user;
    }

    public function storyOwnerAndAdmins(Story $story): Collection
    {
        return collect()
            ->when($story->patient?->user, fn ($c, $user) => $c->push($user))
            ->merge($this->admins())
            ->unique('id');
    }

    public function commentContentOwner(Comment $comment): ?User
    {
        return $comment->article?->author;
    }

    public function jobPostCreator(JobPost $jobPost): ?User
    {
        return $jobPost->user;
    }

    public function staffUser(FacilityStaff $facilityStaff): ?User
    {
        return $facilityStaff->staff?->user;
    }

    public function facilityAdmins(FacilityStaff $facilityStaff): Collection
    {
        $facilityId = $facilityStaff->facility_id;

        return User::whereHas('staff.facilityStaff', fn ($q) => $q
            ->where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($r) => $r->where('slug', 'facility_admin'))
        )->get();
    }

    public function staffAndFacilityAdmins(FacilityStaff $facilityStaff): Collection
    {
        return collect()
            ->when($facilityStaff->staff?->user, fn ($c, $user) => $c->push($user))
            ->merge($this->facilityAdmins($facilityStaff))
            ->unique('id');
    }
}
