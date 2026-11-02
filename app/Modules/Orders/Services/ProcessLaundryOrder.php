<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\OrderProcessor;
use App\Modules\Orders\Models\LaundryOrder;
use DomainException;
use Illuminate\Support\Facades\DB;

final class ProcessLaundryOrder implements OrderProcessor
{
    public function startProcessing(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $order = LaundryOrder::query()
                ->lockForUpdate()
                ->findOrFail($orderId);

            // Safe to retry.
            if ($order->status === LaundryOrder::STATUS_PROCESSING) {
                return;
            }

            if ($order->status !== LaundryOrder::STATUS_RECEIVED) {
                throw new DomainException(
                    "Order cannot start processing from status: {$order->status}"
                );
            }

            $order->update([
                'status' => LaundryOrder::STATUS_PROCESSING,
                'processing_started_at' => now(),
            ]);
        });
    }

    public function completeProcessing(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $order = LaundryOrder::query()
                ->lockForUpdate()
                ->findOrFail($orderId);

            // Safe to retry.
            if (
                $order->status ===
                LaundryOrder::STATUS_READY_FOR_DELIVERY
            ) {
                return;
            }

            if ($order->status !== LaundryOrder::STATUS_PROCESSING) {
                throw new DomainException(
                    "Order cannot be completed from status: {$order->status}"
                );
            }

            $order->update([
                'status' => LaundryOrder::STATUS_READY_FOR_DELIVERY,
                'ready_at' => now(),
            ]);
        });
    }
}