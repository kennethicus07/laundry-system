<?php

namespace App\Modules\Dashboard\Admin\Services;

use App\Modules\Booking\Contracts\BookingDashboardReader;
use App\Modules\Orders\Contracts\OrderDashboardReader;
use App\Modules\Billing\Contracts\BillingDashboardReader;

class GetAdminDashboard
{
    public function __construct(
        private BookingDashboardReader $bookings,
        private OrderDashboardReader $orders,
        private BillingDashboardReader $billing,
    ) {}

    public function handle(): array
    {
        return [
            'bookings' => $this->bookings->getBookingSummary(),
            'orders' => $this->orders->getOrderSummary(),
            'billing' => $this->billing->getBillingSummary(),
        ];
    }
}