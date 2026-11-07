<?php

namespace App\Modules\Booking\Contracts;

interface MemberBookingDashboardReader
{
    public function getSummary(int $customerId): array;
}