<?php

use App\Http\Controllers\MarketingLandingController;
use App\Http\Controllers\PostGridWebhookController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Stripe webhook (no auth, CSRF excluded in bootstrap/app.php)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->name('webhooks.stripe');

// PostGrid webhook (no auth, CSRF excluded in bootstrap/app.php)
Route::post('/webhooks/postgrid', [PostGridWebhookController::class, 'handle'])
    ->name('webhooks.postgrid');

// Marketing landing pages
Route::get('/go/t/{token}', [MarketingLandingController::class, 'tokenLanding'])
    ->name('marketing.landing.token');

Route::get('/go/{slug}', [MarketingLandingController::class, 'slugLanding'])
    ->name('marketing.landing.slug');

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/liens', function () {
    return view('liens');
})->name('liens');

Route::get('/landing2', function () {
    return view('landing2');
})->name('landing2');

Route::view('styleguide', 'pages.styleguide')
    ->middleware(['auth'])
    ->name('styleguide');

Route::view('privacy-policy', 'pages.privacy-policy')->name('privacy-policy');
Route::view('terms-of-service', 'pages.terms-of-service')->name('terms-of-service');
Route::view('refund-policy', 'pages.refund-policy')->name('refund-policy');
Route::view('contact', 'pages.contact')->name('contact');

Route::get('/llc', function () {
    return view('llc');
})->name('llc');

require __DIR__.'/settings.php';
