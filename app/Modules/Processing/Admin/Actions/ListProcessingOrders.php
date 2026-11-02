<?php

namespace App\Modules\Processing\Admin\Actions;

use App\Modules\Orders\Models\LaundryOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ListProcessingOrders
{
    public function execute(string $tab = 'awaiting_processing'): LengthAwarePaginator
    {
        $statuses = match ($tab) {
            'awaiting_processing' => ['received'],
            'in_progress' => ['processing'],
            'ready_for_delivery' => ['ready_for_delivery'],
            'history' => ['completed', 'cancelled'],
            default => throw ValidationException::withMessages([
                'tab' => 'Invalid processing tab.',
            ]),
        };

        return LaundryOrder::query()
            ->whereIn('status', $statuses)
            ->with(['items.service'])
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();
    }
}