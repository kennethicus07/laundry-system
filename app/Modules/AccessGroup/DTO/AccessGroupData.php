<?php

namespace App\Modules\AccessGroup\DTO;
use App\Enums\Status;

class AccessGroupData
{
    public function __construct(
        public readonly string $name,
          public readonly Status $status, 
    ) {}

public static function fromArray(array $data): self
{
    return new self(
        name: $data['name'],
        status: isset($data['status'])
            ? Status::from($data['status'])
            : Status::ACTIVE,
    );
}
}