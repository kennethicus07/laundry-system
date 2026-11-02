<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

type Pickup = {
    id: number;
    status: string;
    scheduled_at: string | null;
    address_snapshot: string | null;
    started_at: string | null;
    completed_at: string | null;
    outcome_notes: string | null;
    order: {
        id: number;
        order_number: string;
        booking: {
            id: number;
            booking_number: string;
        } | null;
        status: string;
        customer: {
            name: string;
        } | null;
    } | null;
};

type PaginatedPickups = {
    data: Pickup[];
    current_page: number;
    last_page: number;
    total?: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
};

type PickupAction = 'start' | 'complete' | 'missed';

const props = defineProps<{
    pickups: PaginatedPickups;
}>();

const page = usePage();
const notes = reactive<Record<number, string>>({});

const confirmation = reactive<{
    pickup: Pickup | null;
    action: PickupAction;
}>({
    pickup: null,
    action: 'start',
});

const isSubmitting = ref(false);

const flash = computed(() => {
    return page.props.flash as { success?: string; error?: string } | undefined;
});

const validationErrors = computed(() => {
    return page.props.errors as Record<string, string>;
});

const confirmationTitle = computed(() => {
    switch (confirmation.action) {
        case 'start':
            return 'Start Pickup?';
        case 'complete':
            return 'Complete Pickup?';
        case 'missed':
            return 'Mark Pickup as Missed?';
    }
});

const confirmationMessage = computed(() => {
    switch (confirmation.action) {
        case 'start':
            return 'Are you sure you want to start this pickup?';
        case 'complete':
            return 'Confirm that you have physically collected the laundry before completing this pickup.';
        case 'missed':
            return 'Are you sure this pickup could not be completed? Review the notes before continuing.';
    }
});

const confirmationButtonLabel = computed(() => {
    switch (confirmation.action) {
        case 'start':
            return 'Yes, Start Pickup';
        case 'complete':
            return 'Yes, Complete Pickup';
        case 'missed':
            return 'Yes, Mark as Missed';
    }
});

function requestConfirmation(pickup: Pickup, action: PickupAction) {
    if (isSubmitting.value) return;

    confirmation.pickup = pickup;
    confirmation.action = action;
}

function closeConfirmation() {
    if (isSubmitting.value) return;

    confirmation.pickup = null;
}

function confirmAction() {
    const pickup = confirmation.pickup;

    if (!pickup || isSubmitting.value) return;

    const action = confirmation.action;
    const url = `/admin/pickups/${pickup.id}/${action}`;

    const data =
        action === 'missed'
            ? {
                  outcome_notes: notes[pickup.id]?.trim() || null,
              }
            : {};

    router.post(url, data, {
        preserveScroll: true,

        onStart: () => {
            isSubmitting.value = true;
        },

        onSuccess: () => {
            confirmation.pickup = null;
        },

        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function formatDate(value: string | null) {
    if (!value) return 'Not recorded';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return 'Invalid date';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}

function statusClass(status: string) {
    switch (status) {
        case 'scheduled':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300';
        case 'in_progress':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300';
        case 'completed':
            return 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300';
        case 'missed':
            return 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300';
        case 'cancelled':
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
        default:
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
}

function statusLabel(status: string) {
    return status.replaceAll('_', ' ');
}

function visitPage(url: string | null) {
    if (!url) return;

    router.visit(url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Pickup Assignments" />

    <div class="space-y-6 p-6">
        <!-- Page heading -->
        <header>
            <div class="text-sm text-muted-foreground">Pickup Assignments</div>

            <h1 class="mt-2 text-2xl font-semibold tracking-tight">
                Pickup Assignments
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                View and manage laundry pickups assigned to you.
            </p>
        </header>

        <!-- Success message -->
        <div
            v-if="flash?.success"
            class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-300"
            role="status"
        >
            {{ flash.success }}
        </div>

        <!-- General error message -->
        <div
            v-if="flash?.error"
            class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300"
            role="alert"
        >
            {{ flash.error }}
        </div>

        <!-- Action validation error -->
        <div
            v-if="validationErrors.pickup"
            class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300"
            role="alert"
        >
            {{ validationErrors.pickup }}
        </div>

        <!-- Empty state -->
        <div
            v-if="pickups.data.length === 0"
            class="rounded-lg border p-8 text-center"
        >
            <h2 class="font-medium">No pickup assignments</h2>

            <p class="mt-2 text-sm text-muted-foreground">
                Your assigned pickups will appear here when they are scheduled.
            </p>
        </div>

        <!-- Pickup list -->
        <div v-else class="space-y-4">
            <article
                v-for="pickup in pickups.data"
                :key="pickup.id"
                class="space-y-4 rounded-lg border p-5"
            >
                <!-- Order and status -->
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <h2 class="font-semibold">
                            {{
                                pickup.order?.order_number ??
                                `Pickup #${pickup.id}`
                            }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Booking Number:
                            <span class="font-medium text-foreground">
                                {{
                                    pickup.order?.booking?.booking_number ??
                                    'N/A'
                                }}
                            </span>
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Customer:
                            {{
                                pickup.order?.customer?.name ??
                                'Customer unavailable'
                            }}
                        </p>

                        <p
                            v-if="pickup.order"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            Order status:
                            {{ statusLabel(pickup.order.status) }}
                        </p>
                    </div>

                    <span
                        class="w-fit rounded-full px-3 py-1 text-xs font-medium capitalize"
                        :class="statusClass(pickup.status)"
                    >
                        {{ statusLabel(pickup.status) }}
                    </span>
                </div>

                <!-- Pickup information -->
                <div class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <p class="font-medium">Scheduled pickup</p>

                        <p class="mt-1 text-muted-foreground">
                            {{ formatDate(pickup.scheduled_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="font-medium">Pickup address</p>

                        <p
                            class="mt-1 whitespace-pre-line text-muted-foreground"
                        >
                            {{
                                pickup.address_snapshot || 'No address recorded'
                            }}
                        </p>
                    </div>

                    <div v-if="pickup.started_at">
                        <p class="font-medium">Started</p>

                        <p class="mt-1 text-muted-foreground">
                            {{ formatDate(pickup.started_at) }}
                        </p>
                    </div>

                    <div v-if="pickup.completed_at">
                        <p class="font-medium">Completed</p>

                        <p class="mt-1 text-muted-foreground">
                            {{ formatDate(pickup.completed_at) }}
                        </p>
                    </div>
                </div>

                <!-- Actions for active pickups -->
                <div
                    v-if="
                        pickup.status === 'scheduled' ||
                        pickup.status === 'in_progress'
                    "
                    class="space-y-3 border-t pt-4"
                >
                    <div>
                        <label
                            :for="`notes-${pickup.id}`"
                            class="block text-sm font-medium"
                        >
                            Notes (if pickup cannot be completed)
                        </label>

                        <textarea
                            :id="`notes-${pickup.id}`"
                            v-model="notes[pickup.id]"
                            rows="2"
                            maxlength="1000"
                            placeholder="For example: customer unavailable..."
                            class="mt-2 w-full rounded-md border bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                        />

                        <p class="mt-1 text-xs text-muted-foreground">
                            Optional. Up to 1,000 characters.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="pickup.status === 'scheduled'"
                            type="button"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="isSubmitting"
                            @click="requestConfirmation(pickup, 'start')"
                        >
                            Start Pickup
                        </button>

                        <button
                            v-if="pickup.status === 'in_progress'"
                            type="button"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="isSubmitting"
                            @click="requestConfirmation(pickup, 'complete')"
                        >
                            Complete Pickup
                        </button>

                        <button
                            type="button"
                            class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950"
                            :disabled="isSubmitting"
                            @click="requestConfirmation(pickup, 'missed')"
                        >
                            Mark as Missed
                        </button>
                    </div>
                </div>

                <!-- Outcome notes -->
                <div
                    v-if="pickup.outcome_notes"
                    class="rounded-md bg-muted p-3 text-sm"
                >
                    <p class="font-medium">Outcome notes</p>

                    <p class="mt-1 whitespace-pre-wrap text-muted-foreground">
                        {{ pickup.outcome_notes }}
                    </p>
                </div>
            </article>
        </div>

        <!-- Pagination -->
        <nav
            v-if="pickups.last_page > 1"
            class="flex flex-wrap gap-2"
            aria-label="Pickup pagination"
        >
            <template
                v-for="(link, index) in pickups.links"
                :key="`${index}-${link.label}`"
            >
                <button
                    v-if="link.url"
                    type="button"
                    class="rounded-md border px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                    :class="{
                        'bg-muted font-semibold': link.active,
                    }"
                    :disabled="link.active"
                    @click="visitPage(link.url)"
                    v-html="link.label"
                />

                <span
                    v-else
                    class="rounded-md border px-3 py-2 text-sm text-muted-foreground opacity-50"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>

    <!-- Confirmation modal -->
    <Teleport to="body">
        <div
            v-if="confirmation.pickup"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
            @click.self="closeConfirmation"
            @keydown.esc="closeConfirmation"
        >
            <section
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="confirmation-title"
                aria-describedby="confirmation-description"
                class="w-full max-w-md space-y-5 rounded-xl border bg-background p-6 shadow-xl"
            >
                <div class="space-y-2">
                    <h2 id="confirmation-title" class="text-lg font-semibold">
                        {{ confirmationTitle }}
                    </h2>

                    <p
                        id="confirmation-description"
                        class="text-sm text-muted-foreground"
                    >
                        {{ confirmationMessage }}
                    </p>
                </div>

                <div class="rounded-lg bg-muted p-4 text-sm">
                    <p class="font-medium">
                        Order:
                        {{
                            confirmation.pickup.order?.order_number ??
                            `Pickup #${confirmation.pickup.id}`
                        }}
                    </p>
                    <p class="mt-1 text-muted-foreground">
                        Booking Number:
                        <span class="font-medium text-foreground">
                            {{
                                confirmation.pickup.order?.booking
                                    ?.booking_number ?? 'N/A'
                            }}
                        </span>
                    </p>

                    <p class="mt-1 text-muted-foreground">
                        Customer:
                        {{
                            confirmation.pickup.order?.customer?.name ??
                            'Customer unavailable'
                        }}
                    </p>

                    <p class="mt-2 text-muted-foreground">
                        Scheduled:
                        {{ formatDate(confirmation.pickup.scheduled_at) }}
                    </p>

                    <div
                        v-if="confirmation.action === 'missed'"
                        class="mt-3 border-t pt-3"
                    >
                        <p class="font-medium">Outcome notes</p>

                        <p
                            class="mt-1 whitespace-pre-wrap text-muted-foreground"
                        >
                            {{
                                notes[confirmation.pickup.id]?.trim() ||
                                'No notes provided.'
                            }}
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                >
                    <button
                        type="button"
                        class="rounded-md border px-4 py-2 text-sm font-medium hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isSubmitting"
                        @click="closeConfirmation"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isSubmitting"
                        @click="confirmAction"
                    >
                        {{
                            isSubmitting
                                ? 'Processing...'
                                : confirmationButtonLabel
                        }}
                    </button>
                </div>
            </section>
        </div>
    </Teleport>
</template>
