<?php

namespace App\Modules\Booking\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The Admin route middleware checks the user's role.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('rejection_reason'))) {
            $this->merge([
                'rejection_reason' => trim(
                    $this->input('rejection_reason')
                ),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' =>
                'A rejection reason is required.',

            'rejection_reason.min' =>
                'The rejection reason must be at least 5 characters.',

            'rejection_reason.max' =>
                'The rejection reason cannot exceed 1000 characters.',
        ];
    }
}