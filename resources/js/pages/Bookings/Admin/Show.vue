<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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
    service_id: number | null;
    service_name: string;
    pricing_unit: string;
    estimated_quantity: string | number;
    estimated_unit_price: string | number;
    estimated_subtotal: string | number;
    notes: string | null;
}

interface Booking {
    id: number;
    booking_number: string;
    customer_id: number;
    pickup_address: string;
    mobile_number: string;
    preferred_pickup_at: string;
    customer_notes: string | null;
    status: 'pending' | 'confirmed' | 'rejected' | 'cancelled';
    confirmed_at: string | null;
    rejection_reason: string | null;
    rejected_at: string | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
    customer: {
        id: number;
        name: string;
        email: string;
    } | null;
    items: BookingItem[];
}

const props = defineProps<{
    booking: Booking;
}>();

// Confirmation and rejection have separate form states.
const confirmForm = useForm({});
const rejectForm = useForm({
    rejection_reason: '',
});

const isPending = computed(() => props.booking.status === 'pending');

const estimatedTotal = computed(() =>
    props.booking.items.reduce(
        (total, item) => total + Number(item.estimated_subtotal),
        0,
    ),
);

const money = (value: string | number) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(Number(value));

const dateTime = (value: string | null) => {
    if (!value) return '—';

    const date = new Date(value);

    return Number.isNaN(date.getTime())
        ? value
        : new Intl.DateTimeFormat('en-PH', {
              dateStyle: 'medium',
              timeStyle: 'short',
          }).format(date);
};

const statusLabel: Record<Booking['status'], string> = {
    pending: 'Pending review',
    confirmed: 'Confirmed',
    rejected: 'Rejected',
    cancelled: 'Cancelled',
};

function confirmBooking() {
    if (!isPending.value || confirmForm.processing) return;

    confirmForm.post(`/admin/bookings/${props.booking.id}/confirm`);
}

function rejectBooking() {
    if (!isPending.value || rejectForm.processing) return;

    rejectForm.post(`/admin/bookings/${props.booking.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => rejectForm.reset(),
    });
}
</script>

<template>
    <Head :title="`Booking ${booking.booking_number}`" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Page heading -->
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div class="space-y-1">
                <Button as-child variant="ghost" class="-ml-3">
                    <Link href="/admin/bookings">
                        ← Back to booking requests
                    </Link>
                </Button>

                <h1 class="text-2xl font-semibold tracking-tight">
                    Booking Details
                </h1>

                <p class="text-sm text-muted-foreground">
                    Review the member's request before making a decision.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span
                    class="inline-flex rounded-full px-3 py-1 text-sm font-medium"
                    :class="{
                        'bg-yellow-100 text-yellow-800':
                            booking.status === 'pending',
                        'bg-green-100 text-green-800':
                            booking.status === 'confirmed',
                        'bg-red-100 text-red-800':
                            booking.status === 'rejected',
                        'bg-muted text-muted-foreground':
                            booking.status === 'cancelled',
                    }"
                >
                    {{ statusLabel[booking.status] }}
                </span>
            </div>
        </div>

        <!-- Confirmation feedback -->
        <div
            v-if="confirmForm.errors.booking"
            role="alert"
            class="rounded-lg border border-destructive/30 bg-destructive/5 p-4 text-sm text-destructive"
        >
            {{ confirmForm.errors.booking }}
        </div>

        <!-- Rejection feedback -->
        <div
            v-if="rejectForm.errors.booking"
            role="alert"
            class="rounded-lg border border-destructive/30 bg-destructive/5 p-4 text-sm text-destructive"
        >
            {{ rejectForm.errors.booking }}
        </div>

        <!-- Booking reference -->
        <Card>
            <CardHeader>
                <CardDescription>Booking reference</CardDescription>
                <CardTitle class="text-xl">
                    {{ booking.booking_number }}
                </CardTitle>
            </CardHeader>

            <CardContent class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-muted-foreground">Booking ID</p>
                    <p class="font-medium">{{ booking.id }}</p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Requested pickup
                    </p>
                    <p class="font-medium">
                        {{ dateTime(booking.preferred_pickup_at) }}
                    </p>
                </div>

                <div v-if="booking.confirmed_at">
                    <p class="text-sm text-muted-foreground">Confirmed at</p>
                    <p class="font-medium">
                        {{ dateTime(booking.confirmed_at) }}
                    </p>
                </div>

                <div v-if="booking.rejected_at">
                    <p class="text-sm text-muted-foreground">Rejected at</p>
                    <p class="font-medium">
                        {{ dateTime(booking.rejected_at) }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Member information -->
        <Card>
            <CardHeader>
                <CardTitle>Member information</CardTitle>
                <CardDescription>
                    The customer who submitted this request.
                </CardDescription>
            </CardHeader>

            <CardContent
                v-if="booking.customer"
                class="grid gap-4 sm:grid-cols-2"
            >
                <div>
                    <p class="text-sm text-muted-foreground">Name</p>
                    <p class="font-medium">
                        {{ booking.customer.name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">Email</p>
                    <p class="font-medium break-all">
                        {{ booking.customer.email }}
                    </p>
                </div>
            </CardContent>

            <CardContent v-else>
                <p class="text-sm text-muted-foreground">
                    Member information is unavailable.
                </p>
            </CardContent>
        </Card>

        <!-- Pickup information -->
        <Card>
            <CardHeader>
                <CardTitle>Pickup information</CardTitle>
            </CardHeader>

            <CardContent class="space-y-4">
                <div>
                    <p class="text-sm text-muted-foreground">Pickup address</p>
                    <p class="font-medium whitespace-pre-line">
                        {{ booking.pickup_address }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">Mobile Number</p>
                    <p class="font-medium whitespace-pre-line">
                        {{ booking.mobile_number }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Preferred pickup date and time
                    </p>
                    <p class="font-medium">
                        {{ dateTime(booking.preferred_pickup_at) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">Member notes</p>
                    <p class="whitespace-pre-line">
                        {{ booking.customer_notes || 'No additional notes.' }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Requested services -->
        <Card>
            <CardHeader>
                <CardTitle>Requested services</CardTitle>
                <CardDescription>
                    Prices and quantities are estimates submitted with the
                    booking.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <div v-if="booking.items.length" class="space-y-4">
                    <div
                        v-for="item in booking.items"
                        :key="item.id"
                        class="flex flex-col justify-between gap-3 border-b pb-4 last:border-0 last:pb-0 sm:flex-row"
                    >
                        <div class="space-y-1">
                            <p class="font-medium">
                                {{ item.service_name }}
                            </p>

                            <p class="text-sm text-muted-foreground">
                                {{ item.estimated_quantity }}
                                {{ item.pricing_unit }}
                                ×
                                {{ money(item.estimated_unit_price) }}
                                per {{ item.pricing_unit }}
                            </p>

                            <p
                                v-if="item.notes"
                                class="text-sm text-muted-foreground"
                            >
                                Notes: {{ item.notes }}
                            </p>
                        </div>

                        <p class="font-semibold sm:text-right">
                            {{ money(item.estimated_subtotal) }}
                        </p>
                    </div>

                    <div
                        class="flex items-center justify-between border-t pt-4"
                    >
                        <span class="font-semibold">Estimated total</span>
                        <span class="text-lg font-bold">
                            {{ money(estimatedTotal) }}
                        </span>
                    </div>
                </div>

                <p v-else class="text-sm text-muted-foreground">
                    This booking has no service items.
                </p>
            </CardContent>
        </Card>

        <!-- Rejection or cancellation information -->
        <Card
            v-if="
                booking.status === 'rejected' || booking.status === 'cancelled'
            "
        >
            <CardHeader>
                <CardTitle>
                    {{
                        booking.status === 'rejected'
                            ? 'Rejection information'
                            : 'Cancellation information'
                    }}
                </CardTitle>
            </CardHeader>

            <CardContent>
                <p class="text-sm whitespace-pre-line">
                    {{
                        booking.status === 'rejected'
                            ? booking.rejection_reason || 'No reason provided.'
                            : booking.cancellation_reason ||
                              'No reason provided.'
                    }}
                </p>
            </CardContent>
        </Card>

        <!-- Admin review actions -->
        <Card v-if="isPending">
            <CardHeader>
                <CardTitle>Review decision</CardTitle>
                <CardDescription>
                    Confirming accepts this request and creates its associated
                    laundry order. Rejecting requires a reason and does not
                    create an order.
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-6">
                <!-- Confirm booking -->
                <div class="flex flex-col gap-3">
                    <h3 class="font-medium">Confirm request</h3>

                    <p class="text-sm text-muted-foreground">
                        Verify the requested services and pickup details before
                        accepting this booking.
                    </p>

                    <div>
                        <Button
                            type="button"
                            :disabled="
                                confirmForm.processing ||
                                rejectForm.processing ||
                                booking.items.length === 0
                            "
                            @click="confirmBooking"
                        >
                            {{
                                confirmForm.processing
                                    ? 'Confirming...'
                                    : 'Confirm Booking'
                            }}
                        </Button>
                    </div>
                </div>

                <div class="border-t" />

                <!-- Reject booking -->
                <form class="space-y-3" @submit.prevent="rejectBooking">
                    <div class="space-y-1">
                        <h3 class="font-medium">Reject request</h3>

                        <p class="text-sm text-muted-foreground">
                            Provide a reason so the member understands why this
                            booking cannot be accepted.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="rejection_reason"
                            class="text-sm font-medium"
                        >
                            Rejection reason
                            <span class="text-destructive">*</span>
                        </label>

                        <textarea
                            id="rejection_reason"
                            v-model="rejectForm.rejection_reason"
                            required
                            minlength="5"
                            maxlength="1000"
                            rows="4"
                            placeholder="Explain why this booking cannot be accepted..."
                            :disabled="
                                rejectForm.processing || confirmForm.processing
                            "
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm outline-none placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            :aria-invalid="
                                Boolean(rejectForm.errors.rejection_reason)
                            "
                            aria-describedby="rejection-reason-error"
                        />

                        <div class="flex justify-between gap-3">
                            <p
                                v-if="rejectForm.errors.rejection_reason"
                                id="rejection-reason-error"
                                role="alert"
                                class="text-sm text-destructive"
                            >
                                {{ rejectForm.errors.rejection_reason }}
                            </p>

                            <p
                                v-else
                                id="rejection-reason-error"
                                class="text-xs text-muted-foreground"
                            >
                                Required. 5–1000 characters.
                            </p>

                            <span
                                class="shrink-0 text-xs text-muted-foreground"
                            >
                                {{ rejectForm.rejection_reason.length }}/1000
                            </span>
                        </div>
                    </div>

                    <div>
                        <Button
                            type="submit"
                            variant="destructive"
                            :disabled="
                                rejectForm.processing ||
                                confirmForm.processing ||
                                rejectForm.rejection_reason.trim().length < 5 ||
                                rejectForm.rejection_reason.trim().length > 1000
                            "
                        >
                            {{
                                rejectForm.processing
                                    ? 'Rejecting...'
                                    : 'Reject Booking'
                            }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <!-- Booking already processed -->
        <div v-else class="rounded-lg border p-4 text-sm text-muted-foreground">
            This booking is {{ booking.status }}. The review actions are no
            longer available.
        </div>
    </div>
</template>
