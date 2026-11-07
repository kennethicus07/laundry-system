<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\OrderDashboardReader;
use App\Modules\Orders\Models\LaundryOrder;

class ReadOrderDashboard implements OrderDashboardReader
{
    public function getOrderSummary(): array
    {
        $counts = LaundryOrder::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),

            'awaiting_pickup' => (int) (
                $counts[LaundryOrder::STATUS_AWAITING_PICKUP] ?? 0
            ),

            'received' => (int) (
                $counts[LaundryOrder::STATUS_RECEIVED] ?? 0
            ),

            'processing' => (int) (
                $counts[LaundryOrder::STATUS_PROCESSING] ?? 0
            ),

            'ready_for_delivery' => (int) (
                $counts[LaundryOrder::STATUS_READY_FOR_DELIVERY] ?? 0
            ),

            'completed' => (int) (
                $counts[LaundryOrder::STATUS_COMPLETED] ?? 0
            ),

            'cancelled' => (int) (
                $counts[LaundryOrder::STATUS_CANCELLED] ?? 0
            ),
        ];
    }
}