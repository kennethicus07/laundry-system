<?php

namespace App\Modules\Orders\Contracts;

use App\Modules\Orders\DTO\BillableOrderData;
use App\Modules\Orders\DTO\BillableOrderSummary;
use Illuminate\Support\Collection;

/**
 * Billing's only doorway into Orders.
 */
interface BillableOrder
{
    /**
     * Full order with items, for creating an invoice.
     * Throws if the laundry has not been received or the order has no items.
     */
    public function findForBilling(int $orderId): BillableOrderData;

    /**
     * Lightweight list of orders that can be billed (received or later, with items),
     * newest first. Billing filters out orders that already have an active invoice.
     *
     * @return Collection<int, BillableOrderSummary>
     */
    public function searchForBilling(?string $search = null, int $limit = 50): Collection;
}