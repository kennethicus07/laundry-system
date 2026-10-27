<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        return match (true) {
            $user->hasRole('super-admin') || $user->hasRole('moderator')
                => redirect()->route('admin.dashboard.index'),

            $user->hasRole('member')
                => redirect()->route('member.laundry-services.index'),

            default
                => redirect()->route('dashboard'),
        };
    }
}