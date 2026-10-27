<?php

namespace App\Modules\Booking\Actions;

use App\Modules\Booking\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetMemberBookings
{
    public function execute(
        int $customerId,
    ): LengthAwarePaginator {
        return Booking::query()
            ->where('customer_id', $customerId)
            ->with('items')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();
    }
}