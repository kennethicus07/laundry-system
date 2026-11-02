<?php

namespace App\Modules\Orders\Contracts;

use App\Modules\Orders\DTO\CreateOrderFromBookingData;

interface OrderCreator
{
    /**
     * Create an order for a booking, or return its existing order ID.
     */
    public function createFromBooking(
        CreateOrderFromBookingData $data,
    ): int;
}