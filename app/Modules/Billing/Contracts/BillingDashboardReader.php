<?php

namespace App\Modules\Billing\Contracts;

interface BillingDashboardReader
{
    /**
     * @return array<string, int|string>
     */
    public function getBillingSummary(): array;
}