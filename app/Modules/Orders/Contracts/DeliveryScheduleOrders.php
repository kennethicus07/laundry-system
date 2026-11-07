<?php

namespace App\Modules\Orders\Contracts;

use App\Modules\Orders\DTO\DeliveryScheduleOrderData;
use Illuminate\Pagination\LengthAwarePaginator;

interface DeliveryScheduleOrders
{
    public function searchAvailableForDelivery(
        string $search = '',
        array $excludedOrderIds = [],
        int $perPage = 10,
    ): LengthAwarePaginator;

    public function findAndLockAvailableForDelivery(
        int $orderId,
    ): DeliveryScheduleOrderData;
}