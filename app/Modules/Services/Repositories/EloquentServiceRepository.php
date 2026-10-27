<?php

namespace App\Modules\Services\Repositories;

use App\Modules\Services\Contracts\ServiceRepositoryInterface;
use App\Modules\Services\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class EloquentServiceRepository implements ServiceRepositoryInterface
{
    public function getActiveServices(): Collection
    {
        return Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findActiveService(int $id): ?Service
    {
        return Service::query()
            ->where('is_active', true)
            ->find($id);
    }
}