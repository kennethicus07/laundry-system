<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import { CalendarDays, Package, Wallet, CreditCard } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
            },
        ],
    },
});

interface DashboardData {
    bookings: {
        total: number;
        pending: number;
        confirmed: number;
        rejected: number;
        cancelled: number;
    };
    orders: {
        total: number;
        awaiting_pickup: number;
        received: number;
        processing: number;
        ready_for_delivery: number;
        completed: number;
        cancelled: number;
    };
    billing: {
        completed_payments_count: number;
        completed_payments_amount: string;
        pending_verification_count: number;
        pending_verification_amount: string;
        unpaid_invoices_count: number;
        completed_refunds_count: number;
        completed_refunds_amount: string;
    };
}

const props = defineProps<{
    dashboard: DashboardData;
}>();

const formatCurrency = (value: string | number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(value));
};

const summaryCards = [
    {
        title: 'Total Bookings',
        value: () => props.dashboard.bookings.total,
        description: `${props.dashboard.bookings.pending} pending requests`,
        icon: CalendarDays,
    },
    {
        title: 'Total Orders',
        value: () => props.dashboard.orders.total,
        description: `${props.dashboard.orders.processing} currently processing`,
        icon: Package,
    },
    {
        title: 'Completed Payments',
        value: () =>
            formatCurrency(props.dashboard.billing.completed_payments_amount),
        description: `${props.dashboard.billing.completed_payments_count} completed payments`,
        icon: Wallet,
    },
    {
        title: 'Pending Verification',
        value: () =>
            formatCurrency(props.dashboard.billing.pending_verification_amount),
        description: `${props.dashboard.billing.pending_verification_count} awaiting verification`,
        icon: CreditCard,
    },
];

const orderStatuses = [
    { label: 'Awaiting Pickup', value: 'awaiting_pickup' },
    { label: 'Received', value: 'received' },
    { label: 'Processing', value: 'processing' },
    { label: 'Ready for Delivery', value: 'ready_for_delivery' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
] as const;
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Admin Dashboard</h1>

            <p class="text-muted-foreground">
                Overview of your laundry business.
            </p>
        </div>

        <!-- Summary cards -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card v-for="card in summaryCards" :key="card.title">
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        {{ card.title }}
                    </CardTitle>

                    <component
                        :is="card.icon"
                        class="size-4 text-muted-foreground"
                    />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ card.value() }}
                    </div>

                    <p class="text-xs text-muted-foreground">
                        {{ card.description }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Booking overview -->
        <Card>
            <CardHeader>
                <CardTitle>Booking Overview</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Total</p>
                        <p class="text-2xl font-semibold">
                            {{ dashboard.bookings.total }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">Pending</p>
                        <p class="text-2xl font-semibold">
                            {{ dashboard.bookings.pending }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">Confirmed</p>
                        <p class="text-2xl font-semibold">
                            {{ dashboard.bookings.confirmed }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">
                            Rejected / Cancelled
                        </p>
                        <p class="text-2xl font-semibold">
                            {{
                                dashboard.bookings.rejected +
                                dashboard.bookings.cancelled
                            }}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Order workflow -->
        <Card>
            <CardHeader>
                <CardTitle>Order Workflow</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="status in orderStatuses"
                        :key="status.value"
                        class="rounded-lg border p-4"
                    >
                        <p class="text-sm text-muted-foreground">
                            {{ status.label }}
                        </p>

                        <p class="mt-1 text-2xl font-semibold">
                            {{ dashboard.orders[status.value] }}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Billing overview -->
        <Card>
            <CardHeader>
                <CardTitle>Billing Overview</CardTitle>
            </CardHeader>

            <CardContent>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Unpaid Invoices
                        </p>
                        <p class="text-2xl font-semibold">
                            {{ dashboard.billing.unpaid_invoices_count }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">
                            Completed Refunds
                        </p>
                        <p class="text-2xl font-semibold">
                            {{
                                formatCurrency(
                                    dashboard.billing.completed_refunds_amount,
                                )
                            }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ dashboard.billing.completed_refunds_count }}
                            refunds
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">
                            Payments Awaiting Verification
                        </p>
                        <p class="text-2xl font-semibold">
                            {{ dashboard.billing.pending_verification_count }}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
