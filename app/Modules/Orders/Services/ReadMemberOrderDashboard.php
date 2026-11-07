<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\MemberOrderDashboardReader;
use App\Modules\Orders\Models\LaundryOrder;

class ReadMemberOrderDashboard implements MemberOrderDashboardReader
{
    public function getSummary(int $customerId): array
    {
        $counts = LaundryOrder::query()
            ->where('customer_id', $customerId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentOrders = LaundryOrder::query()
            ->where('customer_id', $customerId)
            ->select([
                'id',
                'order_number',
                'status',
                'ready_at',
                'created_at',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get();

        return [
            'total' => (int) $counts->sum(),

            'active' => (int) (
                ($counts[LaundryOrder::STATUS_AWAITING_PICKUP] ?? 0)
                + ($counts[LaundryOrder::STATUS_RECEIVED] ?? 0)
                + ($counts[LaundryOrder::STATUS_PROCESSING] ?? 0)
                + ($counts[LaundryOrder::STATUS_READY_FOR_DELIVERY] ?? 0)
            ),

            'completed' => (int) (
                $counts[LaundryOrder::STATUS_COMPLETED] ?? 0
            ),

            'recent' => $recentOrders,
        ];
    }
}