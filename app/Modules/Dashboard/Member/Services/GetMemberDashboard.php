<?php

namespace App\Modules\Dashboard\Member\Services;

use App\Modules\Booking\Contracts\MemberBookingDashboardReader;
use App\Modules\Orders\Contracts\MemberOrderDashboardReader;

class GetMemberDashboard
{
    public function __construct(
        private MemberBookingDashboardReader $bookings,
        private MemberOrderDashboardReader $orders,
    ) {}

    public function handle(int $customerId): array
    {
        return [
            'bookings' => $this->bookings->getSummary($customerId),
            'orders' => $this->orders->getSummary($customerId),
        ];
    }
}