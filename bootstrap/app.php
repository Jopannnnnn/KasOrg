<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureLocalPreview;
use App\Http\Middleware\EnsureRole;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => EnsureAccountIsActive::class,
            'preview.local' => EnsureLocalPreview::class,
            'role' => EnsureRole::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request, $exception) => $request->is('api', 'api/*') || $request->expectsJson());
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api', 'api/*')) {
                return null;
            }
            if ($exception instanceof ValidationException) {
                return ApiResponse::error('Parameter tidak valid.', 422, $exception->errors());
            }
            if ($exception instanceof AuthenticationException) {
                return ApiResponse::error('Silakan login terlebih dahulu.', 401);
            }
            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $message = match ($status) {
                403 => 'Anda tidak memiliki izin untuk mengakses data ini.',
                404 => 'Endpoint tidak ditemukan.',
                405 => 'Metode HTTP tidak diizinkan.',
                419 => 'Token CSRF tidak valid atau sesi telah berakhir.',
                429 => 'Terlalu banyak permintaan. Silakan coba lagi.',
                default => 'Terjadi kesalahan pada server.',
            };
            $response = ApiResponse::error($message, $status);
            if ($exception instanceof HttpExceptionInterface) {
                foreach ($exception->getHeaders() as $name => $value) {
                    $response->header($name, $value);
                }
            }

            return $response;
        });
    })->create();
