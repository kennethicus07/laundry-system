<?php

namespace App\Modules\Booking\Actions;

use App\Modules\Booking\Models\Booking;

final class GetMemberBookingDetails
{
    public function execute(
        int $bookingId,
        int $customerId,
    ): Booking {
        return Booking::query()
            ->whereKey($bookingId)
            ->where('customer_id', $customerId)
            ->with('items')
            ->firstOrFail();
    }
}