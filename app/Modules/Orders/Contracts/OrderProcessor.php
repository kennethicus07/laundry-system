<?php

namespace App\Modules\Orders\Contracts;

interface OrderProcessor
{
    /**
     * Start processing received laundry.
     */
    public function startProcessing(int $orderId): void;

    /**
     * Mark processing as completed and ready for delivery.
     */
    public function completeProcessing(int $orderId): void;
}