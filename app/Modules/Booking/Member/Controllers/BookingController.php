<?php

namespace App\Modules\Booking\Member\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Actions\CreateBooking;
use App\Modules\Booking\Actions\GetMemberBookingDetails;
use App\Modules\Booking\Actions\GetMemberBookings;
use App\Modules\Booking\DTO\CreateBookingData;
use App\Modules\Booking\Actions\CancelMemberBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(
        Request $request,
        GetMemberBookings $action,
    ): Response {
        return Inertia::render('Bookings/Member/Index', [
            'bookings' => $action->execute(
                (int) $request->user()->id,
            ),
        ]);
    }

    public function show(
        Request $request,
        int $booking,
        GetMemberBookingDetails $action,
    ): Response {
        return Inertia::render('Bookings/Member/Show', [
            'booking' => $action->execute(
                $booking,
                (int) $request->user()->id,
            ),
        ]);
    }

    public function store(
        Request $request,
        int $service,
        CreateBooking $action,
    ): RedirectResponse {
        $validated = $request->validate([
            'pickup_address' => ['required', 'string', 'max:2000'],
            'preferred_pickup_at' => ['required', 'date', 'after:now'],
            'estimated_quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:100000',
            ],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = $action->execute(
            new CreateBookingData(
                customerId: (int) $request->user()->id,
                serviceId: $service,
                pickupAddress: $validated['pickup_address'],
                preferredPickupAt: $validated['preferred_pickup_at'],
                estimatedQuantity: (string) $validated['estimated_quantity'],
                customerNotes: $validated['customer_notes'] ?? null,
            ),
        );

        return Redirect::route('member.bookings.show', $booking->id)
            ->with('success', 'Your booking request has been submitted.');
    }


    public function cancel(
        Request $request,
        int $booking,
        CancelMemberBooking $action,
    ): RedirectResponse {
        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute(
            bookingId: $booking,
            customerId: (int) $request->user()->id,
            reason: isset($validated['cancellation_reason'])
                ? trim($validated['cancellation_reason']) ?: null
                : null,
        );

        return Redirect::route('member.bookings.show', $booking)
            ->with('success', 'Your booking has been cancelled.');
    }

    
}