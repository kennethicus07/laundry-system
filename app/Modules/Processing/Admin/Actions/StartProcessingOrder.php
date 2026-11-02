<?php

namespace App\Modules\Processing\Admin\Actions;

use App\Modules\Orders\Contracts\OrderProcessor;

final class StartProcessingOrder
{
    public function __construct(
        private OrderProcessor $orderProcessor,
    ) {}

    public function execute(int $orderId): void
    {
        $this->orderProcessor->startProcessing($orderId);
    }
}