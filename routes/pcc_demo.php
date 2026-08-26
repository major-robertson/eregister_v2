<?php

use App\Http\Controllers\Demo\Pcc\PccDemoController;

/*
|--------------------------------------------------------------------------
| Pitt Community College Demo Sandbox
|--------------------------------------------------------------------------
|
| Isolated, front-end-only concept demo for the Pitt Community College
| RFP 115-6181 proposal (website redesign). Content lives in JSON files
| under resources/demo/pcc and every interaction is client-side — no
| database, no forms that submit. Unbuilt destinations open a "Demo
| preview" modal instead of dead links.
|
*/

Route::prefix('pcc-demo')->name('pcc-demo.')->group(function (): void {
    Route::get('/', [PccDemoController::class, 'home'])->name('home');
    Route::get('/programs', [PccDemoController::class, 'programs'])->name('programs');
    Route::get('/programs/{slug}', [PccDemoController::class, 'program'])->name('program');
    Route::get('/admissions', [PccDemoController::class, 'admissions'])->name('admissions');
    Route::get('/paying-for-college', [PccDemoController::class, 'paying'])->name('paying');
    Route::get('/student-life', [PccDemoController::class, 'studentLife'])->name('student-life');
    Route::get('/workforce', [PccDemoController::class, 'workforce'])->name('workforce');
    Route::get('/about', [PccDemoController::class, 'about'])->name('about');
});
