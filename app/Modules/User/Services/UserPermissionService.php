<?php

namespace App\Modules\User\Services;

use App\Models\User;
use App\Models\UserPermissionOverride;


class UserPermissionService
{
    public static function getRolePermissions(User $user): array
    {
        return $user->getPermissionsViaRoles()->pluck('id')->toArray();
    }

    public static function getDirectPermissions(User $user): array
    {
        return $user->getDirectPermissions()->pluck('id')->toArray();
    }

    /**
     * return:
     * [
     *   permission_id => effect
     * ]
     */

    public static function getOverrides(User $user): array
    {
        return UserPermissionOverride::query()
            ->where('model_id', $user->id)
            ->where('model_type', User::class)
            ->pluck('effect', 'permission_id')
            ->toArray();
    }

    /**
     * FINAL EFFECTIVE PERMISSIONS
     */
public static function resolve(User $user): array
{
    $role = self::getRolePermissions($user);
    $direct = self::getDirectPermissions($user);
    $overrides = self::getOverrides($user);

    $permissions = array_flip(array_unique(array_merge($role, $direct)));

    foreach ($overrides as $permId => $effect) {
        if ((int) $effect === 1) {
            $permissions[$permId] = true;
        }

        if ((int) $effect === -1) {
            unset($permissions[$permId]);
        }
    }

    return [
        'permissions' => array_keys($permissions), // ✅ ONLY SOURCE
        'overrides' => $overrides,
    ];
}
}