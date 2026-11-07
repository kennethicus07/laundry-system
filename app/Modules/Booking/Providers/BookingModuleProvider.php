<?php

namespace App\Modules\Booking\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Booking\Contracts\BookingDashboardReader;
use App\Modules\Booking\Services\ReadBookingDashboard;


class BookingModuleProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BookingDashboardReader::class,
            ReadBookingDashboard::class,
        );
    }
}