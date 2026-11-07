<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\OrderDeliverer;
use App\Modules\Orders\Models\LaundryOrder;
use DomainException;
use Illuminate\Support\Facades\DB;

final class DeliverLaundryOrder implements OrderDeliverer
{
    public function markDelivered(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $order = LaundryOrder::query()
                ->lockForUpdate()
                ->findOrFail($orderId);

            // Safe to retry after successful delivery.
            if ($order->status === LaundryOrder::STATUS_COMPLETED) {
                return;
            }

            if ($order->status !== LaundryOrder::STATUS_READY_FOR_DELIVERY) {
                throw new DomainException(
                    "Order cannot be completed from status: {$order->status}"
                );
            }

            $order->update([
                'status' => LaundryOrder::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        });
    }
}