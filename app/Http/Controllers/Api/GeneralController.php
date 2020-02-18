<?php

namespace App\Http\Controllers\Api;

class GeneralController extends BaseController
{
    public function __construct()
    {
        parent::__construct(false);
    }

    public function getInfo()
    {
        return response()->json([
            'version' => config('app_com.version'),
            'build_number' => config('app_com.build_number'),
            'powered_company' => config('app_com.powered_company')
        ]);
    }
}
