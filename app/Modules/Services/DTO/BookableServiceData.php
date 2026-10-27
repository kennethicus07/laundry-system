<?php

namespace App\Modules\Services\DTO;

final readonly class BookableServiceData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $pricingUnit,
        public string $defaultUnitPrice,
    ) {}
}