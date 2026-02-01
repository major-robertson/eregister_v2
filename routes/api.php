<?php

use App\Http\Controllers\Api\V1\MarketingLeadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->middleware('api.key')->group(function () {
    Route::post('/marketing/leads/import', [MarketingLeadController::class, 'import'])
        ->name('api.v1.marketing.leads.import');
});
