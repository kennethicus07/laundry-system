<?php

namespace App\Modules\Services\Contracts;

use App\Modules\Services\DTO\BookableServiceData;

interface ServiceCatalog
{
    public function findBookable(int $serviceId): ?BookableServiceData;
}