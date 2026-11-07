<?php

namespace App\Modules\Orders\Repositories;

use App\Modules\Orders\Contracts\BillableOrder;
use App\Modules\Orders\DTO\BillableOrderData;
use App\Modules\Orders\DTO\BillableOrderSummary;
use App\Modules\Orders\Models\LaundryOrder;
use DomainException;
use Illuminate\Support\Collection;

class EloquentBillableOrderReader implements BillableOrder
{
    /** Laundry has physically been received (or is further along). */
    public const BILLABLE_STATUSES = [
        'received',
        'processing',
        'ready_for_delivery',
        'completed',
    ];

    public function findForBilling(int $orderId): BillableOrderData
    {
        $order = LaundryOrder::query()
            ->with('items')
            ->findOrFail($orderId);

        if (! in_array($order->status, self::BILLABLE_STATUSES, true)) {
            throw new DomainException('An invoice can only be created after laundry has been received.');
        }

        $items = $order->items->map(fn ($item) => [
            'description' => $item->service_name,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'line_total' => $item->subtotal,
        ]);

        if ($items->isEmpty()) {
            throw new DomainException('Cannot create an invoice without order items.');
        }

        return new BillableOrderData(
            id: $order->id,
            orderNumber: $order->order_number,
            customerId: $order->customer_id,
            status: $order->status,
            items: $items,
        );
    }

    public function searchForBilling(?string $search = null, int $limit = 50): Collection
    {
        return LaundryOrder::query()
            ->whereIn('status', self::BILLABLE_STATUSES)
            ->has('items')
            ->with('customer:id,name')
            ->withCount('items')
            ->withSum('items', 'subtotal')
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest('received_at')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (LaundryOrder $order) => new BillableOrderSummary(
                id: $order->id,
                orderNumber: $order->order_number,
                customerName: $order->customer?->name,
                status: $order->status,
                receivedAt: $order->received_at?->toIso8601String(),
                itemCount: (int) $order->items_count,
                itemsTotal: number_format((float) ($order->items_sum_subtotal ?? 0), 2, '.', ''),
            ));
    }
}