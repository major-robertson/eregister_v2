<?php

use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Livewire\StateSelector;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Form Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'business.current', 'business.complete'])
    ->prefix('/portal/forms')
    ->group(function (): void {
        // State Selection for new application
        Route::get('/{formType}/start', StateSelector::class)
            ->name('forms.start');

        // Form Runner for existing application (requires access - payment or subscription)
        Route::get('/applications/{application}', MultiStateFormRunner::class)
            ->middleware('application.access')
            ->name('forms.application');
    });
