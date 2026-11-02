<?php

namespace App\Modules\Booking\Admin\Actions;

use App\Modules\Booking\Models\Booking;
use App\Modules\Orders\Contracts\OrderCreator;
use App\Modules\Orders\DTO\CreateOrderFromBookingData;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ConfirmBooking
{
    public function __construct(
        private readonly OrderCreator $orderCreator,
    ) {}

    public function execute(
        int $bookingId,
        int $adminId,
    ): Booking {
        return DB::transaction(function () use (
            $bookingId,
            $adminId,
        ): Booking {
            $booking = Booking::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($bookingId);

            // A repeat confirmation must not create a second order.
            if ($booking->status === 'confirmed') {
                $this->createOrder($booking);

                return $booking;
            }

            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages([
                    'booking' => 'Only pending bookings can be confirmed.',
                ]);
            }

            if ($booking->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'booking' => 'This booking has no service items.',
                ]);
            }

            // Create the order through the Orders module's public contract.
            $this->createOrder($booking);

            $booking->update([
                'status' => 'confirmed',
                'confirmed_by' => $adminId,
                'confirmed_at' => now(),
            ]);

            return $booking->refresh();
        });
    }

    private function createOrder(Booking $booking): int
    {
        return $this->orderCreator->createFromBooking(
            new CreateOrderFromBookingData(
                bookingId: (int) $booking->id,
                customerId: (int) $booking->customer_id,
                items: $booking->items
                    ->map(fn ($item): array => [
                        'service_id' => $item->service_id,
                        'service_name' => $item->service_name,
                        'pricing_unit' => $item->pricing_unit,
                        'quantity' => (string) $item->estimated_quantity,
                        'unit_price' => (string) $item->estimated_unit_price,
                        'subtotal' => (string) $item->estimated_subtotal,
                        'notes' => $item->notes,
                    ])
                    ->all(),
            ),
        );
    }
}