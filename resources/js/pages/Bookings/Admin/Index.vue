<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface BookingItem {
    id: number;
    booking_id: number;
    service_name: string;
    estimated_quantity: string | number;
    pricing_unit: string;
    estimated_subtotal: string | number;
}

interface Booking {
    id: number;
    booking_number: string;
    status: 'pending' | 'confirmed' | 'rejected' | 'cancelled';
    preferred_pickup_at: string;
    created_at: string;
    customer: {
        id: number;
        name: string;
        email: string;
    } | null;
    items: BookingItem[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedBookings {
    data: Booking[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

const props = defineProps<{
    bookings: PaginatedBookings;
    filters: {
        status: string;
        search: string;
    };
}>();

const search = ref(props.filters.search);
const status = ref(props.filters.status);

watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search;
        status.value = filters.status;
    },
);

function applyFilters() {
    router.get(
        '/admin/bookings',
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function clearFilters() {
    search.value = '';
    status.value = '';
    applyFilters();
}

function money(value: string | number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(value));
}

function dateTime(value: string) {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return value;

    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}

function statusClass(value: Booking['status']) {
    return {
        'bg-yellow-100 text-yellow-800': value === 'pending',
        'bg-green-100 text-green-800': value === 'confirmed',
        'bg-red-100 text-red-800': value === 'rejected',
        'bg-muted text-muted-foreground': value === 'cancelled',
    };
}

function estimatedTotal(booking: Booking) {
    return booking.items.reduce(
        (total, item) => total + Number(item.estimated_subtotal),
        0,
    );
}
</script>

<template>
    <Head title="Booking Requests" />

    <div class="space-y-6 p-4 md:p-6">
        <div
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Booking Requests
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Review and manage laundry bookings submitted by members.
                </p>
            </div>

            <div class="text-sm text-muted-foreground">
                {{ bookings.total }} booking(s)
            </div>
        </div>

        <!-- Filters -->
        <Card>
            <CardHeader>
                <CardTitle>Find bookings</CardTitle>
                <CardDescription>
                    Search by booking number, member name, or email.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <form
                    class="grid gap-3 md:grid-cols-[1fr_220px_auto_auto]"
                    @submit.prevent="applyFilters"
                >
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search bookings or members..."
                        class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-ring"
                    />

                    <select
                        v-model="status"
                        class="h-10 rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-ring"
                    >
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <Button type="submit">Apply filters</Button>

                    <Button
                        type="button"
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                </form>
            </CardContent>
        </Card>

        <!-- Booking table -->
        <Card>
            <CardHeader>
                <CardTitle>All bookings</CardTitle>
                <CardDescription>
                    Select a booking to inspect its details and available
                    actions.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <div v-if="bookings.data.length" class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left text-sm">
                        <thead>
                            <tr class="border-b text-muted-foreground">
                                <th class="px-3 py-3 font-medium">Booking</th>
                                <th class="px-3 py-3 font-medium">Member</th>
                                <th class="px-3 py-3 font-medium">Services</th>
                                <th class="px-3 py-3 font-medium">
                                    Est. total
                                </th>
                                <th class="px-3 py-3 font-medium">Pickup</th>
                                <th class="px-3 py-3 font-medium">Status</th>
                                <th class="px-3 py-3 text-right font-medium">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="booking in bookings.data"
                                :key="booking.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-3 py-4">
                                    <Link
                                        :href="`/admin/bookings/${booking.id}`"
                                        class="font-medium text-primary hover:underline"
                                    >
                                        {{ booking.booking_number }}
                                    </Link>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Created
                                        {{ dateTime(booking.created_at) }}
                                    </p>
                                </td>

                                <td class="px-3 py-4">
                                    <p class="font-medium">
                                        {{
                                            booking.customer?.name ??
                                            'Unavailable'
                                        }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ booking.customer?.email ?? '—' }}
                                    </p>
                                </td>

                                <td class="px-3 py-4">
                                    <p class="font-medium">
                                        {{ booking.items.length }}
                                        item(s)
                                    </p>
                                    <p
                                        class="mt-1 max-w-48 truncate text-xs text-muted-foreground"
                                    >
                                        {{
                                            booking.items
                                                .map(
                                                    (item) => item.service_name,
                                                )
                                                .join(', ') || 'No items'
                                        }}
                                    </p>
                                </td>

                                <td class="px-3 py-4 font-medium">
                                    {{ money(estimatedTotal(booking)) }}
                                </td>

                                <td class="px-3 py-4">
                                    {{ dateTime(booking.preferred_pickup_at) }}
                                </td>

                                <td class="px-3 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                        :class="statusClass(booking.status)"
                                    >
                                        {{ booking.status }}
                                    </span>
                                </td>

                                <td class="px-3 py-4 text-right">
                                    <Button
                                        as-child
                                        size="sm"
                                        variant="outline"
                                    >
                                        <Link
                                            :href="`/admin/bookings/${booking.id}`"
                                        >
                                            Review
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center gap-2 py-12 text-center"
                >
                    <h3 class="font-semibold">No bookings found</h3>
                    <p class="max-w-md text-sm text-muted-foreground">
                        There are no bookings matching your current filters. Try
                        a different search or status.
                    </p>
                    <Button variant="outline" @click="clearFilters">
                        Clear filters
                    </Button>
                </div>

                <!-- Pagination -->
                <div
                    v-if="bookings.last_page > 1"
                    class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t pt-4"
                >
                    <p class="text-sm text-muted-foreground">
                        Showing {{ bookings.from ?? 0 }}–{{
                            bookings.to ?? 0
                        }}
                        of {{ bookings.total }}
                    </p>

                    <nav class="flex flex-wrap gap-1" aria-label="Pagination">
                        <template
                            v-for="(link, index) in bookings.links"
                            :key="index"
                        >
                            <Button
                                v-if="link.url"
                                as-child
                                size="sm"
                                :variant="link.active ? 'default' : 'outline'"
                            >
                                <Link
                                    :href="link.url"
                                    preserve-scroll
                                    v-html="link.label"
                                />
                            </Button>

                            <Button
                                v-else
                                size="sm"
                                variant="outline"
                                disabled
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
