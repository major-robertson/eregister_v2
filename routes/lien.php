<?php

use App\Domains\Lien\Http\Controllers\FilingDownloadController;
use App\Domains\Lien\Http\Controllers\StripeWebhookController;
use App\Domains\Lien\Livewire\FilingCheckout;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Livewire\LienOnboarding;
use App\Domains\Lien\Livewire\ProjectForm;
use App\Domains\Lien\Livewire\ProjectList;
use App\Domains\Lien\Livewire\ProjectShow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Lien Routes
|--------------------------------------------------------------------------
*/

// Webhook route (no auth required)
Route::post('/webhooks/lien-stripe', [StripeWebhookController::class, 'handle'])
    ->name('lien.webhooks.stripe');

// Lien onboarding (must be before the lien.onboarding middleware group)
Route::middleware(['auth', 'business.current', 'business.complete'])
    ->get('/portal/liens/onboarding', LienOnboarding::class)
    ->name('lien.onboarding');

// Authenticated lien routes (with lien onboarding check)
Route::middleware(['auth', 'business.current', 'business.complete', 'lien.onboarding'])
    ->prefix('/portal/liens')
    ->group(function (): void {
        // Project routes
        Route::get('/', ProjectList::class)->name('lien.projects.index');
        Route::get('/projects/create', ProjectForm::class)->name('lien.projects.create');
        Route::get('/projects/{project}', ProjectShow::class)->name('lien.projects.show');
        Route::get('/projects/{project}/edit', ProjectForm::class)->name('lien.projects.edit');

        // Filing routes
        Route::get('/projects/{project}/filings/{deadline}/start', FilingWizard::class)
            ->name('lien.filings.start');
        Route::get('/filings/{filing}', FilingShow::class)->name('lien.filings.show');
        Route::get('/filings/{filing}/checkout', FilingCheckout::class)->name('lien.filings.checkout');
        Route::get('/filings/{filing}/download', [FilingDownloadController::class, 'download'])
            ->name('lien.filings.download');
    });
