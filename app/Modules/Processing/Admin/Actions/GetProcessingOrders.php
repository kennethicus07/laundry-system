<?php

namespace App\Modules\Processing\Admin\Actions;

use App\Modules\Orders\Contracts\ProcessingOrders;
use App\Modules\Orders\Models\LaundryOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

final class GetProcessingOrders
{
    public function __construct(
        private ProcessingOrders $processingOrders,
    ) {}

    public function execute(
        string $tab = 'awaiting_processing',
        string $search = '',
    ): LengthAwarePaginator {
        $statuses = match ($tab) {
            'awaiting_processing' => [
                LaundryOrder::STATUS_RECEIVED,
            ],
            'in_progress' => [
                LaundryOrder::STATUS_PROCESSING,
            ],
            'ready_for_delivery' => [
                LaundryOrder::STATUS_READY_FOR_DELIVERY,
            ],
            'history' => [
                LaundryOrder::STATUS_COMPLETED,
                LaundryOrder::STATUS_CANCELLED,
            ],
            default => throw ValidationException::withMessages([
                'tab' => 'Invalid processing tab.',
            ]),
        };

        return $this->processingOrders->searchByStatus(
            statuses: $statuses,
            search: $search,
        );
    }
}