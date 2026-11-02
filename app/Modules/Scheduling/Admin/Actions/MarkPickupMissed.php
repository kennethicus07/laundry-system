<?php

namespace App\Modules\Scheduling\Admin\Actions;

use App\Modules\Scheduling\Models\Schedule;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class MarkPickupMissed
{
    public function execute(
        int $scheduleId,
        int $staffId,
        ?string $notes = null,
    ): void {
        DB::transaction(function () use (
            $scheduleId,
            $staffId,
            $notes,
        ): void {
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

            if (! in_array($schedule->status, [
                Schedule::STATUS_SCHEDULED,
                Schedule::STATUS_IN_PROGRESS,
            ], true)) {
                throw new DomainException(
                    "Pickup cannot be marked missed from status: {$schedule->status}"
                );
            }

            $schedule->update([
                'status' => Schedule::STATUS_MISSED,
                'outcome_notes' => $notes,
            ]);

            // The order remains awaiting_pickup.
        });
    }
}
