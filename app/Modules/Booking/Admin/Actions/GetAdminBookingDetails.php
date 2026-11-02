<?php

namespace App\Modules\Booking\Admin\Actions;

use App\Modules\Booking\Models\Booking;

final class GetAdminBookingDetails
{
    public function execute(int $bookingId): Booking
    {
        return Booking::query()
            ->with([
                'customer:id,name,email',
                'items',
            ])
            ->findOrFail($bookingId);
    }
}