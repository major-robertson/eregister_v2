<?php

use App\Domains\Formations\Http\Controllers\FormationPaymentController;
use App\Domains\Formations\Livewire\Dashboard;
use App\Domains\Formations\Livewire\FormationCheckout;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Livewire\StateSelector;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Formations Workspace Routes
|--------------------------------------------------------------------------
|
| The Formations workspace owns the wizard and detail pages for every
| formation-shaped form_type (LLC today; Corporation, DBA, Nonprofit,
| Sole Proprietorship, etc. when their definitions ship).
|
| The {formType} route segment is constrained by the workspace's
| `form_types` array in config/workspaces.php — adding a new formation
| type only requires updating the config and adding its definition file.
|
*/

Route::middleware(['auth', 'business.current', 'business.complete'])
    ->prefix('/portal/formations')
    ->group(function (): void {
        Route::get('/', Dashboard::class)
            ->name('formations.dashboard');

        // Start a new formation. The {formType} URL segment hydrates
        // StateSelector::mount(string $formType) directly via Livewire's
        // route parameter binding. The whereIn() constraint is sourced
        // from config so adding e.g. 'corporation' is config-only.
        Route::get('/start/{formType}', StateSelector::class)
            ->whereIn('formType', config('workspaces.formations.form_types', []))
            ->name('formations.start');

        // Form runner detail page. application.access middleware enforces
        // payment / subscription gating; MultiStateFormRunner::mount()
        // asserts the current route name matches this workspace's
        // applicationRouteName so a sales-tax application can't be
        // loaded here.
        Route::get('/applications/{application}', MultiStateFormRunner::class)
            ->middleware('application.access')
            ->name('formations.show');

        // Checkout = $299/yr membership + one-time state filing fee, charged
        // via a hosted subscription-mode Checkout Session; payment auto-submits
        // + locks the application.
        Route::get('/applications/{application}/checkout', FormationCheckout::class)
            ->name('formations.checkout');

        Route::get('/applications/{application}/payment-confirmation', [FormationPaymentController::class, 'confirmation'])
            ->name('formations.payment-confirmation');
    });

// API route for payment status polling (processing page).
Route::middleware(['auth:sanctum'])
    ->get('/api/formations/applications/{application}/payment-status', [FormationPaymentController::class, 'status'])
    ->name('formations.api.payment-status');
