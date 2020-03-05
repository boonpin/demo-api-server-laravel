<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\JWTGuard;

abstract class BaseController extends Controller
{
    use ResponseHelperTraits;

    public function __construct($requireToken = true)
    {
        $this->middleware("session");
        if ($requireToken) {
            $this->middleware("auth.jwt", ['except' => [
                'doLogin',
                'checkLogin',
                'logout'
            ]]);
        }
    }

    protected function getUserId()
    {
        return data_get($this->getUser(), "id");
    }

    public function getUser()
    {
        return $this->guard()->getPayload()->get('usr');
    }

    public function guard(): JWTGuard
    {
        $guard = \Auth::guard();
        if ($guard instanceof JWTGuard) {
            try {
                $grd = $guard->getPayload()->get('grd');
                if (!empty($grd)) {
                    return \Auth::guard($grd);
                }
            } catch (\Exception $e) {
            }
        }
        return $guard;
    }
}
