<?php

namespace App\Http\Middleware;

use App\Entities\Navbar;
use App\Entities\Auth;
use App\Entities\Message;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';


    public function __construct() {}
    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'phpVersion'     => PHP_VERSION,
            'laravelVersion' => \Illuminate\Foundation\Application::VERSION,

            'auth'      => fn() => (new Auth($request))->toArray(),
            'navbar'    => fn() => app(Navbar::class)->getNavbar(),

            'message'   => fn() => app(Message::class)->getMessage(),
            'flash'     => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
                'event'   => fn() => $request->session()->get('flash_event'),
            ],
        ];
    }
}
