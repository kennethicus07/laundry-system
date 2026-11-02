<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\ProcessingOrders;
use App\Modules\Orders\DTO\ProcessingOrderData;
use App\Modules\Orders\Models\LaundryOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

final class ListProcessingOrders implements ProcessingOrders
{
    public function searchByStatus(
        array $statuses,
        string $search = '',
        int $perPage = 10,
    ): LengthAwarePaginator {
        $allowedStatuses = [
            LaundryOrder::STATUS_RECEIVED,
            LaundryOrder::STATUS_PROCESSING,
            LaundryOrder::STATUS_READY_FOR_DELIVERY,
            LaundryOrder::STATUS_COMPLETED,
            LaundryOrder::STATUS_CANCELLED,
        ];

        if (empty($statuses) || array_diff($statuses, $allowedStatuses)) {
            throw new InvalidArgumentException('Invalid processing order statuses.');
        }

        $orders = LaundryOrder::query()
            ->with(['customer', 'items'])
            ->whereIn('status', $statuses)
            ->when(trim($search) !== '', function ($query) use ($search) {
                $term = trim($search);

                $query->where(function ($query) use ($term) {
                    $query->where('order_number', 'like', "%{$term}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($term) {
                            $customerQuery->where('name', 'like', "%{$term}%");
                        });
                });
            })
            ->latest('updated_at')
            ->paginate($perPage)
            ->withQueryString();

        return $orders->through(function (LaundryOrder $order) {
            return new ProcessingOrderData(
                id: $order->id,
                orderNumber: $order->order_number,
                bookingNumber: $order->booking?->booking_number,
                status: $order->status,
                customerName: $order->customer?->name,
                receivedAt: $order->received_at?->toDateTimeString(),
                items: $order->items->map(fn ($item) => [
                    'service_name' => $item->service_name,
                    'pricing_unit' => $item->pricing_unit,
                    'quantity' => $item->quantity,
                ])->all(),
            );
        });
    }
}