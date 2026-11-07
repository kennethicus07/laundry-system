<?php

namespace App\Modules\Booking\Contracts;

interface BookingDashboardReader
{
    /**
     * @return array<string, int>
     */
    public function getBookingSummary(): array;
}