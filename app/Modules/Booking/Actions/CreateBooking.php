<?php

namespace App\Modules\Booking\Actions;

use App\Modules\Booking\DTO\CreateBookingData;
use App\Modules\Booking\Models\Booking;
use App\Modules\Services\Contracts\ServiceCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateBooking
{
    public function __construct(
        private readonly ServiceCatalog $serviceCatalog,
    ) {}

    public function execute(CreateBookingData $data): Booking
    {
        $service = $this->serviceCatalog->findBookable(
            $data->serviceId
        );

        if (! $service) {
            throw ValidationException::withMessages([
                'service' => 'This service is currently unavailable.',
            ]);
        }

        return DB::transaction(function () use ($data, $service) {
            $quantity = (float) $data->estimatedQuantity;
            $unitPrice = (float) $service->defaultUnitPrice;

            $booking = Booking::create([
                'booking_number' => 'BKG-' . Str::uuid(),
                'customer_id' => $data->customerId,
                'pickup_address' => $data->pickupAddress,
                'preferred_pickup_at' => $data->preferredPickupAt,
                'customer_notes' => $data->customerNotes,
                'status' => 'pending',
            ]);

            $booking->items()->create([
                'service_id' => $service->id,
                'service_name' => $service->name,
                'pricing_unit' => $service->pricingUnit,
                'estimated_quantity' => $data->estimatedQuantity,
                'estimated_unit_price' => $service->defaultUnitPrice,
                'estimated_subtotal' => number_format(
                    round($quantity * $unitPrice, 2),
                    2,
                    '.',
                    ''
                ),
            ]);

            return $booking;
        });
    }
}