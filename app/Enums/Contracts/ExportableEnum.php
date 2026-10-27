<?php

namespace App\Enums\Contracts;

interface ExportableEnum
{
    public static function toArray(): array;
}