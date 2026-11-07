<?php

namespace App\Modules\Billing\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Billing\Models\Refund */
class RefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'amount' => $this->amount,
            'status' => $this->status->value,
            'reason' => $this->reason,
            'provider_reference' => $this->provider_reference,
            'requested_at' => $this->requested_at?->toIso8601String(),
            'processed_at' => $this->processed_at?->toIso8601String(),

            'requested_by' => $this->whenLoaded('requestedBy', fn () => $this->requestedBy?->only(['id', 'name'])),
            'approved_by' => $this->whenLoaded('approvedBy', fn () => $this->approvedBy?->only(['id', 'name'])),
            'payment' => $this->whenLoaded('payment', fn () => [
                'id' => $this->payment->id,
                'method' => $this->payment->method->value,
                'amount' => $this->payment->amount,
                'invoice' => $this->payment->relationLoaded('invoice') ? [
                    'id' => $this->payment->invoice->id,
                    'invoice_number' => $this->payment->invoice->invoice_number,
                ] : null,
            ]),
        ];
    }
}