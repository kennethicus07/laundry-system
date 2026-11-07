<?php

namespace App\Modules\Billing\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordCashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: replace with PaymentPolicy@recordCash in the authorization step.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.decimal' => 'Enter an amount with up to 2 decimal places.',
            'amount.gt' => 'Payment must be greater than zero.',
        ];
    }
}