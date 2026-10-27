<?php

namespace App\Enums;

use App\Enums\Contracts\ExportableEnum;

enum PermissionStatus: int implements ExportableEnum
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }

    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->name => $case->value,
            ])
            ->toArray();
    }
}