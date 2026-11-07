<?php

namespace App\Modules\Booking\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Booking\Contracts\BookingDashboardReader;
use App\Modules\Booking\Services\ReadBookingDashboard;

use App\Modules\Booking\Services\ReadMemberBookingDashboard;
use App\Modules\Booking\Contracts\MemberBookingDashboardReader;


class BookingModuleProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BookingDashboardReader::class,
            ReadBookingDashboard::class,
        );

         $this->app->bind(
            MemberBookingDashboardReader::class,
            ReadMemberBookingDashboard::class,
        );
    }
}