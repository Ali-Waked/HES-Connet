<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Facility;
use App\Models\JobPost;
use App\Models\Staff;
use App\Models\Story;
use App\Models\User;
use App\Observers\AuditModelObserver;
use App\Policies\AuditLogPolicy;
use App\Policies\StoryPolicy;
use App\Policies\UsersPolicy;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UsersPolicy::class);
        Gate::policy(Story::class, StoryPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        Gate::before(function (User $user, string $ability) {
            if ($user->hasSystemRole('super_admin')) {
                return true;
            }

            return $user->hasSystemPermission($ability) ? true : null;
        });

        Relation::morphMap([
            'facility' => Facility::class,
            'staff' => Staff::class,
            'article' => Article::class,
            'job_post' => JobPost::class,
            'story' => Story::class,
        ]);

        $this->registerAuditObservers();
    }

    private function registerAuditObservers(): void
    {
        $modelPath = app_path('Models');
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modelPath, \FilesystemIterator::SKIP_DOTS),
        );

        $classes = [];
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($modelPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $class = 'App\\Models\\' . str_replace(
                [DIRECTORY_SEPARATOR, '.php'],
                ['\\', ''],
                $relativePath,
            );

            if (in_array(Auditable::class, class_uses_recursive($class), true)) {
                $classes[] = $class;
            }
        }

        foreach ($classes as $class) {
            $class::observe(AuditModelObserver::class);
        }
    }
}
