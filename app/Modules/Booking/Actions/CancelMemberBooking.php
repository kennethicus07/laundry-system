<?php

namespace App\Modules\Booking\Actions;

use App\Modules\Booking\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelMemberBooking
{
    public function execute(
        int $bookingId,
        int $customerId,
        ?string $reason = null,
    ): Booking {
        return DB::transaction(function () use (
            $bookingId,
            $customerId,
            $reason,
        ) {
            // Restrict the query to the authenticated member's own booking.
            $booking = Booking::query()
                ->where('customer_id', $customerId)
                ->whereKey($bookingId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages([
                    'booking' => 'Only pending bookings can be cancelled.',
                ]);
            }

            $booking->status = 'cancelled';
            $booking->cancellation_reason = $reason;
            $booking->cancelled_at = now();
            $booking->save();

            return $booking;
        });
    }
}
