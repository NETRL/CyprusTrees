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

    
    public function __construct(private Message $message, private Navbar $navbar, private Auth $auth){}
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
            // 'auth' => [
            //     'user' => fn() => $request->user()
            //         ? $request->user()
            //         : null,
            // ],
            
            'auth'    => $this->auth->toArray(),
            'message' => $this->message->getMessage(),
            'navbar'  => $this->navbar->getNavbar(),

            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
            ],
        ];
    }
}
