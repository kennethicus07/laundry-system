<?php

namespace App\Modules\Booking\Services;

use App\Modules\Booking\Contracts\MemberBookingDashboardReader;
use App\Modules\Booking\Models\Booking;

class ReadMemberBookingDashboard implements MemberBookingDashboardReader
{
    public function getSummary(int $customerId): array
    {
        $counts = Booking::query()
            ->where('customer_id', $customerId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentBookings = Booking::query()
            ->where('customer_id', $customerId)
            ->select([
                'id',
                'booking_number',
                'status',
                'preferred_pickup_at',
                'created_at',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get();

        return [
            'total' => (int) $counts->sum(),
            'pending' => (int) ($counts[Booking::STATUS_PENDING] ?? 0),
            'confirmed' => (int) ($counts[Booking::STATUS_CONFIRMED] ?? 0),
            'rejected' => (int) ($counts[Booking::STATUS_REJECTED] ?? 0),
            'cancelled' => (int) ($counts[Booking::STATUS_CANCELLED] ?? 0),
            'recent' => $recentBookings,
        ];
    }
}