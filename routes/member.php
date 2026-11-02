<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Member\Controllers\DashboardController;
use App\Modules\Services\Member\Controllers\ServiceController;
use App\Modules\Booking\Member\Controllers\BookingController;

Route::middleware(['auth', 'verified', 'role:member'])->group(function () {

    Route::prefix('member')->name('member.')->group(function () {
       
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index');
        });

        Route::prefix('laundry-services')->name('laundry-services.')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index');
            Route::get('/{service}', [ServiceController::class,'show'])->whereNumber('service')->name('show');
            Route::post('/{service}/book', [BookingController::class, 'store'])->whereNumber('service')->name('book');
        });

        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [BookingController::class, 'index'])->name('index');
            Route::get('/{booking}', [BookingController::class, 'show'])->whereNumber('booking')->name('show');
            Route::post('/{booking}/cancel', [BookingController::class,'cancel',])->whereNumber('booking')->name('cancel');
        });
      

    });
});