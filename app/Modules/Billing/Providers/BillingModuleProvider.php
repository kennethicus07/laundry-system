<?php

namespace App\Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Billing\Contracts\BillingDashboardReader;
use App\Modules\Billing\Services\ReadBillingDashboard;

class BillingModuleProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BillingDashboardReader::class,
            ReadBillingDashboard::class,
        );
    }
}