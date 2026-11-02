<?php

namespace App\Modules\Orders\DTO;

final readonly class PickupScheduleOrderData
{
    public function __construct(
        public int $id,
        public string $orderNumber,
        public ?string $bookingNumber,
        public string $status,
        public string $customerName,
        public ?string $requestedPickupAt,
        public ?string $preferredPickUpAddress,
    ) {}
}