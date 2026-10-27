<?php

namespace App\Modules\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Modules\Services\Models\Service;

interface ServiceRepositoryInterface
{
    public function getActiveServices(): Collection;
    public function findActiveService(int $id): ?Service;
}
