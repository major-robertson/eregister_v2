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

Route::view('privacy-policy', 'pages.privacy-policy')->name('privacy-policy');
Route::view('terms-of-service', 'pages.terms-of-service')->name('terms-of-service');
Route::view('refund-policy', 'pages.refund-policy')->name('refund-policy');
Route::view('contact', 'pages.contact')->name('contact');

require __DIR__.'/settings.php';
