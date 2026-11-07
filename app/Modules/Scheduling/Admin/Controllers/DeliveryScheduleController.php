<?php

namespace App\Modules\Scheduling\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Orders\Contracts\DeliveryScheduleOrders;
use App\Modules\Scheduling\Admin\Actions\CreateDeliverySchedule;
use App\Modules\Scheduling\Models\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class DeliveryScheduleController extends Controller
{
    public function index(
        Request $request,
        DeliveryScheduleOrders $deliveryOrders,
    ): Response {
        $orderSearch = trim(
            (string) $request->query('order_search', '')
        );

        $activeOrderIds = Schedule::query()
            ->where('type', Schedule::TYPE_DELIVERY)
            ->whereIn('status', [
                Schedule::STATUS_SCHEDULED,
                Schedule::STATUS_IN_PROGRESS,
            ])
            ->pluck('order_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $orders = $deliveryOrders->searchAvailableForDelivery(
            search: $orderSearch,
            excludedOrderIds: $activeOrderIds,
            perPage: 10,
        );

        $officers = User::role('officer')
            ->orderBy('name')
            ->get(['id', 'name']);

        $scheduleSearch = trim(
            (string) $request->query('schedule_search', '')
        );

        $scheduleStatus = (string) $request->query(
            'schedule_status',
            'all'
        );

        $allowedStatuses = [
            Schedule::STATUS_SCHEDULED,
            Schedule::STATUS_IN_PROGRESS,
            Schedule::STATUS_COMPLETED,
            Schedule::STATUS_MISSED,
            Schedule::STATUS_CANCELLED,
        ];

        $schedules = Schedule::query()
               ->with([
        'order:id,order_number,booking_id',
        'order.booking:id,booking_number,pickup_address',
        'assignedStaff:id,name',
    ])
            ->where('type', Schedule::TYPE_DELIVERY)
            ->when(
                $scheduleStatus !== 'all'
                    && in_array($scheduleStatus, $allowedStatuses, true),
                fn (Builder $query) => $query->where(
                    'status',
                    $scheduleStatus,
                )
            )
            ->when($scheduleSearch !== '', function (Builder $query) use ($scheduleSearch) {
                $term = "%{$scheduleSearch}%";

                $query->where(function (Builder $query) use ($term) {
                    $query
                        ->where('address_snapshot', 'like', $term)
                        ->orWhereHas('order', function (Builder $orderQuery) use ($term) {
                            $orderQuery->where('order_number', 'like', $term);
                        })
                        ->orWhereHas('order.booking', function (Builder $bookingQuery) use ($term) {
                            $bookingQuery->where('booking_number', 'like', $term);
                        })
                        ->orWhereHas('assignedStaff', function (Builder $staffQuery) use ($term) {
                            $staffQuery->where('name', 'like', $term);
                        });
                });
            })
            ->orderByDesc('scheduled_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Deliveries/Admin/ScheduleIndex', [
            'orders' => $orders,
            'orderSearch' => $orderSearch,
            'officers' => $officers,
            'schedules' => $schedules,
            'scheduleSearch' => $scheduleSearch,
            'scheduleStatus' => in_array(
                $scheduleStatus,
                ['all', ...$allowedStatuses],
                true
            ) ? $scheduleStatus : 'all',
        ]);
    }

    public function store(
        Request $request,
        CreateDeliverySchedule $createDeliverySchedule,
    ): RedirectResponse {
        $validated = $request->validate([
            'order_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
            ],
            'address_snapshot' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $createDeliverySchedule->execute(
            orderId: (int) $validated['order_id'],
            staffId: (int) $validated['staff_id'],
            scheduledAt: $validated['scheduled_at'],
            address: $validated['address_snapshot'],
        );

        return to_route('admin.delivery-schedules.index')
            ->with('success', 'Delivery schedule created successfully.');
    }
}