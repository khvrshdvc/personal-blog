<?php

namespace App\Providers;

use App\Services\Activity\ActivityProvider;
use App\Services\Activity\ResilientActivityProvider;
use App\View\Composers\AdminNavComposer;
use App\View\Composers\NavComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ActivityProvider::class, ResilientActivityProvider::class);
    }

    public function boot(): void
    {
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        View::composer('components.nav.*', NavComposer::class);
        View::composer('components.layouts.admin', AdminNavComposer::class);
    }
}
