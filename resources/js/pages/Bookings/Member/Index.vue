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
    service_name: string;
    pricing_unit: string;
    estimated_quantity: string;
    estimated_unit_price: string;
    estimated_subtotal: string;
}

interface Booking {
    id: number;
    booking_number: string;
    status: 'pending' | 'confirmed' | 'rejected' | 'cancelled';
    pickup_address: string;
    preferred_pickup_at: string | null;
    created_at: string;
    rejection_reason?: string | null;
    cancellation_reason?: string | null;
    items: BookingItem[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedBookings {
    data: Booking[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'My Bookings',
                href: '/member/bookings',
            },
        ],
    },
});

defineProps<{
    bookings: PaginatedBookings;
}>();

function money(value: string | number): string {
    return Number(value).toLocaleString('en-PH', {
        style: 'currency',
        currency: 'PHP',
    });
}

function dateTime(value: string): string {
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

function estimatedTotal(booking: Booking): number {
    return booking.items.reduce(
        (total, item) => total + Number(item.estimated_subtotal),
        0,
    );
}
</script>

<template>
    <Head title="My Bookings" />

    <main class="mx-auto max-w-6xl space-y-6 p-6">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">My Bookings</h1>
                <p class="text-sm text-muted-foreground">
                    View your laundry requests and track their status.
                </p>
            </div>

            <Button as-child>
                <Link href="/member/laundry-services"> Book a Service </Link>
            </Button>
        </div>

        <Card v-if="bookings.data.length === 0">
            <CardContent
                class="flex flex-col items-center gap-3 py-12 text-center"
            >
                <h2 class="text-lg font-semibold">No bookings yet</h2>

                <p class="max-w-md text-sm text-muted-foreground">
                    Your laundry bookings will appear here after you submit your
                    first request.
                </p>

                <Button as-child>
                    <Link href="/member/laundry-services">
                        Browse Laundry Services
                    </Link>
                </Button>
            </CardContent>
        </Card>

        <div v-else class="space-y-4">
            <Card v-for="booking in bookings.data" :key="booking.id">
                <CardHeader>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="space-y-1">
                            <CardTitle class="text-lg">
                                {{ booking.booking_number }}
                            </CardTitle>

                            <CardDescription>
                                Submitted {{ dateTime(booking.created_at) }}
                            </CardDescription>
                        </div>

                        <span
                            class="w-fit rounded-full px-3 py-1 text-xs font-medium"
                            :class="statusClass(booking.status)"
                        >
                            {{ statusLabel(booking.status) }}
                        </span>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <h3 class="text-sm font-semibold">
                            Requested services
                        </h3>

                        <div
                            v-for="item in booking.items"
                            :key="item.id"
                            class="flex flex-col gap-1 border-b pb-3 last:border-0 last:pb-0 sm:flex-row sm:justify-between"
                        >
                            <div>
                                <p class="font-medium">
                                    {{ item.service_name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ item.estimated_quantity }}
                                    {{ item.pricing_unit }}
                                    × {{ money(item.estimated_unit_price) }}
                                </p>
                            </div>

                            <p class="font-medium">
                                {{ money(item.estimated_subtotal) }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid gap-4 rounded-lg bg-muted/40 p-4 sm:grid-cols-2"
                    >
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Preferred pickup
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                {{
                                    booking.preferred_pickup_at
                                        ? dateTime(booking.preferred_pickup_at)
                                        : 'Not specified'
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Pickup address
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                {{ booking.pickup_address }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Estimated total
                            </p>

                            <p class="mt-1 text-lg font-semibold">
                                {{ money(estimatedTotal(booking)) }}
                            </p>
                        </div>
                    </div>
                    <p
                        v-if="
                            booking.status === 'rejected' &&
                            booking.rejection_reason
                        "
                        class="text-sm text-destructive"
                    >
                        Reason: {{ booking.rejection_reason }}
                    </p>

                    <p
                        v-if="
                            booking.status === 'cancelled' &&
                            booking.cancellation_reason
                        "
                        class="text-sm text-muted-foreground"
                    >
                        Cancellation reason: {{ booking.cancellation_reason }}
                    </p>

                    <div class="flex justify-end">
                        <Button variant="outline" as-child>
                            <Link :href="`/member/bookings/${booking.id}`">
                                View Booking
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <nav
                v-if="bookings.last_page > 1"
                class="flex flex-wrap items-center justify-center gap-2 pt-2"
                aria-label="Bookings pagination"
            >
                <template
                    v-for="(link, index) in bookings.links"
                    :key="`${index}-${link.label}`"
                >
                    <Button
                        v-if="link.url"
                        variant="outline"
                        size="sm"
                        as-child
                        :class="link.active ? 'border-primary bg-muted' : ''"
                    >
                        <Link
                            :href="link.url"
                            preserve-scroll
                            v-html="link.label"
                        />
                    </Button>

                    <Button v-else variant="outline" size="sm" disabled>
                        <span v-html="link.label" />
                    </Button>
                </template>
            </nav>

            <p class="text-center text-xs text-muted-foreground">
                Showing {{ bookings.data.length }} of
                {{ bookings.total }} bookings
            </p>
        </div>
    </main>
</template>
