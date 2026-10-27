<?php

namespace App\Modules\Permission\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\PermissionStatus;

class UpdatePermissionRequest extends FormRequest
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
                Rule::unique('permissions', 'name')
                    ->ignore($this->route('permission')->id),
            ],

            'label' => [
                'required',
                'string',
                'max:255',
             ],
        

            'access_group_id' => [
                'required',
                'exists:access_groups,id',
            ],

            'features_id' => [
                'required',
                'exists:features,id',
            ],

            'status' => ['required', new Enum(PermissionStatus::class)],
         
        ];
    }
}