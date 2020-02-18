<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\JWTGuard;

class AuthenticateJwt
{
    public function handle($request, Closure $next)
    {
        $guard = \Auth::guard();
        if ($guard instanceof JWTGuard) {
            $user = $guard->getPayload()->get('usr');
            session()->put('__user', $user);
        } else {
            throw new \Exception("invalid jwt guard!");
        }

        return $next($request);
    }
}
