<?php

namespace App\Modules\Services\Actions;

use App\Modules\Services\Contracts\ServiceCatalog;
use App\Modules\Services\DTO\BookableServiceData;
use App\Modules\Services\Models\Service;

final class FindBookableService implements ServiceCatalog
{
    public function findBookable(int $serviceId): ?BookableServiceData
    {
        $service = Service::query()
            ->whereKey($serviceId)
            ->where('is_active', true)
            ->first();

        if (! $service) {
            return null;
        }

        return new BookableServiceData(
            id: $service->id,
            name: $service->name,
            pricingUnit: $service->pricing_unit,
            defaultUnitPrice: (string) $service->default_unit_price,
        );
    }
}