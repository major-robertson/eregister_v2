<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/portal.php'));
            Route::middleware('web')->group(base_path('routes/forms.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'business.selected' => \App\Domains\Portal\Http\Middleware\EnsureBusinessSelected::class,
            'business.current' => \App\Domains\Portal\Http\Middleware\ResolveCurrentBusiness::class,
            'business.complete' => \App\Domains\Portal\Http\Middleware\EnsureBusinessProfileComplete::class,
            'application.access' => \App\Domains\Portal\Http\Middleware\EnsureHasAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
