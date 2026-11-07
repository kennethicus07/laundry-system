<?php

namespace App\Modules\Billing\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Billing\Models\InvoiceRevision */
class InvoiceRevisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'revision_number' => $this->revision_number,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'reason' => $this->reason,
            'revised_at' => $this->revised_at?->toIso8601String(),

            'revised_by' => $this->whenLoaded('revisedBy', fn () => $this->revisedBy?->only(['id', 'name'])),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->values()),
        ];
    }
}