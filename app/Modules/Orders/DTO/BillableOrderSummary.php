<?php

namespace App\Modules\Orders\DTO;

final readonly class BillableOrderSummary
{
    public function __construct(
        public int $id,
        public string $orderNumber,
        public ?string $customerName,
        public string $status,
        public ?string $receivedAt,
        public int $itemCount,
        public string $itemsTotal, // 2-decimal string, display only
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->orderNumber,
            'customer_name' => $this->customerName,
            'status' => $this->status,
            'received_at' => $this->receivedAt,
            'item_count' => $this->itemCount,
            'items_total' => $this->itemsTotal,
        ];
    }
}