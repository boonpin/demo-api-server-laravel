<?php

namespace App\Http\Controllers\Api;

trait ResponseHelperTraits
{
    public function toListResponse($data, $total = null, $debug = null)
    {
        if ($total === null) {
            $total = count($data);
        }
        $res = ["items" => $data, "total" => $total];
        if (config('app.debug')) {
            $res['debug'] = $debug;
        }
        return $res;
    }

    public function toActionResponse($message, $success = true, $refId = null, $debug = null)
    {
        $res = ["message" => $message, "success" => $success, "ref_id" => $refId];
        if (config('app.debug')) {
            $res['debug'] = $debug;
        }
        return $res;
    }

    public function toListActionResponse(array $results, $debug = null)
    {
        $res = ["results" => $results];
        if (config('app.debug')) {
            $res['debug'] = $debug;
        }
        return $res;
    }
}
