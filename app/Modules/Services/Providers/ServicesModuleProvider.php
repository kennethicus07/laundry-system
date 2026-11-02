<?php

namespace App\Modules\Services\Providers;

use App\Modules\Services\Contracts\ServiceRepositoryInterface;
use App\Modules\Services\Repositories\EloquentServiceRepository;
use App\Modules\Services\Contracts\ServiceCatalog;
use App\Modules\Services\Actions\FindBookableService;
use Illuminate\Support\ServiceProvider;

class ServicesModuleProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind the repository contract
        $this->app->bind(
            ServiceRepositoryInterface::class,
            EloquentServiceRepository::class,
        );

        // Bind the service catalog contract
        $this->app->bind(
            ServiceCatalog::class,
            FindBookableService::class,
        );

   

        
    }
}
