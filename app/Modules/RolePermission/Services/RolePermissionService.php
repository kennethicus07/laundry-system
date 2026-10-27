<?php

namespace App\Modules\RolePermission\Services;

use App\Modules\RolePermission\DTO\RolePermissionSyncData;
use App\Models\Internal\Role;

class RolePermissionService
{
    public function sync(RolePermissionSyncData $dto): void
    {
        foreach ($dto->roles as $roleData) {

            $roleId = $roleData['role_id'];
            $permissions = $roleData['permissions'] ?? [];

            $role = Role::find($roleId);

            if (! $role) {
                continue;
            }

            /*
             * ENTERPRISE RULE:
             * FULL SYNC replaces pivot state (authoritative source = frontend state)
             */
            $role->permissions()->sync($permissions);
        }
    }
}