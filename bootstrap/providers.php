<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    App\Modules\Services\Providers\ServicesModuleProvider::class,
    App\Modules\Orders\Providers\OrdersModuleProvider::class,
    App\Modules\Booking\Providers\BookingModuleProvider::class,
    App\Modules\Billing\Providers\BillingModuleProvider::class,
];
