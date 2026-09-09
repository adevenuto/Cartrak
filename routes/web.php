<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleIntervalController;
use App\Http\Controllers\VehiclePhotoController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('garage', [VehicleController::class, 'index'])->name('garage');
    Route::get('history', HistoryController::class)->name('history');
    Route::inertia('insights', 'Insights')->name('insights');

    Route::resource('vehicles', VehicleController::class)->except(['index']);

    // Not under /storage: Laravel already serves the local disk there, and
    // these need the owner check rather than a signature.
    Route::get('vehicle-photos/{photo}', [VehiclePhotoController::class, 'show'])
        ->name('vehicle-photos.show');
    Route::get('vehicle-photos/{photo}/thumb', [VehiclePhotoController::class, 'thumbnail'])
        ->name('vehicle-photos.thumbnail');

    // Quick calibrate runs straight after adding a car, so the gauges start
    // from something real rather than a screen of grey rings.
    Route::get('vehicles/{vehicle}/calibrate', [VehicleIntervalController::class, 'create'])
        ->name('vehicles.calibrate');
    Route::post('vehicles/{vehicle}/calibrate', [VehicleIntervalController::class, 'store'])
        ->name('vehicles.calibrate.store');
    Route::patch('vehicle-intervals/{interval}', [VehicleIntervalController::class, 'update'])
        ->name('vehicle-intervals.update');

    Route::post('vehicles/{vehicle}/events', [EventController::class, 'store'])
        ->name('vehicles.events.store');
    Route::delete('events/{event}', [EventController::class, 'destroy'])
        ->name('events.destroy');
});

require __DIR__.'/settings.php';
