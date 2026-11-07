<?php

namespace App\Modules\Billing\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Billing\Models\Payment */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'amount' => $this->amount,
            'method' => $this->method->value,
            'status' => $this->status->value,
            'reference_number' => $this->reference_number,
            // Never expose proof_path; the proof is served by an authorized controller.
            'has_proof' => $this->hasProof(),
            'notes' => $this->notes,
            'received_at' => $this->received_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),

            'recorded_by' => $this->whenLoaded('recordedBy', fn () => $this->recordedBy?->only(['id', 'name'])),
            'verified_by' => $this->whenLoaded('verifiedBy', fn () => $this->verifiedBy?->only(['id', 'name'])),
            'invoice' => $this->whenLoaded('invoice', fn () => [
                'id' => $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number,
            ]),
            'refunds' => $this->whenLoaded(
    'refunds',
    fn () => RefundResource::collection(
        $this->refunds
    )->resolve(),
),
        ];
    }
}