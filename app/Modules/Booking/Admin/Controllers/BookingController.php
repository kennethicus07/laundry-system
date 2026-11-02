<?php

namespace App\Modules\Booking\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Admin\Actions\ConfirmBooking;
use App\Modules\Booking\Admin\Actions\GetAdminBookingDetails;
use App\Modules\Booking\Admin\Actions\GetAdminBookingRequests;
use App\Modules\Booking\Admin\Actions\RejectBooking;
use App\Modules\Booking\Admin\Requests\RejectBookingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BookingController extends Controller
{


    public function index(Request $request, GetAdminBookingRequests $action,): Response {
    $validated = $request->validate([
        'status' => [
            'nullable',
            'in:pending,confirmed,rejected,cancelled',
        ],
        'search' => ['nullable', 'string', 'max:100'],
    ]);

    return Inertia::render('Bookings/Admin/Index', [
        'bookings' => $action->execute(
            status: $validated['status'] ?? null,
            search: $validated['search'] ?? null,
        ),
        'filters' => [
            'status' => $validated['status'] ?? '',
            'search' => $validated['search'] ?? '',
        ],
    ]);

}
    public function show(int $booking,GetAdminBookingDetails $action,): Response {
        return Inertia::render('Bookings/Admin/Show', [
            'booking' => $action->execute($booking),
        ]);
    }

    public function confirm(Request $request, int $booking, ConfirmBooking $action,): RedirectResponse {
        $action->execute(
            bookingId: $booking,
            adminId: (int) $request->user()->id,
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking confirmed and laundry order created.');
    }

    public function reject(RejectBookingRequest $request,int $booking,RejectBooking $action,): RedirectResponse {
    $action->execute(
        bookingId: $booking,
        adminId: (int) $request->user()->id,
        reason: $request->validated('rejection_reason'),
    );

    return to_route('admin.bookings.show', $booking)
        ->with('success', 'Booking rejected successfully.');
}
}