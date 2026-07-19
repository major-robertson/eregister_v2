<?php

use App\Domains\Lien\Http\Controllers\FilingDownloadController;
use App\Domains\Lien\Http\Controllers\FilingPaymentController;
use App\Domains\Lien\Livewire\Dashboard;
use App\Domains\Lien\Livewire\DeadlineList;
use App\Domains\Lien\Livewire\FilingCheckout;
use App\Domains\Lien\Livewire\FilingList;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Livewire\LienOnboarding;
use App\Domains\Lien\Livewire\LienProfileComplete;
use App\Domains\Lien\Livewire\ProjectForm;
use App\Domains\Lien\Livewire\ProjectList;
use App\Domains\Lien\Livewire\ProjectShow;
use App\Domains\Lien\Http\Controllers\WaiverDownloadController;
use App\Domains\Lien\Http\Controllers\WaiverPaymentController;
use App\Domains\Lien\Livewire\Waivers\ContactForm;
use App\Domains\Lien\Livewire\Waivers\ContactList;
use App\Domains\Lien\Livewire\Waivers\WaiverDashboard;
use App\Domains\Lien\Livewire\Waivers\WaiverList;
use App\Domains\Lien\Livewire\Waivers\WaiverSeatManager;
use App\Domains\Lien\Livewire\Waivers\WaiverShow;
use App\Domains\Lien\Livewire\Waivers\WaiverSubscriptionCheckout;
use App\Domains\Lien\Livewire\Waivers\WaiverWizard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Lien Routes
|--------------------------------------------------------------------------
*/

// Lien onboarding (must be before the lien.onboarding middleware group)
Route::middleware(['auth', 'business.current', 'business.complete', 'marketing.lead'])
    ->get('/portal/liens/onboarding', LienOnboarding::class)
    ->name('lien.onboarding');

// Profile complete page (shown after onboarding when user has 0 projects)
Route::middleware(['auth', 'business.current', 'business.complete', 'marketing.lead'])
    ->get('/portal/liens/profile-complete', LienProfileComplete::class)
    ->name('lien.profile-complete');

// Authenticated lien routes (with lien onboarding check)
Route::middleware(['auth', 'business.current', 'business.complete', 'lien.onboarding'])
    ->prefix('/portal/liens')
    ->group(function (): void {
        // Dashboard
        Route::get('/', Dashboard::class)->name('lien.dashboard');

        // Project routes
        Route::get('/projects', ProjectList::class)->name('lien.projects.index');
        Route::get('/projects/create', ProjectForm::class)->middleware('marketing.lead')->name('lien.projects.create');
        Route::get('/projects/{project}', ProjectShow::class)->name('lien.projects.show');
        Route::get('/projects/{project}/edit', ProjectForm::class)->name('lien.projects.edit');

        // Filing routes
        Route::get('/projects/{project}/filings/{deadline}/start', FilingWizard::class)
            ->name('lien.filings.start');
        Route::get('/filings/{filing}', FilingShow::class)->name('lien.filings.show');
        Route::get('/filings/{filing}/checkout', FilingCheckout::class)->name('lien.filings.checkout');
        Route::get('/filings/{filing}/payment-confirmation', [FilingPaymentController::class, 'confirmation'])
            ->name('lien.filings.payment-confirmation');
        Route::get('/filings/{filing}/download', [FilingDownloadController::class, 'download'])
            ->name('lien.filings.download');

        // Filings list
        Route::get('/filings', FilingList::class)->name('lien.filings.index');

        // Lien waivers
        Route::get('/waivers', WaiverDashboard::class)->name('lien.waivers.index');
        Route::get('/waivers/all', WaiverList::class)->name('lien.waivers.list');
        Route::get('/waivers/new', WaiverWizard::class)->name('lien.waivers.create');
        Route::get('/waivers/subscribe', WaiverSubscriptionCheckout::class)->name('lien.waivers.subscribe');
        Route::get('/waivers/seats', WaiverSeatManager::class)->name('lien.waivers.seats');
        Route::get('/waivers/payment-confirmation', [WaiverPaymentController::class, 'confirmation'])
            ->name('lien.waivers.payment-confirmation');

        // Waiver contact directory (also reachable inline from the wizard)
        Route::get('/waivers/contacts', ContactList::class)->name('lien.waivers.contacts.index');
        Route::get('/waivers/contacts/new', ContactForm::class)->name('lien.waivers.contacts.create');
        Route::get('/waivers/contacts/{contact}/edit', ContactForm::class)->name('lien.waivers.contacts.edit');

        Route::get('/waivers/{waiver}', WaiverShow::class)->name('lien.waivers.show');
        Route::get('/waivers/{waiver}/download', [WaiverDownloadController::class, 'download'])
            ->name('lien.waivers.download');

        // Deadlines list
        Route::get('/deadlines', DeadlineList::class)->name('lien.deadlines.index');

        // Placeholder routes - redirect to dashboard until implemented
        Route::get('/parties', fn () => redirect()->route('lien.dashboard'))->name('lien.parties.index');
        Route::get('/payments', fn () => redirect()->route('lien.dashboard'))->name('lien.payments.index');
    });

// API route for payment status polling
Route::middleware(['auth:sanctum'])
    ->get('/api/lien/filings/{filing}/payment-status', [FilingPaymentController::class, 'status'])
    ->name('lien.api.payment-status');
