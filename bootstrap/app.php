<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/portal.php'));
            Route::middleware('web')->group(base_path('routes/forms.php'));
            Route::middleware('web')->group(base_path('routes/lien.php'));
            Route::middleware('web')->group(base_path('routes/admin.php'));

            // API routes (no session, no CSRF)
            Route::prefix('api')->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\TrackSignupAttribution::class,
        ]);

        // Exclude webhooks from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
            'webhooks/postgrid',
        ]);

        $middleware->alias([
            'business.selected' => \App\Domains\Portal\Http\Middleware\EnsureBusinessSelected::class,
            'business.current' => \App\Domains\Portal\Http\Middleware\ResolveCurrentBusiness::class,
            'business.complete' => \App\Domains\Portal\Http\Middleware\EnsureBusinessProfileComplete::class,
            'application.access' => \App\Domains\Portal\Http\Middleware\EnsureHasAccess::class,
            'lien.onboarding' => \App\Domains\Lien\Http\Middleware\EnsureLienOnboardingComplete::class,
            // API middleware
            'api.key' => \App\Http\Middleware\ValidateApiKey::class,
            // Spatie Permission middleware
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
