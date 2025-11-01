<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Chatify routes are loaded by the ChatifyServiceProvider
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'client' => \App\Http\Middleware\ClientMiddleware::class,
        ]);

        // Apply profile completion check to all web routes
        $middleware->web(append: [
            \App\Http\Middleware\EnsureProfileCompleted::class,
        ]);

        // Apply security headers to all web routes
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        // Don't encrypt sidebar state cookie (set by JavaScript)
        $middleware->encryptCookies(except: [
            'sidebar_collapsed',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // In production, don't expose error details to users
        if (! $this->environment('local')) {
            $exceptions->dontReport([
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Auth\Access\AuthorizationException::class,
                \Illuminate\Validation\ValidationException::class,
            ]);
        }

        // Render friendly error pages
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => config('app.debug') ? $e->getMessage() : 'Server Error',
                    'error' => config('app.debug') ? $e->getTraceAsString() : null,
                ], 500);
            }
        });
    })->create();
