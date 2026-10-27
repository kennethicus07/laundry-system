<?php

namespace App\Modules\Services\Member\Actions;

use App\Modules\Services\Contracts\ServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetAvailableServices
{
    public function __construct(
        private readonly ServiceRepositoryInterface $repository
    ) {}

    public function execute(): Collection
    {
        return $this->repository->getActiveServices();
    }
}
