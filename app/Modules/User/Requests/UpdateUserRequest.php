<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\UserStatus;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],

        'email' => [
            'required',
            'email',
            Rule::unique('users')->ignore($this->route('user')->id),
        ],

        'status' => ['required', new Enum(UserStatus::class)],

        'password' => ['nullable', 'min:6'],

        'roles' => ['nullable', 'array'],
        'roles.*' => ['exists:roles,id'],

        // 🔥 ADD THIS
        'overrides' => ['nullable', 'array'],

        'overrides.*.permission_id' => ['required', 'integer', 'exists:permissions,id'],

        'overrides.*.effect' => ['required', 'integer', 'in:1,-1'],
    ];
}
}