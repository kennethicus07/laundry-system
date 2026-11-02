<?php

namespace App\Modules\Orders\DTO;

final readonly class CreateOrderFromBookingData
{
    /**
     * @param array<int, array{
     *     service_id: int|null,
     *     service_name: string,
     *     pricing_unit: string,
     *     quantity: string,
     *     unit_price: string,
     *     subtotal: string,
     *     notes: string|null
     * }> $items
     */
    public function __construct(
        public int $bookingId,
        public int $customerId,
        public array $items,
    ) {}
}