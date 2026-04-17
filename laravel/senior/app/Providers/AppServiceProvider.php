<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Note;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Task;
use App\Policies\ClientPolicy;
use App\Policies\NotePolicy;
use App\Policies\ProjectFilePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::unguard(false);
        Model::preventLazyLoading(! app()->isProduction());

        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Note::class, NotePolicy::class);
        Gate::policy(ProjectFile::class, ProjectFilePolicy::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();

            return $user
                ? Limit::perMinute(60)->by((string) $user->id)
                : Limit::perMinute(20)->by($request->ip());
        });
    }
}
