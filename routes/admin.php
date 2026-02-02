<?php

use App\Domains\Admin\Livewire\BusinessesList;
use App\Domains\Admin\Livewire\BusinessOverview;
use App\Domains\Admin\Livewire\LienStats;
use App\Domains\Admin\Livewire\MarketingStats;
use App\Domains\Admin\Livewire\RolesBoard;
use App\Domains\Admin\Livewire\StatsBoard;
use App\Domains\Admin\Livewire\UserOverview;
use App\Domains\Admin\Livewire\UsersList;
use App\Domains\Lien\Admin\Livewire\LienBoard;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes for the internal admin dashboard. Protected by authentication
| and Spatie permission middleware.
|
*/

Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Admin home - redirect to liens board for now
    Route::get('/', fn () => redirect()->route('admin.liens.board'))->name('admin.home');

    // Lien admin routes - require lien.view permission
    Route::prefix('liens')
        ->name('admin.liens.')
        ->middleware(['permission:lien.view'])
        ->group(function () {
            Route::get('/board', LienBoard::class)->name('board');
            Route::get('/{lienFiling:public_id}', LienFilingDetail::class)->name('show');
        });

    // Users management - admin role only
    Route::prefix('users')
        ->name('admin.users.')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', UsersList::class)->name('index');
            Route::get('/{user}', UserOverview::class)->name('show');
        });

    // Businesses management - admin role only
    Route::prefix('businesses')
        ->name('admin.businesses.')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', BusinessesList::class)->name('index');
            Route::get('/{business}', BusinessOverview::class)->name('show');
        });

    // Roles management - admin role only
    Route::get('/roles', RolesBoard::class)
        ->name('admin.roles')
        ->middleware('role:admin');

    // Stats dashboard - admin role only
    Route::get('/stats', StatsBoard::class)
        ->name('admin.stats')
        ->middleware('role:admin');

    // Marketing stats - admin role only
    Route::get('/marketing', MarketingStats::class)
        ->name('admin.marketing')
        ->middleware('role:admin');

    // Lien stats - admin role only
    Route::get('/lien-stats', LienStats::class)
        ->name('admin.lien-stats')
        ->middleware('role:admin');
});
