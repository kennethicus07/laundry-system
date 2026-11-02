<?php

namespace App\Modules\Orders\Contracts;

use App\Modules\Orders\DTO\PickupScheduleOrderData;
use Illuminate\Pagination\LengthAwarePaginator;

interface PickupScheduleOrders
{
    public function searchAvailableForPickup(
        string $search = '',
        array $excludedOrderIds = [],
        int $perPage = 10,
    ): LengthAwarePaginator;

    public function findAndLockAvailableForPickup(
        int $orderId,
    ): PickupScheduleOrderData;
}