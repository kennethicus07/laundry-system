<?php

namespace App\Modules\Orders\DTO;

final readonly class DeliveryScheduleOrderData
{
    public function __construct(
        public int $id,
        public string $orderNumber,
        public ?string $bookingNumber,
        public string $status,
        public string $customerName,
        public ?string $readyAt,
        public ?string $customerAddress,
    ) {}
}