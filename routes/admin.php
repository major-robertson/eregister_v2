<?php

use App\Domains\Admin\Livewire\BusinessesList;
use App\Domains\Admin\Livewire\BusinessOverview;
use App\Domains\Admin\Livewire\FormationStats;
use App\Domains\Admin\Livewire\LienStats;
use App\Domains\Admin\Livewire\MarketingStats;
use App\Domains\Admin\Livewire\RolesBoard;
use App\Domains\Admin\Livewire\SalesTaxStats;
use App\Domains\Admin\Livewire\StatsBoard;
use App\Domains\Admin\Livewire\UserOverview;
use App\Domains\Admin\Livewire\UsersList;
use App\Domains\Forms\Admin\Livewire\FormationApplicationStateDetail;
use App\Domains\Forms\Admin\Livewire\FormationsBoard;
use App\Domains\Forms\Admin\Livewire\FormationsBoardAll;
use App\Domains\Forms\Admin\Livewire\SalesTaxApplicationStateDetail;
use App\Domains\Forms\Admin\Livewire\SalesTaxBoard;
use App\Domains\Forms\Admin\Livewire\SalesTaxBoardAll;
use App\Domains\Lien\Admin\Http\Controllers\DemandLetterController;
use App\Domains\Lien\Admin\Http\Controllers\SignedDocumentController;
use App\Domains\Lien\Admin\Livewire\LienBoard;
use App\Domains\Lien\Admin\Livewire\LienBoardAll;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Admin\Livewire\LienRulesOverview;
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
            Route::get('/board/all', LienBoardAll::class)->name('board-all');
            Route::get('/lien-rules-overview', LienRulesOverview::class)->name('lien-rules-overview');
            Route::get('/{publicId}/demand-letters', [DemandLetterController::class, 'downloadAll'])->name('demand-letters');
            Route::get('/{publicId}/demand-letter/{party}', [DemandLetterController::class, 'download'])->name('demand-letter');
            // Signed e-sign document download (kept before the {publicId} catch-all).
            Route::get('/esign/documents/{publicId}/download', [SignedDocumentController::class, 'download'])->name('esign.documents.download');
            Route::get('/{lienFiling:public_id}', LienFilingDetail::class)->name('show')->withTrashed();
        });

    // Sales Tax admin routes - require tax.view permission. The /states/
    // nesting on the detail page leaves room for future /applications/,
    // /reports/, /settings/ namespaces under the same workspace.
    Route::prefix('sales-tax')
        ->name('admin.sales-tax.')
        ->middleware(['permission:tax.view'])
        ->group(function () {
            Route::get('/board', SalesTaxBoard::class)->name('board');
            Route::get('/board/all', SalesTaxBoardAll::class)->name('board-all');
            Route::get('/states/{formApplicationState}', SalesTaxApplicationStateDetail::class)
                ->name('states.show');
        });

    // Formations admin routes - require llc.view permission. Mirrors the
    // sales-tax board; the /states/ detail nesting leaves room for future
    // /applications/, /reports/ namespaces under the same workspace.
    Route::prefix('formations')
        ->name('admin.formations.')
        ->middleware(['permission:llc.view'])
        ->group(function () {
            Route::get('/board', FormationsBoard::class)->name('board');
            Route::get('/board/all', FormationsBoardAll::class)->name('board-all');
            Route::get('/states/{formApplicationState}', FormationApplicationStateDetail::class)
                ->name('states.show');
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

    // Sales tax stats - admin role only
    Route::get('/sales-tax-stats', SalesTaxStats::class)
        ->name('admin.sales-tax-stats')
        ->middleware('role:admin');

    // Formation stats - admin role only
    Route::get('/formation-stats', FormationStats::class)
        ->name('admin.formation-stats')
        ->middleware('role:admin');
});
