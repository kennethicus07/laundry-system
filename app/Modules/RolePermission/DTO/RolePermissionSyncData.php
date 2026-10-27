<?php

namespace App\Modules\RolePermission\DTO;

class RolePermissionSyncData
{
    public function __construct(
        public int $accessGroupId,
        public array $roles
    ) {}

    public static function from(array $data): self
    {
        return new self(
            accessGroupId: $data['access_group_id'],
            roles: $data['roles'] ?? [],
        );
    }
}