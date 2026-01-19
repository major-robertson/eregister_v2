<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('styleguide', 'pages.styleguide')
    ->middleware(['auth'])
    ->name('styleguide');

require __DIR__.'/settings.php';
