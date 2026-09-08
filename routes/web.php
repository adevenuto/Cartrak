<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('garage', 'Garage')->name('garage');
    Route::inertia('history', 'History')->name('history');
    Route::inertia('insights', 'Insights')->name('insights');
});

require __DIR__.'/settings.php';
