<?php

namespace App\Modules\Scheduling\Admin\Actions;

use App\Modules\Orders\Contracts\OrderReceiver;
use App\Modules\Scheduling\Models\Schedule;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class CompletePickup
{
    public function __construct(
        private readonly OrderReceiver $orderReceiver,
    ) {}

    public function execute(int $scheduleId, int $staffId): void
    {
        DB::transaction(function () use ($scheduleId, $staffId): void {
            $schedule = Schedule::query()
                ->lockForUpdate()
                ->findOrFail($scheduleId);

            if ((int) $schedule->assigned_staff_id !== $staffId) {
                throw new AuthorizationException(
                    'You are not assigned to this pickup.'
                );
            }

            if ($schedule->type !== Schedule::TYPE_PICKUP) {
                throw new DomainException(
                    'This schedule is not a pickup.'
                );
            }

            // Safe to retry after a successful completion.
            if ($schedule->status === Schedule::STATUS_COMPLETED) {
                return;
            }

            if ($schedule->status !== Schedule::STATUS_IN_PROGRESS) {
                throw new DomainException(
                    "Pickup cannot be completed from status: {$schedule->status}"
                );
            }

            // Orders owns the order-status transition.
            $this->orderReceiver->markReceived(
                (int) $schedule->order_id
            );

            $schedule->update([
                'status' => Schedule::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        });
    }
}
