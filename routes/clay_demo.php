<?php

use App\Http\Controllers\Demo\ClayCounty\ClayCountyDemoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Clay County Parks Demo Sandbox
|--------------------------------------------------------------------------
|
| Isolated, front-end-only concept demo for the Clay County, Missouri
| RFP 78-26 (Historic Sites Website Development) proposal. No database,
| models, or shared eRegister state — all content lives in JSON files
| under resources/demo/clay-county and every interaction is client-side.
|
*/

Route::prefix('clay-demo')->name('clay-demo.')->group(function (): void {
    Route::get('/', [ClayCountyDemoController::class, 'home'])->name('home');
    Route::get('/explore', [ClayCountyDemoController::class, 'explore'])->name('explore');
    Route::get('/destinations/smithville-lake', [ClayCountyDemoController::class, 'smithvilleLake'])->name('smithville-lake');
    Route::get('/trails', [ClayCountyDemoController::class, 'trails'])->name('trails');
    Route::get('/historic-sites', [ClayCountyDemoController::class, 'historicSites'])->name('historic-sites');
    Route::get('/historic-sites/jesse-james-birthplace', [ClayCountyDemoController::class, 'jesseJamesBirthplace'])->name('jesse-james-birthplace');
    Route::get('/events', [ClayCountyDemoController::class, 'events'])->name('events');
    Route::get('/plan-your-visit', [ClayCountyDemoController::class, 'planYourVisit'])->name('plan-your-visit');
});
