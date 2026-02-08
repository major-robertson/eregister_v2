<?php

use App\Domains\Billing\Livewire\Checkout;
use App\Domains\Business\Livewire\BusinessSwitcher;
use App\Domains\Business\Livewire\OnboardingWizard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard / Portal Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    // Business Selection (only shown when user needs to pick or create a business)
    Route::get('/portal/select-business', BusinessSwitcher::class)
        ->middleware('marketing.lead')
        ->name('portal.select-business');

    // Session-based portal routes (business resolved via middleware)
    Route::prefix('/portal')
        ->middleware('business.current')
        ->group(function (): void {
            // Onboarding (before profile complete check)
            Route::get('/onboarding', OnboardingWizard::class)
                ->middleware('marketing.lead')
                ->name('portal.onboarding');

            // Dashboard and other routes (requires complete profile)
            Route::middleware('business.complete')->group(function (): void {
                Route::get('/', function () {
                    return view('portal.dashboard');
                })->name('dashboard');

                // Checkout for a specific application
                Route::get('/checkout/{application}', Checkout::class)
                    ->name('portal.checkout');
            });
        });
});
