<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Observers\PostObserver;
use App\Observers\ProjectObserver;
use App\Observers\SettingObserver;
use App\Services\Activity\ActivityProvider;
use App\Services\Activity\ResilientActivityProvider;
use App\Services\FrontendRevalidator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ActivityProvider::class, ResilientActivityProvider::class);
        $this->app->singleton(FrontendRevalidator::class);
    }

    public function boot(): void
    {
        Post::observe(PostObserver::class);
        Project::observe(ProjectObserver::class);
        Setting::observe(SettingObserver::class);

        RateLimiter::for('public', function (Request $request) {
            $secret = (string) config('services.frontend.secret');
            $given = (string) $request->header('X-Frontend-Secret');

            if ($secret !== '' && $given !== '' && hash_equals($secret, $given)) {
                return Limit::none();
            }

            return Limit::perMinute(120)->by($request->ip());
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
