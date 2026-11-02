<?php

namespace App\Modules\Scheduling\Admin\Actions;

use App\Modules\Scheduling\Models\Schedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListMyPickups
{
    public function execute(int $staffId): LengthAwarePaginator
    {
        return Schedule::query()
            ->with(['order.customer'])
            ->where('assigned_staff_id', $staffId)
            ->where('type', Schedule::TYPE_PICKUP)
            ->latest('scheduled_at')
            ->paginate(10)
            ->through(fn (Schedule $schedule) => [
                'id' => $schedule->id,
                'status' => $schedule->status,
                'scheduled_at' => $schedule->scheduled_at?->toISOString(),
                'address_snapshot' => $schedule->address_snapshot,
                'started_at' => $schedule->started_at?->toISOString(),
                'completed_at' => $schedule->completed_at?->toISOString(),
                'outcome_notes' => $schedule->outcome_notes,
                'order' => $schedule->order ? [
                    'id' => $schedule->order->id,
                    'order_number' => $schedule->order->order_number,
                    'status' => $schedule->order->status,
                    'booking' => $schedule->order->booking ? [
                            'id' => $schedule->order->booking->id,
                            'booking_number' => $schedule->order->booking->booking_number,
                    ] : null,
                    'customer' => $schedule->order->customer ? [
                        'name' => $schedule->order->customer->name,
                    ] : null,
                ] : null,
            ]);
    }
}
