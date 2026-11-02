<?php

namespace App\Modules\Booking\Admin\Actions;

use App\Modules\Booking\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectBooking
{
    public function execute(
        int $bookingId,
        int $adminId,
        string $reason,
    ): Booking {
        $reason = trim($reason);

        // Enforce the rule even if the Action is called outside HTTP.
        if (mb_strlen($reason) < 5 || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages([
                'rejection_reason' =>
                    'The rejection reason must be between 5 and 1000 characters.',
            ]);
        }

        return DB::transaction(function () use (
            $bookingId,
            $adminId,
            $reason,
        ) {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($bookingId);

            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages([
                    'booking' =>
                        'Only pending bookings can be rejected.',
                ]);
            }

            $booking->status = 'rejected';
            $booking->rejected_by = $adminId;
            $booking->rejected_at = now();
            $booking->rejection_reason = $reason;

            $booking->save();

            return $booking;
        });
    }
}