<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

import { Button } from '@/components/ui/button';

interface BookingItem {
    id: number;
    service_id: number | null;
    service_name: string;
    pricing_unit: string;
    estimated_quantity: string;
    estimated_unit_price: string;
    estimated_subtotal: string;
    notes: string | null;
}

interface Booking {
    id: number;
    booking_number: string;
    status: 'pending' | 'confirmed' | 'rejected' | 'cancelled';
    pickup_address: string;
    preferred_pickup_at: string;
    customer_notes: string | null;
    created_at: string;
    confirmed_at: string | null;
    rejection_reason: string | null;
    cancellation_reason: string | null;
    items: BookingItem[];
    order: LaundryOrder | null;
}

interface PickupSchedule {
    id: number;
    type: 'pickup' | 'delivery';
    status: 'scheduled' | 'in_progress' | 'completed' | 'missed' | 'cancelled';
    scheduled_at: string | null;
    started_at: string | null;
    completed_at: string | null;
    address_snapshot: string | null;
}

interface LaundryOrder {
    id: number;
    order_number: string;
    status:
        | 'awaiting_pickup'
        | 'received'
        | 'processing'
        | 'ready_for_delivery'
        | 'completed'
        | 'cancelled';
    schedules: PickupSchedule[];
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'My Bookings',
                href: '/member/bookings',
            },
            {
                title: 'Booking Details',
                href: '',
            },
        ],
    },
});

const props = defineProps<{
    booking: Booking;
}>();

function money(value: string | number): string {
    return Number(value).toLocaleString('en-PH', {
        style: 'currency',
        currency: 'PHP',
    });
}

function dateTime(value: string | null): string {
    if (!value) return 'Not available';

    return new Date(value).toLocaleString('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function statusLabel(status: Booking['status']): string {
    const labels: Record<Booking['status'], string> = {
        pending: 'Pending confirmation',
        confirmed: 'Confirmed',
        rejected: 'Rejected',
        cancelled: 'Cancelled',
    };

    return labels[status];
}

function statusClass(status: Booking['status']): string {
    const classes: Record<Booking['status'], string> = {
        pending: 'bg-amber-100 text-amber-800',
        confirmed: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        cancelled: 'bg-muted text-muted-foreground',
    };

    return classes[status];
}

const estimatedTotal = () =>
    props.booking.items.reduce(
        (total, item) => total + Number(item.estimated_subtotal),
        0,
    );

const cancelForm = useForm({
    cancellation_reason: '',
});

function cancelBooking() {
    cancelForm.post(`/member/bookings/${props.booking.id}/cancel`, {
        preserveScroll: true,
        onSuccess: () => cancelForm.reset(),
    });
}
</script>

<template>
    <Head :title="`Booking ${booking.booking_number}`" />

    <main class="mx-auto max-w-4xl space-y-6 p-6">
        <div>
            <Link
                href="/member/bookings"
                class="text-sm text-muted-foreground hover:text-foreground"
            >
                ← Back to My Bookings
            </Link>
        </div>

        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    Booking Details
                </h1>

                <p class="text-sm text-muted-foreground">
                    Reference: {{ booking.booking_number }}
                </p>
            </div>

            <span
                class="w-fit rounded-full px-3 py-1 text-sm font-medium"
                :class="statusClass(booking.status)"
            >
                {{ statusLabel(booking.status) }}
            </span>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Booking Status</CardTitle>
                <CardDescription>
                    Your request was submitted
                    {{ dateTime(booking.created_at) }}.
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-3">
                <p v-if="booking.status === 'pending'" class="text-sm">
                    Your request is waiting for staff confirmation. The pickup
                    schedule is requested, not yet guaranteed.
                </p>

                <p v-else-if="booking.status === 'confirmed'" class="text-sm">
                    Your booking has been confirmed by staff.
                </p>

                <p v-else-if="booking.status === 'rejected'" class="text-sm">
                    Your booking request was rejected.
                </p>

                <p v-else class="text-sm">This booking has been cancelled.</p>

                <div
                    v-if="
                        booking.status === 'rejected' &&
                        booking.rejection_reason
                    "
                    class="rounded-md bg-destructive/10 p-3 text-sm"
                >
                    <p class="font-medium">Reason for rejection</p>
                    <p>{{ booking.rejection_reason }}</p>
                </div>

                <div
                    v-if="
                        booking.status === 'cancelled' &&
                        booking.cancellation_reason
                    "
                    class="rounded-md bg-muted p-3 text-sm"
                >
                    <p class="font-medium">Cancellation reason</p>
                    <p>{{ booking.cancellation_reason }}</p>
                </div>

                <p
                    v-if="booking.confirmed_at"
                    class="text-sm text-muted-foreground"
                >
                    Confirmed at: {{ dateTime(booking.confirmed_at) }}
                </p>
            </CardContent>
        </Card>

        <!-- Laundry Order and Pickup Tracking -->
        <Card>
            <CardHeader>
                <CardTitle>Laundry Order & Tracking</CardTitle>
                <CardDescription>
                    Follow your laundry from pickup through processing and
                    delivery.
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-5">
                <!-- No order yet -->
                <div v-if="!booking.order" class="rounded-lg bg-muted p-4">
                    <p class="font-medium">Laundry order not created yet</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Your booking must be confirmed before its laundry order
                        and pickup schedule become available.
                    </p>
                </div>

                <!-- Order exists -->
                <template v-else>
                    <div class="rounded-lg border p-4">
                        <p class="text-sm text-muted-foreground">
                            Order Number
                        </p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ booking.order.order_number }}
                        </p>

                        <div class="mt-3">
                            <p class="text-sm text-muted-foreground">
                                Order Status
                            </p>

                            <span
                                class="mt-1 inline-flex rounded-full bg-primary/10 px-3 py-1 text-sm font-medium capitalize"
                            >
                                {{ booking.order.status.replaceAll('_', ' ') }}
                            </span>
                        </div>
                    </div>

                    <!-- Progress steps -->
                    <div>
                        <h3 class="mb-3 font-semibold">Order Progress</h3>

                        <ol class="space-y-3">
                            <li
                                v-for="step in [
                                    {
                                        status: 'awaiting_pickup',
                                        label: 'Awaiting Pickup',
                                    },
                                    {
                                        status: 'received',
                                        label: 'Laundry Received',
                                    },
                                    {
                                        status: 'processing',
                                        label: 'Processing Laundry',
                                    },
                                    {
                                        status: 'ready_for_delivery',
                                        label: 'Ready for Delivery',
                                    },
                                    {
                                        status: 'completed',
                                        label: 'Order Completed',
                                    },
                                ]"
                                :key="step.status"
                                class="flex items-start gap-3"
                            >
                                <span
                                    class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border text-xs"
                                    :class="{
                                        'border-primary bg-primary text-primary-foreground':
                                            booking.order.status ===
                                            step.status,
                                        'border-green-600 bg-green-600 text-white':
                                            [
                                                'awaiting_pickup',
                                                'received',
                                                'processing',
                                                'ready_for_delivery',
                                                'completed',
                                            ].indexOf(booking.order.status) >
                                            [
                                                'awaiting_pickup',
                                                'received',
                                                'processing',
                                                'ready_for_delivery',
                                                'completed',
                                            ].indexOf(step.status),
                                    }"
                                >
                                    <span
                                        v-if="
                                            [
                                                'awaiting_pickup',
                                                'received',
                                                'processing',
                                                'ready_for_delivery',
                                                'completed',
                                            ].indexOf(booking.order.status) >
                                            [
                                                'awaiting_pickup',
                                                'received',
                                                'processing',
                                                'ready_for_delivery',
                                                'completed',
                                            ].indexOf(step.status)
                                        "
                                    >
                                        ✓
                                    </span>
                                </span>

                                <div>
                                    <p class="text-sm font-medium">
                                        {{ step.label }}
                                    </p>

                                    <p
                                        v-if="
                                            booking.order.status === step.status
                                        "
                                        class="text-xs text-muted-foreground"
                                    >
                                        Current status
                                    </p>
                                </div>
                            </li>
                        </ol>

                        <p
                            v-if="booking.order.status === 'cancelled'"
                            class="mt-3 rounded-md bg-destructive/10 p-3 text-sm"
                        >
                            This laundry order has been cancelled.
                        </p>
                    </div>

                    <!-- Pickup and delivery schedules -->
                    <div>
                        <h3 class="mb-3 font-semibold">
                            Pickup & Delivery Schedule
                        </h3>

                        <div
                            v-if="booking.order.schedules.length === 0"
                            class="rounded-lg bg-muted p-4 text-sm text-muted-foreground"
                        >
                            No pickup or delivery schedules have been created
                            yet.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="schedule in booking.order.schedules"
                                :key="schedule.id"
                                class="rounded-lg border p-4"
                            >
                                <div
                                    class="flex flex-wrap items-center justify-between gap-2"
                                >
                                    <p class="font-medium capitalize">
                                        {{ schedule.type }} Schedule
                                    </p>

                                    <span
                                        class="rounded-full bg-muted px-3 py-1 text-xs font-medium capitalize"
                                    >
                                        {{
                                            schedule.status.replaceAll('_', ' ')
                                        }}
                                    </span>
                                </div>

                                <div class="mt-3 space-y-2 text-sm">
                                    <div>
                                        <p class="text-muted-foreground">
                                            Scheduled Date & Time
                                        </p>
                                        <p class="mt-1 font-medium">
                                            {{
                                                dateTime(
                                                    schedule.scheduled_at ?? '',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div v-if="schedule.address_snapshot">
                                        <p class="text-muted-foreground">
                                            Address
                                        </p>
                                        <p class="mt-1 whitespace-pre-line">
                                            {{ schedule.address_snapshot }}
                                        </p>
                                    </div>

                                    <div v-if="schedule.started_at">
                                        <p class="text-muted-foreground">
                                            Started At
                                        </p>
                                        <p class="mt-1">
                                            {{ dateTime(schedule.started_at) }}
                                        </p>
                                    </div>

                                    <div v-if="schedule.completed_at">
                                        <p class="text-muted-foreground">
                                            Completed At
                                        </p>
                                        <p class="mt-1">
                                            {{
                                                dateTime(schedule.completed_at)
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Requested Services</CardTitle>
                <CardDescription>
                    These are the estimated services and quantities in your
                    original booking request.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <div
                    v-if="booking.items.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No booking items were found.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="item in booking.items"
                        :key="item.id"
                        class="flex flex-col gap-3 border-b pb-4 last:border-0 last:pb-0 sm:flex-row sm:justify-between"
                    >
                        <div class="space-y-1">
                            <h3 class="font-semibold">
                                {{ item.service_name }}
                            </h3>

                            <p class="text-sm text-muted-foreground">
                                Quantity: {{ item.estimated_quantity }}
                                {{ item.pricing_unit }}
                            </p>

                            <p class="text-sm text-muted-foreground">
                                Unit price:
                                {{ money(item.estimated_unit_price) }}
                            </p>

                            <p
                                v-if="item.notes"
                                class="text-sm text-muted-foreground"
                            >
                                Note: {{ item.notes }}
                            </p>
                        </div>

                        <p class="font-semibold sm:text-right">
                            {{ money(item.estimated_subtotal) }}
                        </p>
                    </div>

                    <div class="flex justify-between border-t pt-4">
                        <span class="font-semibold">Estimated total</span>
                        <span class="text-lg font-bold">
                            {{ money(estimatedTotal()) }}
                        </span>
                    </div>

                    <p class="text-xs text-muted-foreground">
                        This is an estimate based on your booking items. The
                        final charge may differ after the laundry is inspected
                        and weighed.
                    </p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Pickup Information</CardTitle>
                <CardDescription>
                    The details you submitted with your request.
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4">
                <div>
                    <p class="text-sm text-muted-foreground">
                        Preferred pickup date and time
                    </p>
                    <p class="mt-1 font-medium">
                        {{ dateTime(booking.preferred_pickup_at) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">Pickup address</p>
                    <p class="mt-1 font-medium whitespace-pre-line">
                        {{ booking.pickup_address }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Special instructions
                    </p>
                    <p class="mt-1 whitespace-pre-line">
                        {{
                            booking.customer_notes ||
                            'No special instructions provided.'
                        }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <Card v-if="booking.status === 'pending'">
            <CardHeader>
                <CardTitle>Cancel Booking</CardTitle>
                <CardDescription>
                    You can cancel this booking while it is still pending
                    confirmation.
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4">
                <div class="space-y-2">
                    <label
                        for="cancellation_reason"
                        class="text-sm font-medium"
                    >
                        Reason for cancellation (optional)
                    </label>

                    <textarea
                        id="cancellation_reason"
                        v-model="cancelForm.cancellation_reason"
                        maxlength="1000"
                        rows="3"
                        placeholder="Tell us why you want to cancel..."
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        :disabled="cancelForm.processing"
                    />

                    <p
                        v-if="cancelForm.errors.cancellation_reason"
                        class="text-sm text-destructive"
                    >
                        {{ cancelForm.errors.cancellation_reason }}
                    </p>

                    <p
                        v-if="cancelForm.errors.booking"
                        class="text-sm text-destructive"
                    >
                        {{ cancelForm.errors.booking }}
                    </p>
                </div>

                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <Button
                            type="button"
                            variant="destructive"
                            :disabled="cancelForm.processing"
                        >
                            Cancel Booking
                        </Button>
                    </AlertDialogTrigger>

                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Are you absolutely sure?
                            </AlertDialogTitle>

                            <AlertDialogDescription>
                                This will cancel booking
                                {{ booking.booking_number }}. You can only
                                cancel while the booking is pending. This action
                                cannot be undone.
                            </AlertDialogDescription>
                        </AlertDialogHeader>

                        <AlertDialogFooter>
                            <AlertDialogCancel
                                :disabled="cancelForm.processing"
                            >
                                Keep Booking
                            </AlertDialogCancel>

                            <AlertDialogAction
                                class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                :disabled="cancelForm.processing"
                                @click.prevent="cancelBooking"
                            >
                                {{
                                    cancelForm.processing
                                        ? 'Cancelling...'
                                        : 'Yes, Cancel Booking'
                                }}
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </CardContent>
        </Card>

        <div class="flex flex-wrap gap-3">
            <Button variant="outline" as-child>
                <Link href="/member/bookings"> My Bookings </Link>
            </Button>

            <Button v-if="booking.status === 'pending'" as-child>
                <Link href="/member/laundry-services">
                    Browse More Services
                </Link>
            </Button>
        </div>
    </main>
</template>
