<?php

namespace App\Modules\Orders\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface ProcessingOrders
{
    public function searchByStatus(
        array $statuses,
        string $search = '',
        int $perPage = 10,
    ): LengthAwarePaginator;
}