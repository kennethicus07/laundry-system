<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\PickupScheduleOrders;
use App\Modules\Orders\DTO\PickupScheduleOrderData;
use App\Modules\Orders\Models\LaundryOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

final class ListOrdersAvailableForPickup implements PickupScheduleOrders
{
    public function listAvailableForPickup(): array
    {
        return LaundryOrder::query()
            ->with([
                'customer:id,name',
                'booking:id,booking_number,preferred_pickup_at,pickup_address',
            ])
            ->where(
                'status',
                LaundryOrder::STATUS_AWAITING_PICKUP,
            )
            ->orderBy('id')
            ->get()
            ->map(fn (LaundryOrder $order) => $this->toDto($order))
            ->all();
    }

    public function searchAvailableForPickup(
        string $search = '',
        array $excludedOrderIds = [],
        int $perPage = 10,
    ): LengthAwarePaginator {
        $search = trim($search);

        $query = LaundryOrder::query()
            ->with([
                'customer:id,name',
                'booking:id,booking_number,preferred_pickup_at,pickup_address',
            ])
            ->where(
                'status',
                LaundryOrder::STATUS_AWAITING_PICKUP,
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
            ->orderByDesc('id')
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

    public function findAndLockAvailableForPickup(
        int $orderId,
    ): PickupScheduleOrderData {
        $order = LaundryOrder::query()
            ->lockForUpdate()
            ->find($orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order_id' => 'The selected order does not exist.',
            ]);
        }

        if ($order->status !== LaundryOrder::STATUS_AWAITING_PICKUP) {
            throw ValidationException::withMessages([
                'order_id' => 'This order is no longer awaiting pickup.',
            ]);
        }

        $order->load([
            'customer:id,name',
            'booking:id,booking_number,preferred_pickup_at,pickup_address',
        ]);

        return $this->toDto($order);
    }

    private function toDto(
        LaundryOrder $order,
    ): PickupScheduleOrderData {
        return new PickupScheduleOrderData(
            id: (int) $order->id,
            orderNumber: $order->order_number,
            bookingNumber: $order->booking?->booking_number,
            status: $order->status,
            customerName: $order->customer?->name ?? 'Unknown customer',
            requestedPickupAt: $order->booking?->preferred_pickup_at
                ?->toDateTimeString(),
            preferredPickUpAddress: $order->booking?->pickup_address,
        );
    }
}
