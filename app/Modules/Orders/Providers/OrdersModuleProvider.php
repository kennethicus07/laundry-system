<?php

namespace App\Modules\Orders\Providers;

use App\Modules\Orders\Contracts\OrderCreator;
use App\Modules\Orders\Contracts\OrderReceiver;
use App\Modules\Orders\Services\CreateOrderFromBooking;
use App\Modules\Orders\Services\ReceiveLaundryOrder;
use App\Modules\Orders\Contracts\PickupScheduleOrders;
use App\Modules\Orders\Services\ListOrdersAvailableForPickup;
use App\Modules\Orders\Contracts\OrderProcessor;
use App\Modules\Orders\Services\ProcessLaundryOrder;
use App\Modules\Orders\Contracts\ProcessingOrders;
use App\Modules\Orders\Services\ListProcessingOrders;
use App\Modules\Orders\Contracts\DeliveryScheduleOrders;
use App\Modules\Orders\Services\ListOrdersAvailableForDelivery;
use App\Modules\Orders\Contracts\OrderDeliverer;
use App\Modules\Orders\Services\DeliverLaundryOrder;
use App\Modules\Orders\Contracts\BillableOrder;
use App\Modules\Orders\Repositories\EloquentBillableOrderReader;


use Illuminate\Support\ServiceProvider;

class OrdersModuleProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            OrderCreator::class,
            CreateOrderFromBooking::class,
        );

        $this->app->bind(
            OrderReceiver::class,
            ReceiveLaundryOrder::class,
        );

        $this->app->bind(
            PickupScheduleOrders::class,
            ListOrdersAvailableForPickup::class,
        );

        $this->app->bind(
            OrderProcessor::class,
            ProcessLaundryOrder::class,
        );

        $this->app->bind(
            ProcessingOrders::class,
            ListProcessingOrders::class
        );

        $this->app->bind(
            DeliveryScheduleOrders::class,
            ListOrdersAvailableForDelivery::class,
        );

        $this->app->bind(
            OrderDeliverer::class,
            DeliverLaundryOrder::class,
        );
               
       $this->app->bind(
    BillableOrder::class,
    EloquentBillableOrderReader::class,
);
        
    }
}
