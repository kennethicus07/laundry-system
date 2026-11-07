<?php

namespace App\Modules\Billing\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Billing\Models\Invoice */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'order_id' => $this->order_id,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->status->value,
            'revision_number' => $this->revision_number,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'voided_at' => $this->voided_at?->toIso8601String(),

            'items' => $this->whenLoaded(
                'items',
                fn () => $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ])->values()
            ),

            'payments' => $this->whenLoaded(
                'payments',
                fn () => $this->payments
                    ->map(fn ($payment) => (new PaymentResource($payment))->resolve($request))
                    ->values()
            ),

            'revisions' => $this->whenLoaded(
                'revisions',
                fn () => $this->revisions
                    ->map(fn ($revision) => (new InvoiceRevisionResource($revision))->resolve($request))
                    ->values()
            ),
        ];
    }
}