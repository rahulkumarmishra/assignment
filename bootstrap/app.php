<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':30,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        });

        $exceptions->render(function (ModelNotFoundException | NotFoundHttpException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Resource not found.',
            ], 404);
        });

        $exceptions->render(function (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() ?: 'Server error',
            ], 500);
        });
    })
    ->create();
