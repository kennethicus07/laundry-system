<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\OrderCreator;
use App\Modules\Orders\DTO\CreateOrderFromBookingData;
use App\Modules\Orders\Models\LaundryOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateOrderFromBooking implements OrderCreator
{
    public function createFromBooking(
        CreateOrderFromBookingData $data,
    ): int {
        return DB::transaction(function () use ($data): int {
            // Avoid creating a second order for the same booking.
            $existingOrder = LaundryOrder::query()
                ->where('booking_id', $data->bookingId)
                ->first();

            if ($existingOrder) {
                return (int) $existingOrder->id;
            }

            $order = LaundryOrder::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'booking_id' => $data->bookingId,
                'customer_id' => $data->customerId,
                'status' => 'awaiting_pickup',
            ]);

            foreach ($data->items as $item) {
                $order->items()->create($item);
            }

            return (int) $order->id;
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' .
            Str::upper(Str::random(8));
    }
}