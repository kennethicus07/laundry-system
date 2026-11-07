<?php

namespace App\Modules\Orders\DTO;

use Illuminate\Support\Collection;

final readonly class BillableOrderData
{
    public function __construct(
        public int $id,
        public string $orderNumber,
        public int $customerId,
        public string $status,
        public Collection $items,
    ) {}
}