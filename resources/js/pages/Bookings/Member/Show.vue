<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
