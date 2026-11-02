<?php

namespace App\Modules\Booking\Admin\Actions;

use App\Modules\Booking\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetAdminBookingRequests
{
    private const STATUSES = [
        'pending',
        'confirmed',
        'rejected',
        'cancelled',
    ];

    public function execute(
        ?string $status = null,
        ?string $search = null,
    ): LengthAwarePaginator {
        return Booking::query()
            ->with([
                'customer:id,name,email',
                'items:id,booking_id,service_name,estimated_quantity,pricing_unit,estimated_subtotal',
            ])
            ->when(
                $status && in_array($status, self::STATUSES, true),
                fn ($query) => $query->where('status', $status),
            )
            ->when(
                filled($search),
                function ($query) use ($search) {
                    $term = trim($search);

                    $query->where(function ($query) use ($term) {
                        $query
                            ->where('booking_number', 'like', "%{$term}%")
                            ->orWhereHas('customer', function ($customerQuery) use ($term) {
                                $customerQuery
                                    ->where('name', 'like', "%{$term}%")
                                    ->orWhere('email', 'like', "%{$term}%");
                            });
                    });
                },
            )
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();
    }
}