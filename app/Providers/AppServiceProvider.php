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

        Inertia::share('notifications', function () {
            $user = Auth::user();
            if (!$user) return null;


            // Keep payload small for every request
            $latest = $user->notifications()
                ->latest()
                ->take(20)
                ->get()
                ->map(fn($n) => [
                    'id' => $n->id,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                    'data' => array_merge($n->data ?? [], [
                        'type_label' => $this->notificationTypeLabel($n->data['type'] ?? null),
                    ]),
                ]);

            return [
                'unread_count' => $user->unreadNotifications()->count(),
                'latest' => $latest,
            ];
        });
    }


    private function notificationTypeLabel(?string $type): string
    {
        return match ($type) {
            'citizen_report.status_changed' => 'Citizen report',
            default => 'General',
        };
    }
}
