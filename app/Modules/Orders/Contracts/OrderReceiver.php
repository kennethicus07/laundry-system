<?php

namespace App\Modules\Orders\Contracts;

interface OrderReceiver
{
    /**
     * Record that the laundry has physically been received.
     *
     * The operation is safe to retry if the order is already received.
     */
    public function markReceived(int $orderId): void;
}