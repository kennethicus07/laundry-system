<?php

use Illuminate\Support\Facades\Route;
use  App\Modules\Permission\Controllers\PermissionController;
use  App\Modules\Role\Controllers\RoleController;
use  App\Modules\AccessGroup\Controllers\AccessGroupController;
use  App\Modules\RolePermission\Controllers\RolePermissionSyncController;
use App\Modules\User\Controllers\UserController;
use App\Modules\Dashboard\Admin\Controllers\DashboardController;
use App\Modules\Booking\Admin\Controllers\BookingController;
use App\Modules\Scheduling\Admin\Controllers\PickupController;
use App\Modules\Scheduling\Admin\Controllers\PickupScheduleController;
use App\Modules\Processing\Admin\Controllers\ProcessingController;

Route::middleware(['auth', 'verified', 'role:super-admin|moderator|staff|officer'])->group(function () {

    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {

        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/', [DashboardController::class, '__invoke'])->name('index');
        });

        Route::inertia('/users', 'Users/Index')->name('users.index');
        Route::inertia('/events', 'Events/Index')->name('events.index');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('roles-permissions')->name('roles-permissions.')->group(function () {
            Route::post('/sync', [RolePermissionSyncController::class, 'store']);
        });

        Route::prefix('access-groups')->name('access_groups.')->group(function () {
            Route::get('/', [AccessGroupController::class, 'index'])->name('index');
            Route::get('/{access_group}/edit', [AccessGroupController::class, 'edit'])->name('edit');
            Route::post('/', [AccessGroupController::class, 'store'])->name('store');
            Route::put('/{access_group}', [AccessGroupController::class, 'update'])->name('update');
            Route::delete('/{access_group}', [AccessGroupController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [BookingController::class, 'index'])->name('index');
            Route::get('/{booking}', [BookingController::class,'show',])->whereNumber('booking')->name('show');
            Route::post('/{booking}/confirm', [BookingController::class,'confirm',])->whereNumber('booking')->name('confirm');
            Route::post('/{booking}/reject',[BookingController::class, 'reject'])->whereNumber('booking')->name('reject');
        });

        Route::prefix('pickup-schedules')->name('pickup-schedules.')->group(function () {
            Route::get('/', [PickupScheduleController::class,'index'])->name('index');
            Route::post('/', [PickupScheduleController::class,'store'])->name('store');
        });

        Route::prefix('pickups')->name('pickups.')->group(function () {
            Route::get('/', [PickupController::class,'index'])->name('index');
            Route::post('/{schedule}/start', [PickupController::class,'start'])->whereNumber('schedule')->name('start');
            Route::post('/{schedule}/complete', [PickupController::class,'complete'])->whereNumber('schedule')->name('complete');
            Route::post('/{schedule}/missed', [PickupController::class,'missed'])->whereNumber('schedule')->name('missed');
        });

        Route::prefix('processing')->name('processing.')->group(function () {
            Route::get('/', [ProcessingController::class,'index'])->name('index');
            Route::post('/{order}/start', [ProcessingController::class,'start',])->whereNumber('order')->name('start');
            Route::post('/{order}/complete', [ProcessingController::class,'complete',])->whereNumber('order')->name('complete');
        });
        
    });



       

 
});
