<?php

use App\Http\Controllers\MarketingLandingController;
use App\Http\Controllers\PostGridWebhookController;
use App\Http\Controllers\SitemapController;
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

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

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

// Form a Business - Register
Route::view('corporation', 'pages.corporation')->name('corporation');
Route::view('dba', 'pages.dba')->name('dba');
Route::view('nonprofit', 'pages.nonprofit')->name('nonprofit');
Route::view('sole-proprietorship', 'pages.sole-proprietorship')->name('sole-proprietorship');

// Form a Business - Run
Route::view('registered-agent', 'pages.registered-agent')->name('registered-agent');
Route::view('annual-reports', 'pages.annual-reports')->name('annual-reports');
Route::view('ein-tax-id', 'pages.ein-tax-id')->name('ein-tax-id');
Route::view('operating-agreement', 'pages.operating-agreement')->name('operating-agreement');

// Compliance & Tax
Route::view('sales-tax-registration', 'pages.sales-tax-registration')->name('sales-tax-registration');
Route::view('resale-certificates', 'pages.resale-certificates')->name('resale-certificates');

// Payment Protection (lien sub-pages)
Route::view('liens/preliminary-notice', 'pages.liens.preliminary-notice')->name('liens.preliminary-notice');
Route::view('liens/notice-of-intent-to-lien', 'pages.liens.notice-of-intent-to-lien')->name('liens.notice-of-intent-to-lien');
Route::view('liens/lien-release', 'pages.liens.lien-release')->name('liens.lien-release');
Route::view('liens/payment-demand-letter', 'pages.liens.payment-demand-letter')->name('liens.payment-demand-letter');

require __DIR__.'/settings.php';
