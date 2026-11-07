<?php

namespace App\Modules\Billing\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Billing\Models\Invoice */
class InvoiceSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'order_id' => $this->order_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status->value,
            'revision_number' => $this->revision_number,
            'issued_at' => $this->issued_at?->toIso8601String(),
        ];
    }
}