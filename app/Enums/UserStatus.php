<?php

namespace App\Enums;

use App\Enums\Contracts\ExportableEnum;

enum UserStatus:  int implements ExportableEnum
{
    case DEACTIVATED = 0;
    case ACTIVE = 1;
    case PENDING = 2;
    case SUSPENDED = 5;

    public function label(): string
    {
        return match ($this) {
            self::DEACTIVATED => 'Deactivated',
            self::ACTIVE => 'Active',
            self::PENDING => 'Pending',
            self::SUSPENDED => 'Suspended',
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