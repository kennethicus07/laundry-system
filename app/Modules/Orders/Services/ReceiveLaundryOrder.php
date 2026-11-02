<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\OrderReceiver;
use App\Modules\Orders\Models\LaundryOrder;
use DomainException;
use Illuminate\Support\Facades\DB;

final class ReceiveLaundryOrder implements OrderReceiver
{
    public function markReceived(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $order = LaundryOrder::query()
                ->lockForUpdate()
                ->findOrFail($orderId);

            // Safe to retry if a previous attempt succeeded.
            if ($order->status === LaundryOrder::STATUS_RECEIVED) {
                return;
            }

            if (
                $order->status !==
                LaundryOrder::STATUS_AWAITING_PICKUP
            ) {
                throw new DomainException(
                    "Order cannot be received from status: {$order->status}"
                );
            }

            $order->update([
                'status' => LaundryOrder::STATUS_RECEIVED,
                'received_at' => now(),
            ]);
        });
    }
}
