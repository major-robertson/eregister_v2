<?php

use App\Http\Controllers\Demo\Mdcps\MdcpsDemoController;
use App\Http\Middleware\EnsureMdcpsDemoAuth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MDCPS Demo Sandbox
|--------------------------------------------------------------------------
|
| Isolated, front-end-only proof-of-concept for the Miami-Dade school
| website proposal. No database, models, or shared eRegister state. The
| CMS area is gated by a simple session flag (no real auth system); all
| content persistence happens client-side via localStorage.
|
*/

Route::prefix('mdcps-demo')->name('mdcps-demo.')->group(function (): void {
    Route::get('/', [MdcpsDemoController::class, 'home'])->name('home');
    Route::get('/calendar', [MdcpsDemoController::class, 'publicCalendar'])->name('calendar');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        // Login (public)
        Route::get('/login', [MdcpsDemoController::class, 'showLogin'])->name('login');
        Route::post('/login', [MdcpsDemoController::class, 'login'])->name('login.attempt');
        Route::post('/logout', [MdcpsDemoController::class, 'logout'])->name('logout');

        // CMS (session-gated)
        Route::middleware(EnsureMdcpsDemoAuth::class)->group(function (): void {
            Route::get('/', [MdcpsDemoController::class, 'admin'])->name('dashboard');
            Route::get('/calendar', [MdcpsDemoController::class, 'calendar'])->name('calendar');
            Route::get('/alert', [MdcpsDemoController::class, 'alert'])->name('alert');
            Route::get('/media', [MdcpsDemoController::class, 'media'])->name('media');
        });
    });
});
