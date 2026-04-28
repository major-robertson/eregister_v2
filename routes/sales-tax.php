<?php

use App\Domains\SalesTax\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sales Tax Workspace Routes
|--------------------------------------------------------------------------
|
| Single-page workspace today: a dashboard listing the current business's
| sales tax registrations and linking out to the existing forms runner
| (`forms.start`) for new registrations. Multi-page nav (Filings, Nexus,
| Documents) will be added when those features are built.
|
*/

Route::middleware(['auth', 'business.current', 'business.complete'])
    ->prefix('/portal/sales-tax')
    ->group(function (): void {
        Route::get('/', Dashboard::class)->name('sales-tax.dashboard');
    });
