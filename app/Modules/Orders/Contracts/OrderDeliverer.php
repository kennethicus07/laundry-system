<?php

namespace App\Modules\Orders\Contracts;

interface OrderDeliverer
{
    public function markDelivered(int $orderId): void;
}