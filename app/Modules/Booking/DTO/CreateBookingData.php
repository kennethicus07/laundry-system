<?php

namespace App\Modules\Booking\DTO;

final readonly class CreateBookingData
{
    public function __construct(
        public int $customerId,
        public int $serviceId,
        public string $pickupAddress,
        public string $mobileNumber,
        public string $preferredPickupAt,
        public string $estimatedQuantity,
        public ?string $customerNotes,
    ) {}
}