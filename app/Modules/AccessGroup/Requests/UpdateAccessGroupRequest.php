<?php

namespace App\Modules\AccessGroup\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Status;

class UpdateAccessGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('access_groups', 'name')
                    ->ignore($this->route('access_group')->id),
            ],

            'label' => [
                'required',
                'string',
                'max:255',
            ],

            'status' => ['required', new Enum(Status::class)],
         
        ];
    }
}