<?php

use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Livewire\StateSelector;
use App\Domains\SalesTax\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sales Tax Workspace Routes
|--------------------------------------------------------------------------
|
| The Sales Tax workspace owns the registration wizard and detail pages
| for `sales_tax_permit` form-runner applications. The generic forms
| runner Livewire components (StateSelector, MultiStateFormRunner) are
| reused here; the workspace just owns the URL.
|
*/

Route::middleware(['auth', 'business.current', 'business.complete'])
    ->prefix('/portal/sales-tax')
    ->group(function (): void {
        Route::get('/', Dashboard::class)
            ->name('sales-tax.dashboard');

        // Start a new sales tax registration. The form_type is baked in
        // via Route::defaults() so StateSelector::mount(string $formType)
        // receives it without a URL segment.
        Route::get('/registrations/start', StateSelector::class)
            ->defaults('formType', 'sales_tax_permit')
            ->name('sales-tax.registrations.start');

        // Form runner detail page. application.access middleware enforces
        // payment / subscription gating; MultiStateFormRunner::mount()
        // asserts the current route name matches this workspace's
        // applicationRouteName so an LLC application can't be loaded here.
        Route::get('/registrations/{application}', MultiStateFormRunner::class)
            ->middleware('application.access')
            ->name('sales-tax.registrations.show');
    });
