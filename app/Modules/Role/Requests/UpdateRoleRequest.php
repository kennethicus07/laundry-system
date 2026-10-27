<?php

namespace App\Modules\Role\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\RoleStatus;

class UpdateRoleRequest extends FormRequest
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
                Rule::unique('roles', 'name')
                    ->ignore($this->route('role')->id),
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
            
            'status' => ['required', new Enum(RoleStatus::class)],
         
        ];
    }
}