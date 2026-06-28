<?php

use App\Domains\Esign\Http\Controllers\SignerDownloadController;
use App\Domains\Esign\Http\Controllers\SignLanding;
use App\Domains\Esign\Livewire\SignConsent;
use App\Domains\Esign\Livewire\SignDone;
use App\Domains\Esign\Livewire\SignReview;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-Sign (signer-facing) Routes
|--------------------------------------------------------------------------
|
| Authenticated signing flow. Deliberately NOT behind the portal's
| business.current / lien.onboarding middleware — the signer just needs to be
| logged in and email-verified. The emailed link is a temporary signed URL; the
| inner pages rely on auth + an in-component identity guard.
|
*/

Route::middleware(['auth', 'esign.verified'])
    ->prefix('/esign')
    ->name('esign.')
    ->group(function (): void {
        Route::get('/{request}', SignLanding::class)->name('sign')->middleware('signed');
        Route::get('/{request}/consent', SignConsent::class)->name('sign.consent');
        Route::get('/{request}/review', SignReview::class)->name('sign.review');
        Route::get('/{request}/done', SignDone::class)->name('sign.done');
        Route::get('/{request}/document/{document}/download', [SignerDownloadController::class, 'download'])
            ->name('sign.download');
    });
