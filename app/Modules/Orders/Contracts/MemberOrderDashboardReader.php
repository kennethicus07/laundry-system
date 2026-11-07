<?php

namespace App\Modules\Orders\Contracts;

interface MemberOrderDashboardReader
{
    public function getSummary(int $customerId): array;
}