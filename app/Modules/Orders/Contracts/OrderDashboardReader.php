<?php

namespace App\Modules\Orders\Contracts;

interface OrderDashboardReader
{
    /**
     * @return array<string, int>
     */
    public function getOrderSummary(): array;
}