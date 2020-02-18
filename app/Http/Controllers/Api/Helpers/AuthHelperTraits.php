<?php

namespace App\Http\Controllers\Api\Helpers;

use App\Exceptions\PermissionDeniedException;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTGuard;

trait AuthHelperTraits
{
    use ThrottlesLogins;

    protected function doLogin(Request $request, $guardName, $options = [])
    {
        $guard = \Auth::guard($guardName);

        $usernameKey = data_get($options, "username", "username");
        $passwordKey = data_get($options, "password", "password");
        $rememberKey = data_get($options, "remember", "remember");

        if (empty($request->get($usernameKey)) || empty($request->get($passwordKey))) {
            throw new \InvalidArgumentException("Invalid argument!");
        }

        if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {

            $this->fireLockoutEvent($request);
            $seconds = $this->limiter()->availableIn(
                $this->throttleKey($request)
            );

            throw new PermissionDeniedException(Lang::get('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ], Response::HTTP_TOO_MANY_REQUESTS));
        }

        if ($guard instanceof JWTGuard) {
            $customClaims = ['sid' => $request->session()->getId(), 'grd' => $guardName];
            $attempt = $guard->claims($customClaims)->attempt($request->only($usernameKey, $passwordKey));
        } else {
            $attempt = $guard->attempt($request->only($usernameKey, $passwordKey), $request->filled($rememberKey));
        }

        if ($attempt) {
            if (!$this->isEnable($guardName, $usernameKey, $request->get($usernameKey))) {
                throw new PermissionDeniedException("Invalid login!");
            }
            $this->clearLoginAttempts($request);

            $user = $guard->user();
            $request->session()->put('user_id', $user->id);
            $request->session()->put('__guard', $guardName);

            if ($guard instanceof JWTGuard) {
                return $this->respondWithToken($attempt);
            } else {
                if ($this->load()) {
                    $user->load($this->load());
                }
                $guard->setUser($user);

                $this->logger()->info(sprintf("[%s] login success for guard [%s] from [%s]",
                    data_get($user, $usernameKey),
                    $guardName,
                    $request->getClientIp()
                ));
                return response()->json($user);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        $this->logger()->warning(sprintf("login failed on user [%s] on guard [%s] from [%s]",
            $request->get($usernameKey), $guardName, $request->getClientIp()
        ));

        throw new PermissionDeniedException("Please provide valid credential!");
    }

    public function logout(Request $request)
    {
        try {
            $this->guard()->logout();
        } catch (TokenExpiredException $e) {

        }
        $request->session()->invalidate();
        return $this->toActionResponse("logged out");
    }

    public function checkLogin()
    {
        $guard = $this->guard();
        if ($guard instanceof JWTGuard) {
            try {
                $guard->getUser();
                $user = $guard->getPayload()->get('usr');
                $ok = data_get($user, 'type') === $this->getSessionGuard();
            } catch (\Exception $e) {
                $ok = false;
            }
        } else {
            $ok = $guard->check();
        }

        return $this->toActionResponse(null, $ok, null, [
            "guard" => $this->getSessionGuard()
        ]);
    }

    public function refreshToken()
    {
        $guard = $this->guard();
        $token = null;
        if ($guard instanceof JWTGuard) {
            $token = $guard->refresh();
        } else {
            $t = \Str::random(60);
            $token = hash('sha256', $t);
            $guard->user()->forceFill([
                'api_token' => $t,
            ])->save();
        }
        return $this->respondWithToken($token);
    }

    public function getLoggedUser()
    {
        $guard = $this->guard();
        if ($guard instanceof JWTGuard) {
            $user = $guard->getPayload()->get('usr');
        } else {
            $user = $guard->user();
        }
        return response()->json($user);
    }

    protected function respondWithToken($token)
    {
        $guard = $this->guard();
        if ($guard instanceof JWTGuard) {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $guard->factory()->getTTL() * 60
            ]);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer'
        ]);
    }

    protected function getSessionGuard()
    {
        return session()->get('__guard', 'admin');
    }

    // ------------------------------------------------------------

    protected function logger()
    {
        return \Log::channel("auth");
    }

    protected function load()
    {
        return [];
    }

    protected function isEnable($guard, $k, $v)
    {
        return true;
    }
}
