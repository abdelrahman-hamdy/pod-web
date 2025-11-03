<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        // App\Providers\BroadcastServiceProvider::class,
        \App\Providers\Filament\AdminPanelProvider::class,
    ], true)
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
        // Log all exceptions for debugging
        $exceptions->report(function (\Throwable $e) {
            \Log::error('Exception caught', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        });

        // Only show custom error pages in production
        if (! app()->environment('local') && ! app()->environment('testing')) {
            $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
                return response()->view('errors.404', [], 404);
            });

            $exceptions->render(function (\Throwable $e, $request) {
                // In debug mode, show actual error instead of 500 page
                if (config('app.debug')) {
                    return null; // Let Laravel show the error page
                }
                return response()->view('errors.500', [], 500);
            });
        }
    })->create();
