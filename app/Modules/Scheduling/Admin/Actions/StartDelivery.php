<?php

namespace App\Modules\Scheduling\Admin\Actions;

use App\Modules\Scheduling\Models\Schedule;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class StartDelivery
{
    public function execute(int $scheduleId, int $staffId): void
    {
        DB::transaction(function () use ($scheduleId, $staffId): void {
            $schedule = Schedule::query()
                ->lockForUpdate()
                ->findOrFail($scheduleId);

            $this->ensureAssignedStaff($schedule, $staffId);

            if ($schedule->type !== Schedule::TYPE_DELIVERY) {
                throw new DomainException('This schedule is not a delivery.');
            }

            if ($schedule->status !== Schedule::STATUS_SCHEDULED) {
                throw new DomainException(
                    "Delivery cannot start from status: {$schedule->status}"
                );
            }

            $schedule->update([
                'status' => Schedule::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);
        });
    }

    private function ensureAssignedStaff(
        Schedule $schedule,
        int $staffId,
    ): void {
        if ((int) $schedule->assigned_staff_id !== $staffId) {
            throw new AuthorizationException(
                'You are not assigned to this delivery.'
            );
        }
    }
}