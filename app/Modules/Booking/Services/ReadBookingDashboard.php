<?php

namespace App\Modules\Booking\Services;

use App\Modules\Booking\Contracts\BookingDashboardReader;
use App\Modules\Booking\Models\Booking;

class ReadBookingDashboard implements BookingDashboardReader
{
    public function getBookingSummary(): array
    {
        $counts = Booking::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'pending' => (int) ($counts[Booking::STATUS_PENDING] ?? 0),
            'confirmed' => (int) ($counts[Booking::STATUS_CONFIRMED] ?? 0),
            'rejected' => (int) ($counts[Booking::STATUS_REJECTED] ?? 0),
            'cancelled' => (int) ($counts[Booking::STATUS_CANCELLED] ?? 0),
        ];
    }
}