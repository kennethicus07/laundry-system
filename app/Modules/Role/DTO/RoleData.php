<?php

namespace App\Modules\Role\DTO;
use App\Enums\RoleStatus;

class RoleData
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly int $access_group_id,
          public readonly RoleStatus $status, 
    ) {}

public static function fromArray(array $data): self
{
    return new self(
        name: $data['name'],
        label: $data['label'],
        access_group_id: (int) $data['access_group_id'],
        status: isset($data['status'])
            ? RoleStatus::from($data['status'])
            : RoleStatus::ACTIVE,
    );
}
}