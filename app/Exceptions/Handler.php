<?php

namespace App\Exceptions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (
            \PHPOpenSourceSaver\JWTAuth\Exceptions\SecretMissingException $e
        ) {
            \Illuminate\Support\Facades\Log::error(
                'Intermittent JWT SecretMissingException',
                [
                    'url' => request()->path(),
                    'process_id' => getmypid(),
                    'secret_present' => !empty(config('jwt.secret')),
                    'config_cached' => app()->configurationIsCached(),
                    'algorithm' => config('jwt.algo'),
                    'base_path' => base_path(),
                ]
            );
        });
    }

    protected function unauthenticated(
    $request,
    AuthenticationException $exception
) {
    if ($request->is('api/*') || $request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated. JWT token missing, invalid, or expired.',
        ], 401);
    }

    return redirect()->guest(
        $exception->redirectTo() ?? route('login')
    );
}
}
