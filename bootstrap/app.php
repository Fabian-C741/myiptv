<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware de API: verifica status del user en cada request autenticado
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\CheckUserStatus::class,
        ]);

        // Alias para usar en rutas con ->middleware('alias')
        $middleware->alias([
            'admin.role'      => \App\Http\Middleware\AdminRole::class,
            'parental'        => \App\Http\Middleware\ParentalControl::class,
            'check.status'    => \App\Http\Middleware\CheckUserStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Forzar respuesta JSON para todas las excepciones en rutas de API
        // Esto evita redirecciones al login (302) y devuelve 401 en su lugar
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });
    })->create();
