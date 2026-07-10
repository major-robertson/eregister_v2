<?php

use App\Domains\Business\Http\Controllers\BusinessInvitationController;
use App\Http\Controllers\EmailUnsubscribeController;
use App\Http\Controllers\MarketingLandingController;
use App\Http\Controllers\MarketingRedirectController;
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

// Marketing redirects (banner ads, partnerships)
Route::get('/r/{slug}', [MarketingRedirectController::class, 'handle'])
    ->name('marketing.redirect');

// Marketing landing pages (direct mail campaigns)
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
Route::get('liens/lien-waivers', [\App\Http\Controllers\WaiverLandingController::class, 'index'])->name('liens.lien-waivers');
// Registered before the {state} route so "pricing" resolves to the page, not a state code.
Route::view('liens/lien-waivers/pricing', 'pages.liens.lien-waivers-pricing')->name('liens.lien-waivers.pricing');
Route::get('liens/lien-waivers/{state}', [\App\Http\Controllers\WaiverLandingController::class, 'state'])->name('liens.lien-waivers.state');
Route::view('liens/preliminary-notice', 'pages.liens.preliminary-notice')->name('liens.preliminary-notice');
Route::view('liens/notice-of-intent-to-lien', 'pages.liens.notice-of-intent-to-lien')->name('liens.notice-of-intent-to-lien');
Route::view('liens/lien-release', 'pages.liens.lien-release')->name('liens.lien-release');
Route::view('liens/payment-demand-letter', 'pages.liens.payment-demand-letter')->name('liens.payment-demand-letter');
Route::view('liens/pricing', 'pages.liens.pricing')->name('liens.pricing');

// Government
Route::prefix('government')->name('government.')->group(function () {
    Route::view('/', 'pages.government.index')->name('home');
    Route::view('website-redesign', 'pages.government.website-redesign')->name('website-redesign');
    Route::view('accessibility', 'pages.government.accessibility')->name('accessibility');
    Route::view('cms', 'pages.government.cms')->name('cms');
    Route::view('hosting', 'pages.government.hosting')->name('hosting');
    Route::view('maintenance', 'pages.government.maintenance')->name('maintenance');
    Route::view('portals', 'pages.government.portals')->name('portals');
    Route::view('integrations', 'pages.government.integrations')->name('integrations');
    Route::view('implementation', 'pages.government.implementation')->name('implementation');

    // Sales demos (noindex, shareable by direct URL only)
    // Two design options for EOG–RFQ–26-03; cross-link via banner.
    Route::view('florida-eog-demo-1', 'pages.government.florida-eog-demo-1')->name('florida-eog-demo-1');
    Route::view('florida-eog-demo-2', 'pages.government.florida-eog-demo-2')->name('florida-eog-demo-2');
});

// Email preferences (signed URL, no auth required)
Route::get('/email/preferences/{user}', [EmailUnsubscribeController::class, 'preferences'])
    ->middleware('throttle:30,1')
    ->name('email.preferences');

// Business team invitations. The GET is the emailed temporary signed link
// (guests get bounced to register/login and back); the POST accept requires
// auth + CSRF — the invitation-email match is enforced in the action.
Route::get('/invitations/{invitation}', [BusinessInvitationController::class, 'show'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('invitations.accept');

Route::post('/invitations/{invitation}', [BusinessInvitationController::class, 'accept'])
    ->middleware(['auth', 'throttle:30,1'])
    ->name('invitations.accept.store');

require __DIR__.'/settings.php';
