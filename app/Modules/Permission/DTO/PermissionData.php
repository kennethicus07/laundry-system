<?php

namespace App\Modules\Permission\DTO;
use App\Enums\PermissionStatus;

class PermissionData
{
    public function __construct(
        public readonly string $name,
        public readonly int $access_group_id,
        public readonly int $features_id,
        public readonly PermissionStatus $status, 
    ) {}

public static function fromArray(array $data): self
{
    return new self(
        name: $data['name'],
        access_group_id: (int) $data['access_group_id'],
        features_id: (int) $data['features_id'],
        status: isset($data['status'])
            ? PermissionStatus::from($data['status'])
            : PermissionStatus::ACTIVE,
    );
}
}