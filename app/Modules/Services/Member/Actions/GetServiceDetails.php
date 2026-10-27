<?php

namespace App\Modules\Services\Member\Actions;

use App\Modules\Services\Contracts\ServiceRepositoryInterface;
use App\Modules\Services\Models\Service;

class GetServiceDetails
{
    public function __construct(
        private readonly ServiceRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?Service
    {
        return $this->repository->findActiveService($id);
    }
}