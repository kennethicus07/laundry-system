<?php

namespace App\Modules\AccessGroup\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Status;

class StoreAccessGroupRequest extends FormRequest
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
                'unique:access_groups,name',
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