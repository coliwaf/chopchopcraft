<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add Inertia middleware to every web request — shares auth, cart, flash
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Alias for convenience in route files
        $middleware->alias([
            'auth'  => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render Inertia-friendly error pages (403, 404, 500)
        $exceptions->render(function (Response $response) {
            if (
                in_array($response->getStatusCode(), [403, 404, 500, 503])
                && request()->header('X-Inertia')
            ) {
                return back()->with([
                    'error' => $response->getStatusCode() === 404
                        ? 'Page not found.'
                        : 'Something went wrong.',
                ]);
            }
            return $response;
        });
    })->create();
