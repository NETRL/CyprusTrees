<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {

        $user = $request->user();

        if (
            config('twofactor.enabled') &&
            $user &&
            $user->hasTwoFactorEnabled() &&
            ! $request->session()->get('2fa_passed')
        ) {

            return redirect()->route('two-factor.challenge', [], 303);
        }

        return $next($request);
    }
}
