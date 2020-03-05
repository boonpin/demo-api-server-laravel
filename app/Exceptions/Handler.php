<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        if (\Str::startsWith(request()->path(), "api/")) {
            $code = $exception->getCode();
            $message = $exception->getMessage();

            if ($exception instanceof NotFoundHttpException ||
                $exception instanceof ModelNotFoundException) {

                if ($exception instanceof ModelNotFoundException) {
                    $message = "No query results.";
                } else {
                    $message = empty($exception->getMessage()) ? "Resource not found!" : $exception->getMessage();
                }
                $code = 404;
            }

            if ($code < 200 || $code >= 600) {
                $code = 500;
            }

            $res = [
                "message" => $message,
                "code" => $code
            ];

            if (config('app.debug')) {
                $res["debug"] = [
                    'code' => $exception->getCode(),
                    'class' => get_class($exception),
                    "line" => $exception->getLine(),
                    "file" => $exception->getFile(),
                    "trace" => $exception->getTrace(),
                ];
            }
            return response()->json($res, $code);
        } else if (\Str::startsWith(request()->path(), "file/")) {
            if ($exception instanceof NotFoundHttpException ||
                $exception instanceof ModelNotFoundException) {
                if ($exception instanceof ModelNotFoundException) {
                    $message = "No query results.";
                } else {
                    $message = empty($exception->getMessage()) ? "Resource not found!" : $exception->getMessage();
                }
                $exception = new NotFoundHttpException($message);
            }
        }

        return parent::render($request, $exception);
    }
}
