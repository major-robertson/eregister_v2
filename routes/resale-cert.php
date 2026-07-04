<?php

use App\Domains\ResaleCert\Http\Controllers\CertificateDownloadController;
use App\Domains\ResaleCert\Http\Controllers\SubscriptionPaymentController;
use App\Domains\ResaleCert\Livewire\CertificateList;
use App\Domains\ResaleCert\Livewire\CertificateShow;
use App\Domains\ResaleCert\Livewire\CertificateWizard;
use App\Domains\ResaleCert\Livewire\Dashboard;
use App\Domains\ResaleCert\Livewire\ProfileSettings;
use App\Domains\ResaleCert\Livewire\ResaleOnboarding;
use App\Domains\ResaleCert\Livewire\SubscriptionCheckout;
use App\Domains\ResaleCert\Livewire\VendorForm;
use App\Domains\ResaleCert\Livewire\VendorList;
use App\Domains\ResaleCert\Livewire\VendorShow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Resale Certificate Workspace Routes
|--------------------------------------------------------------------------
|
| The Resale Certificate Generator: a $297/yr subscription product. The
| dashboard, subscribe, and checkout pages are open to any business member
| (the dashboard renders setup/subscribe prompts); everything that
| generates or manages certificates sits behind the resale.subscribed
| gate. Onboarding (resale profile + tax registrations + signature) is
| enforced in-component so it can redirect fluidly mid-wizard.
|
*/

Route::middleware(['auth', 'business.current', 'business.complete'])
    ->prefix('/portal/resale-certificates')
    ->group(function (): void {
        Route::get('/', Dashboard::class)
            ->name('resale-cert.dashboard');

        // The dashboard doubles as the pricing page for unsubscribed
        // businesses (fewer steps to checkout); keep the old subscribe URL
        // for backlinks.
        Route::redirect('/subscribe', '/portal/resale-certificates')
            ->name('resale-cert.subscribe');

        Route::get('/checkout', SubscriptionCheckout::class)
            ->name('resale-cert.checkout');

        Route::get('/payment-confirmation', [SubscriptionPaymentController::class, 'confirmation'])
            ->name('resale-cert.payment-confirmation');

        Route::middleware('resale.subscribed')->group(function (): void {
            // Onboarding and settings are reachable with an unverified email —
            // the signature step handles verification in-context so users see
            // "Set up your resale profile" first, not a bare verify-email page.
            Route::get('/onboarding', ResaleOnboarding::class)
                ->name('resale-cert.onboarding');

            Route::get('/settings', ProfileSettings::class)
                ->name('resale-cert.settings');

            // Generating applies the signature, so this page requires a
            // verified email (esign.verified stashes the intended URL and
            // returns the user here after they click the link).
            Route::get('/certificates/create', CertificateWizard::class)
                ->middleware('esign.verified')
                ->name('resale-cert.certificates.create');

            Route::get('/vendors', VendorList::class)
                ->name('resale-cert.vendors.index');

            Route::get('/vendors/create', VendorForm::class)
                ->name('resale-cert.vendors.create');

            Route::get('/vendors/{vendor}', VendorShow::class)
                ->name('resale-cert.vendors.show');

            Route::get('/vendors/{vendor}/edit', VendorForm::class)
                ->name('resale-cert.vendors.edit');

            Route::get('/certificates', CertificateList::class)
                ->name('resale-cert.certificates.index');

            Route::get('/certificates/{certificate}', CertificateShow::class)
                ->name('resale-cert.certificates.show');

            Route::get('/certificates/{certificate}/download', CertificateDownloadController::class)
                ->name('resale-cert.certificates.download');
        });
    });

// Payment status polling for the processing page.
Route::middleware(['auth:sanctum'])
    ->get('/api/resale-cert/payment-status', [SubscriptionPaymentController::class, 'status'])
    ->name('resale-cert.api.payment-status');
