<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        $middleware->redirectTo(
            guests: fn () => route('login'),
            users: fn () => route('dashboard'),
        );

        // Inertia: adiciona o middleware ao grupo 'web' (não impacta rotas até que sejam Inertia)
        // Evite autoload do nosso middleware antes da lib estar instalada.
        if (class_exists(\Inertia\Middleware::class) && method_exists($middleware, 'appendToGroup')) {
            $middleware->appendToGroup('web', \App\Http\Middleware\HandleInertiaRequests::class);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
