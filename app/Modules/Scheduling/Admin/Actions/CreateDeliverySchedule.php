<?php

namespace App\Modules\Scheduling\Admin\Actions;

use App\Models\User;
use App\Modules\Orders\Contracts\DeliveryScheduleOrders;
use App\Modules\Scheduling\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateDeliverySchedule
{
    public function __construct(
        private readonly DeliveryScheduleOrders $deliveryOrders,
    ) {}

    public function execute(
        int $orderId,
        int $staffId,
        string $scheduledAt,
        string $address,
    ): Schedule {
        return DB::transaction(function () use (
            $orderId,
            $staffId,
            $scheduledAt,
            $address,
        ): Schedule {

            // Orders owns order eligibility and row locking.
            $this->deliveryOrders->findAndLockAvailableForDelivery($orderId);

            // Scheduling owns duplicate-schedule checks.
            $hasActiveDelivery = Schedule::query()
                ->where('order_id', $orderId)
                ->where('type', Schedule::TYPE_DELIVERY)
                ->whereIn('status', [
                    Schedule::STATUS_SCHEDULED,
                    Schedule::STATUS_IN_PROGRESS,
                ])
                ->exists();

            if ($hasActiveDelivery) {
                throw ValidationException::withMessages([
                    'order_id' => 'This order already has an active delivery schedule.',
                ]);
            }

            $staff = User::query()->find($staffId);

            if (! $staff || ! $staff->hasRole('officer')) {
                throw ValidationException::withMessages([
                    'staff_id' => 'Select a valid officer.',
                ]);
            }

            $address = trim($address);

            if ($address === '') {
                throw ValidationException::withMessages([
                    'address_snapshot' => 'The delivery address is required.',
                ]);
            }

            return Schedule::query()->create([
                'order_id' => $orderId,
                'assigned_staff_id' => $staffId,
                'type' => Schedule::TYPE_DELIVERY,
                'status' => Schedule::STATUS_SCHEDULED,
                'scheduled_at' => $scheduledAt,
                'address_snapshot' => $address,
            ]);
        });
    }
}