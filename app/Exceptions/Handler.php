<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return json_response(422, current($exception->errors())[0], $exception->errors());
        } elseif ($exception instanceof AuthorizationException) {
            return json_response(403, '未授权');
        } elseif ($exception instanceof AuthenticationException) {
            return json_response(401, '请登录');
        } elseif ($exception instanceof UnauthorizedException) {
            return json_response(403, $exception->getMessage());
        } elseif ($exception instanceof ThrottleRequestsException) {
            return json_response(429, '操作频繁，请稍后重试');
        }
        // else {
        //     return json_response(400, $exception->getMessage()); 
        // }

        return parent::render($request, $exception);
    }
}
