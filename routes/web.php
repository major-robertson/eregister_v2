<?php

use Illuminate\Support\Facades\Route;

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

require __DIR__.'/settings.php';
