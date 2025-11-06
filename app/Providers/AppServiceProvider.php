<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Gate::before(function ($user, $ability) {
        //     return $user->hasRole('admin') ? true : null; // admin bypasses checks
        // });

        Vite::prefetch(concurrency: 3);

        // LogViewer::auth(function ($request) {
        //     return $request->user()->hasPermissionTo('logs.view');
        // });

        Inertia::share('userTz', function () {
            return optional(Auth::user())->timezone
                ?? session('user_timezone', 'UTC');
        });
    }
}
