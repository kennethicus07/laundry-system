<?php

namespace App\Modules\Billing\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: replace with InvoicePolicy@create in the authorization step.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // No "exists:laundry_orders,id": Billing does not reach into Orders
        // tables. The BillableOrder contract reports a missing order.
        return [
            'order_id' => ['required', 'integer', 'min:1'],
        ];
    }
}