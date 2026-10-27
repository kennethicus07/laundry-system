<?php

namespace App\Modules\Role\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\RoleStatus;

class StoreRoleRequest extends FormRequest
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
                'unique:roles,name',
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