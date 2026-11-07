<?php

namespace App\Modules\Scheduling\Admin\Actions;

use App\Modules\Orders\Contracts\OrderDeliverer;
use App\Modules\Scheduling\Models\Schedule;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class CompleteDelivery
{
    public function __construct(
        private readonly OrderDeliverer $orderDeliverer,
    ) {}

    public function execute(
        int $scheduleId,
        int $staffId,
        string $photoPath,
    ): void {
        DB::transaction(function () use (
            $scheduleId,
            $staffId,
            $photoPath
        ): void {
            $schedule = Schedule::query()
                ->lockForUpdate()
                ->findOrFail($scheduleId);

            if ((int) $schedule->assigned_staff_id !== $staffId) {
                throw new AuthorizationException(
                    'You are not assigned to this delivery.'
                );
            }

            if ($schedule->type !== Schedule::TYPE_DELIVERY) {
                throw new DomainException(
                    'This schedule is not a delivery.'
                );
            }

            // Safe to retry after successful completion.
            if ($schedule->status === Schedule::STATUS_COMPLETED) {
                return;
            }

            if ($schedule->status !== Schedule::STATUS_IN_PROGRESS) {
                throw new DomainException(
                    "Delivery cannot be completed from status: {$schedule->status}"
                );
            }

            // Save delivery proof.
            $schedule->update([
                'photo_path' => $photoPath,
                'photo_taken_at' => now(),
            ]);

            // Orders owns the order-status transition.
            $this->orderDeliverer->markDelivered(
                (int) $schedule->order_id
            );

            // Complete the delivery schedule.
            $schedule->update([
                'status' => Schedule::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        });
    }
}
