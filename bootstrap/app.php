<?php

use App\Http\Middleware\EnsureUserIsAdmin;
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
        $middleware->alias(['admin' => EnsureUserIsAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua response API (termasuk error) tetap {status, message, data|errors}
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak valid',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated, token tidak valid atau tidak dikirim',
                    'errors' => (object) [],
                ], 401);
            }
        });

        // 403/404/405/409 dll ikut format yang sama supaya parser Flutter seragam
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            $status = $e->getStatusCode();

            if (! $request->is('api/*') || $status === 422) {
                return null;
            }

            $message = match ($status) {
                404 => 'Data tidak ditemukan',
                405 => 'Metode HTTP tidak diizinkan untuk endpoint ini',
                default => $e->getMessage() ?: 'Permintaan ditolak',
            };

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'errors' => (object) [],
            ], $status);
        });
    })->create();
