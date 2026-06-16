<?php

use App\Domains\Formations\Livewire\Dashboard;
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
    });
