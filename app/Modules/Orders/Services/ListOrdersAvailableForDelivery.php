<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\DeliveryScheduleOrders;
use App\Modules\Orders\DTO\DeliveryScheduleOrderData;
use App\Modules\Orders\Models\LaundryOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

final class ListOrdersAvailableForDelivery implements DeliveryScheduleOrders
{
    public function searchAvailableForDelivery(
        string $search = '',
        array $excludedOrderIds = [],
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim($search);

        $query = LaundryOrder::query()
            ->with([
                'customer:id,name',
                'booking:id,booking_number,pickup_address',
            ])
            ->where(
                'status',
                LaundryOrder::STATUS_READY_FOR_DELIVERY,
            );

        if ($excludedOrderIds !== []) {
            $query->whereNotIn('id', $excludedOrderIds);
        }

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas(
                        'customer',
                        fn (Builder $customer) => $customer
                            ->where('name', 'like', "%{$search}%"),
                    )
                    ->orWhereHas(
                        'booking',
                        fn (Builder $booking) => $booking
                            ->where('booking_number', 'like', "%{$search}%"),
                    );
            });
        }

        $paginator = $query
            ->orderByDesc('ready_at')
            ->paginate(
                perPage: max(1, min($perPage, 50)),
                columns: ['*'],
                pageName: 'orders_page',
            )
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn (LaundryOrder $order) => $this->toDto($order),
            ),
        );

        return $paginator;
    }

    public function findAndLockAvailableForDelivery(
        int $orderId,
    ): DeliveryScheduleOrderData {
        $order = LaundryOrder::query()
            ->lockForUpdate()
            ->find($orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order_id' => 'The selected order does not exist.',
            ]);
        }

        if (
            $order->status !==
            LaundryOrder::STATUS_READY_FOR_DELIVERY
        ) {
            throw ValidationException::withMessages([
                'order_id' => 'This order is no longer ready for delivery.',
            ]);
        }

        $order->load([
            'customer:id,name',
            'booking:id,booking_number,pickup_address',
        ]);

        return $this->toDto($order);
    }

    private function toDto(
        LaundryOrder $order,
    ): DeliveryScheduleOrderData {
        return new DeliveryScheduleOrderData(
            id: (int) $order->id,
            orderNumber: $order->order_number,
            bookingNumber: $order->booking?->booking_number,
            status: $order->status,
            customerName: $order->customer?->name ?? 'Unknown customer',
            readyAt: $order->ready_at?->toDateTimeString(),
             customerAddress: $order->booking?->pickup_address,
        );
    }
}