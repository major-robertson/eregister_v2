<?php

use App\Domains\Esign\Http\Controllers\SignerDownloadController;
use App\Domains\Esign\Http\Controllers\SignLanding;
use App\Domains\Esign\Livewire\MyDocuments;
use App\Domains\Esign\Livewire\SignConsent;
use App\Domains\Esign\Livewire\SignDone;
use App\Domains\Esign\Livewire\SignReview;
use App\Domains\Esign\Livewire\SignVerifyIdentity;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-Sign (signer-facing) Routes
|--------------------------------------------------------------------------
|
| Deliberately NOT behind the portal's business.current / lien.onboarding
| middleware. Two signer modes, resolved per-request by `esign.signer`
| (EnsureSignerAccess):
|  - Account signers (demand letters): must be logged in + email-verified.
|  - Guest signers (lien waiver counterparties): no account. The emailed
|    signed URL grants entry and a one-time email code proves identity.
|
*/

Route::middleware(['esign.signer'])
    ->prefix('/esign')
    ->name('esign.')
    ->group(function (): void {
        Route::get('/{request}', SignLanding::class)->name('sign')->middleware('signed');
        Route::get('/{request}/verify', SignVerifyIdentity::class)->name('sign.verify');
        Route::get('/{request}/consent', SignConsent::class)->name('sign.consent');
        Route::get('/{request}/review', SignReview::class)->name('sign.review');
        Route::get('/{request}/done', SignDone::class)->name('sign.done');
        Route::get('/{request}/document/{document}/download', [SignerDownloadController::class, 'download'])
            ->name('sign.download');
    });

// A signer's own archive: everything they've signed, tied to their account
// (guest signatures are claimed onto the account by email at login).
Route::middleware(['auth', 'esign.verified'])
    ->get('/esign-documents', MyDocuments::class)
    ->name('esign.mine');
