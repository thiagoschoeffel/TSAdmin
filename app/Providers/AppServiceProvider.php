<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar renderables para exceções HTTP comuns, retornando páginas Inertia
        $this->registerInertiaExceptionHandlers();
    }

    protected function registerInertiaExceptionHandlers(): void
    {
        // Só registra se a classe Inertia existir
        if (!class_exists(Inertia::class)) {
            return;
        }

        // Handler genérico para exceções http
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class; // garantir carregamento

        // Renderable closures para códigos comuns
        app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->header('X-Inertia') || str_contains($request->header('Accept', ''), 'text/html')) {
                // Mesclar props globais do Inertia manualmente
                $globals = app(\App\Http\Middleware\HandleInertiaRequests::class)->share($request);
                return Inertia::render('Errors/404', array_merge($globals, ['url' => $request->getRequestUri()]))->toResponse($request)->setStatusCode(404);
            }
        });

        // Rota Model Binding falhou (ex: Client não existe) -> retornar Errors/404 via Inertia
        app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->header('X-Inertia') || str_contains($request->header('Accept', ''), 'text/html')) {
                return Inertia::render('Errors/404', ['url' => $request->getRequestUri()])->toResponse($request)->setStatusCode(404);
            }
        });

        // Tratar HttpException com status 403 (abort(403) gera HttpExceptionInterface)
        app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, Request $request) {
            if ($request->header('X-Inertia') || str_contains($request->header('Accept', ''), 'text/html')) {
                if ($e->getStatusCode() === 403) {
                    $globals = app(\App\Http\Middleware\HandleInertiaRequests::class)->share($request);
                    return Inertia::render('Errors/403', array_merge($globals, ['url' => $request->getRequestUri()]))->toResponse($request)->setStatusCode(403);
                }
            }
        });

        // Tratar Unauthenticated separadamente: redirecionar ao login (evita transformar em 500)
        app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->renderable(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            // Para requisições Inertia / HTML, redirecionar ao formulário de login
            if ($request->header('X-Inertia') || str_contains($request->header('Accept', ''), 'text/html')) {
                return redirect()->guest(route('login'));
            }
        });

        app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->renderable(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            if ($request->header('X-Inertia') || str_contains($request->header('Accept', ''), 'text/html')) {
                $globals = app(\App\Http\Middleware\HandleInertiaRequests::class)->share($request);
                return Inertia::render('Errors/403', array_merge($globals, ['url' => $request->getRequestUri()]))->toResponse($request)->setStatusCode(403);
            }
        });

        app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->renderable(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if ($request->header('X-Inertia') || str_contains($request->header('Accept', ''), 'text/html')) {
                $globals = app(\App\Http\Middleware\HandleInertiaRequests::class)->share($request);
                return Inertia::render('Errors/419', array_merge($globals, ['url' => $request->getRequestUri()]))->toResponse($request)->setStatusCode(419);
            }
        });

        app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->renderable(function (Throwable $e, Request $request) {
            // Se a exceção for específica, tratá-la aqui para evitar que o handler genérico a transforme em 500
            if ($request->header('X-Inertia') || str_contains($request->header('Accept', ''), 'text/html')) {
                // Authentication -> redirecionar para login
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return redirect()->guest(route('login'));
                }

                // Model not found / NotFoundHttpException -> mostrar 404 Inertia
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException || $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return Inertia::render('Errors/404', ['url' => $request->getRequestUri()])->toResponse($request)->setStatusCode(404);
                }

                // Access denied -> 403 Inertia
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                    return Inertia::render('Errors/403', ['url' => $request->getRequestUri()])->toResponse($request)->setStatusCode(403);
                }

                // Fallback: 500 genérico
                $globals = app(\App\Http\Middleware\HandleInertiaRequests::class)->share($request);
                return Inertia::render('Errors/500', array_merge($globals, ['message' => $e->getMessage(), 'url' => $request->getRequestUri()]))->toResponse($request)->setStatusCode(500);
            }
        });
    }
}
