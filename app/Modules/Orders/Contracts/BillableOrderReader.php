<?php

namespace App\Modules\Orders\Contracts;

use App\Modules\Orders\DTO\BillableOrderData;

interface BillableOrderReader
{
    public function findForBilling(int $orderId): BillableOrderData;
}