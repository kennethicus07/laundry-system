<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\UserStatus;


class StoreUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'status' => ['required', new Enum(UserStatus::class)],
            'password' => ['required', 'min:6'],


            'roles' => ['required', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'auto_verify' => ['sometimes', 'boolean'],
        ];
    }
}        