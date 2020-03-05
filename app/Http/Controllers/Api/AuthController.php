<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PermissionDeniedException;
use App\Models\User;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Monolog\Logger;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTGuard;

class AuthController extends BaseController
{
    use ThrottlesLogins;

    public function doLogin()
    {
        $guard = \Auth::guard();

        $username = request('username');
        $password = request('password');

        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException("Invalid argument!");
        }

        if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts(request())) {

            $this->fireLockoutEvent(request());
            $seconds = $this->limiter()->availableIn(
                $this->throttleKey(request())
            );

            throw new PermissionDeniedException(Lang::get('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ], Response::HTTP_TOO_MANY_REQUESTS));
        }


        if ($guard instanceof JWTGuard) {
            $customClaims = ['sid' => request()->session()->getId(), 'grd' => "jwt"];

            $attempt = $guard->claims($customClaims)->attempt([
                "username" => $username,
                "password" => $password,
            ]);
        }

        if ($attempt) {
            if ($guard->user()->is_disabled) {
                throw new PermissionDeniedException("User already disabled!");
            }
            $this->clearLoginAttempts(request());
            $user = $guard->user();
            session()->put('user_id', $user->id);

            $user->refresh_token = \Str::random(32);
            $user->refresh_token_expiry = time() + (config('session.lifetime', 1) * 60);
            $user->save();

            return $this->respondWithToken($attempt, $user->refresh_token);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts(request());

        $this->logger()->warning(sprintf("login failed on user [%s] from [%s]",
            $username, request()->getClientIp()
        ));

        throw new PermissionDeniedException("Please provide valid credential!");
    }

    public function logout()
    {
        try {
            $this->guard()->logout();
        } catch (TokenExpiredException $e) {

        } finally {
            request()->session()->invalidate();
        }
        return $this->toActionResponse("logged out");
    }

    public function checkLogin()
    {
        try {
            $user = $this->guard()->getPayload()->get('usr');
            $ok = !!empty($user);
        } catch (\Exception $e) {
            $ok = false;
        }
        return $this->toActionResponse(null, $ok, null);
    }

    public function refreshToken()
    {
        $user = User::query()->findOrFail(data_get($this->guard()->getPayload()->get('usr'), "id"));

        if ($user->refresh_token === request('refresh_token')) {
            if ($user->refresh_token_expiry > time()) {
                $user->refresh_token_expiry = time() + (config('session.lifetime', 1) * 60);
                $user->save();
                return $this->respondWithToken($this->guard()->refresh(), $user->refresh_token);
            } else {
                throw new PermissionDeniedException("refresh token already expired!");
            }
        } else {
            throw new PermissionDeniedException("invalid refresh token!");
        }
    }

    public function getLoggedUser()
    {
        $user = User::query()->findOrFail(data_get($this->guard()->getPayload()->get('usr'), "id"));
        return response()->json($user);
    }

    protected function respondWithToken($token, $refreshToken = null)
    {
        $res = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => time() + (config('session.lifetime', 1) * 60)
        ];

        if (!empty($refreshToken)) {
            $res['refresh_token'] = $refreshToken;
        }

        return response()->json($res);
    }

    protected function logger()
    {
        return \Log::channel("auth");
    }

    public function username()
    {
        return "username";
    }
}
