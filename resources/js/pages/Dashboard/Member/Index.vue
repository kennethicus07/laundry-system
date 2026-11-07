<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import {
    ClipboardList,
    WashingMachine,
    CalendarCheck,
    CircleCheck,
    ArrowRight,
} from 'lucide-vue-next';

interface Booking {
    id: number;
    booking_number: string;
    status: string;
    preferred_pickup_at: string | null;
    created_at: string;
}

interface LaundryOrder {
    id: number;
    order_number: string;
    status: string;
    ready_at: string | null;
    created_at: string;
}

interface BookingSummary {
    total: number;
    pending: number;
    confirmed: number;
    rejected: number;
    cancelled: number;
    recent: Booking[];
}

interface OrderSummary {
    total: number;
    active: number;
    completed: number;
    recent: LaundryOrder[];
}

interface DashboardData {
    bookings: BookingSummary;
    orders: OrderSummary;
}

const props = defineProps<{
    customerName: string;
    dashboard: DashboardData;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/member/dashboard',
            },
        ],
    },
});

function formatDate(date: string | null): string {
    if (!date) {
        return 'Not scheduled';
    }

    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(date));
}

function formatStatus(status: string): string {
    return status
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (character) => character.toUpperCase());
}
</script>

<template>
    <Head title="Member Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
        <!-- Welcome section -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight">
                Welcome back, {{ props.customerName }}!
            </h1>

            <p class="text-muted-foreground">
                Here's an overview of your laundry bookings and orders.
            </p>
        </div>

        <!-- Summary cards -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Total Bookings -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Total Bookings
                    </CardTitle>
                    <ClipboardList class="size-5 text-muted-foreground" />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ props.dashboard.bookings.total }}
                    </div>

                    <p class="text-xs text-muted-foreground">
                        All your booking requests
                    </p>
                </CardContent>
            </Card>

            <!-- Active Orders -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Active Orders
                    </CardTitle>
                    <WashingMachine class="size-5 text-muted-foreground" />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ props.dashboard.orders.active }}
                    </div>

                    <p class="text-xs text-muted-foreground">
                        Orders currently in progress
                    </p>
                </CardContent>
            </Card>

            <!-- Confirmed Bookings -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Confirmed Bookings
                    </CardTitle>
                    <CalendarCheck class="size-5 text-muted-foreground" />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ props.dashboard.bookings.confirmed }}
                    </div>

                    <p class="text-xs text-muted-foreground">
                        Bookings confirmed by staff
                    </p>
                </CardContent>
            </Card>

            <!-- Completed Orders -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Completed Orders
                    </CardTitle>
                    <CircleCheck class="size-5 text-muted-foreground" />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ props.dashboard.orders.completed }}
                    </div>

                    <p class="text-xs text-muted-foreground">
                        Successfully completed orders
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Quick actions -->
        <div>
            <h2 class="mb-3 text-lg font-semibold">Quick Actions</h2>

            <div class="flex flex-wrap gap-3">
                <Link
                    href="/member/laundry-services"
                    class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-opacity hover:opacity-90"
                >
                    Browse Laundry Services →
                </Link>

                <Link
                    href="/member/bookings"
                    class="inline-flex items-center rounded-md border px-4 py-2 text-sm font-medium transition-colors hover:bg-accent"
                >
                    View My Bookings
                </Link>
            </div>
        </div>

        <!-- Recent activity -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Recent Bookings -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <CardTitle>Recent Bookings</CardTitle>
                            <CardDescription>
                                Your latest booking requests.
                            </CardDescription>
                        </div>

                        <Link
                            href="/member/bookings"
                            class="text-sm font-medium text-primary hover:underline"
                        >
                            View all
                        </Link>
                    </div>
                </CardHeader>

                <CardContent>
                    <div
                        v-if="props.dashboard.bookings.recent.length > 0"
                        class="space-y-4"
                    >
                        <div
                            v-for="booking in props.dashboard.bookings.recent"
                            :key="booking.id"
                            class="flex items-center justify-between gap-3 border-b pb-4 last:border-0 last:pb-0"
                        >
                            <div class="min-w-0">
                                <Link
                                    :href="`/member/bookings/${booking.id}`"
                                    class="font-medium hover:text-primary hover:underline"
                                >
                                    {{ booking.booking_number }}
                                </Link>

                                <p class="text-sm text-muted-foreground">
                                    Pickup:
                                    {{
                                        formatDate(booking.preferred_pickup_at)
                                    }}
                                </p>
                            </div>

                            <span
                                class="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
                            >
                                {{ formatStatus(booking.status) }}
                            </span>
                        </div>
                    </div>

                    <div v-else class="py-6 text-center">
                        <p class="font-medium">No bookings yet.</p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Start by browsing our laundry services.
                        </p>

                        <Link
                            href="/member/laundry-services"
                            class="mt-3 inline-block text-sm font-medium text-primary hover:underline"
                        >
                            Browse Services →
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Orders -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Laundry Orders</CardTitle>

                    <CardDescription>
                        Track the progress of your laundry orders.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div
                        v-if="props.dashboard.orders.recent.length > 0"
                        class="space-y-4"
                    >
                        <div
                            v-for="order in props.dashboard.orders.recent"
                            :key="order.id"
                            class="flex items-center justify-between gap-3 border-b pb-4 last:border-0 last:pb-0"
                        >
                            <div class="min-w-0">
                                <p class="font-medium">
                                    {{ order.order_number }}
                                </p>

                                <p class="text-sm text-muted-foreground">
                                    Ready:
                                    {{ formatDate(order.ready_at) }}
                                </p>
                            </div>

                            <span
                                class="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
                            >
                                {{ formatStatus(order.status) }}
                            </span>
                        </div>
                    </div>

                    <div v-else class="py-6 text-center">
                        <p class="font-medium">No laundry orders yet.</p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Your orders will appear here once your booking is
                            processed.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
