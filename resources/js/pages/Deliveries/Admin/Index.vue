<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, onBeforeUnmount } from 'vue';

type Delivery = {
    id: number;
    status: string;
    scheduled_at: string | null;
    address_snapshot: string | null;
    started_at: string | null;
    completed_at: string | null;
    outcome_notes: string | null;
    photo_path: string | null;
    photo_taken_at: string | null;
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

type PaginatedDeliveries = {
    data: Delivery[];
    current_page: number;
    last_page: number;
    total?: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
};

type DeliveryAction = 'start' | 'complete' | 'missed';

const props = defineProps<{
    deliveries: PaginatedDeliveries;
}>();

const page = usePage();
const notes = reactive<Record<number, string>>({});

const confirmation = reactive<{
    delivery: Delivery | null;
    action: DeliveryAction;
}>({
    delivery: null,
    action: 'start',
});

const isSubmitting = ref(false);

// Photo upload states
const photoFile = ref<File | null>(null);
const photoPreview = ref<string | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);

const flash = computed(() => {
    return page.props.flash as
        | {
              success?: string;
              error?: string;
          }
        | undefined;
});

const validationErrors = computed(() => {
    return page.props.errors as Record<string, string>;
});

const confirmationTitle = computed(() => {
    switch (confirmation.action) {
        case 'start':
            return 'Start Delivery?';
        case 'complete':
            return 'Complete Delivery?';
        case 'missed':
            return 'Mark Delivery as Missed?';
    }
});

const confirmationMessage = computed(() => {
    switch (confirmation.action) {
        case 'start':
            return 'Are you sure you want to start this delivery?';
        case 'complete':
            return 'Confirm that you have physically delivered the laundry to the customer. Upload a delivery photo before completing this delivery.';
        case 'missed':
            return 'Are you sure this delivery could not be completed? Review the notes before continuing.';
    }
});

const confirmationButtonLabel = computed(() => {
    switch (confirmation.action) {
        case 'start':
            return 'Yes, Start Delivery';
        case 'complete':
            return 'Yes, Complete Delivery';
        case 'missed':
            return 'Yes, Mark as Missed';
    }
});

// --------------------------------------------------
// Photo handling
// --------------------------------------------------

function clearPhoto() {
    if (photoPreview.value) {
        URL.revokeObjectURL(photoPreview.value);
    }

    photoFile.value = null;
    photoPreview.value = null;

    if (photoInput.value) {
        photoInput.value.value = '';
    }
}

function selectPhoto() {
    photoInput.value?.click();
}

function handlePhotoChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        clearPhoto();
        target.value = '';

        return;
    }

    // Maximum file size: 5 MB
    if (file.size > 5 * 1024 * 1024) {
        clearPhoto();
        target.value = '';

        return;
    }

    clearPhoto();

    photoFile.value = file;
    photoPreview.value = URL.createObjectURL(file);
}

function removePhoto() {
    clearPhoto();
}

onBeforeUnmount(() => {
    clearPhoto();
});

// --------------------------------------------------
// Confirmation
// --------------------------------------------------

function requestConfirmation(delivery: Delivery, action: DeliveryAction) {
    if (isSubmitting.value) return;

    clearPhoto();

    confirmation.delivery = delivery;
    confirmation.action = action;
}

function closeConfirmation() {
    if (isSubmitting.value) return;

    clearPhoto();
    confirmation.delivery = null;
}

function confirmAction() {
    const delivery = confirmation.delivery;

    if (!delivery || isSubmitting.value) return;

    const action = confirmation.action;
    const url = `/admin/deliveries/${delivery.id}/${action}`;

    // Photo is mandatory for delivery completion.
    if (action === 'complete' && !photoFile.value) {
        return;
    }

    if (action === 'complete') {
        const formData = new FormData();

        formData.append('photo', photoFile.value!);

        router.post(url, formData, {
            forceFormData: true,
            preserveScroll: true,

            onStart: () => {
                isSubmitting.value = true;
            },

            onSuccess: () => {
                clearPhoto();
                confirmation.delivery = null;
            },

            onFinish: () => {
                isSubmitting.value = false;
            },
        });

        return;
    }

    const data =
        action === 'missed'
            ? {
                  outcome_notes: notes[delivery.id]?.trim() || null,
              }
            : {};

    router.post(url, data, {
        preserveScroll: true,

        onStart: () => {
            isSubmitting.value = true;
        },

        onSuccess: () => {
            confirmation.delivery = null;
        },

        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

// --------------------------------------------------
// Formatting
// --------------------------------------------------

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

function photoUrl(path: string) {
    return `/storage/${path}`;
}
</script>

<template>
    <Head title="Delivery Assignments" />

    <div class="space-y-6 p-6">
        <!-- Page heading -->
        <header>
            <div class="text-sm text-muted-foreground">
                Delivery Assignments
            </div>

            <h1 class="mt-2 text-2xl font-semibold tracking-tight">
                Delivery Assignments
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                View and manage laundry deliveries assigned to you.
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
            v-if="validationErrors.delivery"
            class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300"
            role="alert"
        >
            {{ validationErrors.delivery }}
        </div>

        <!-- Empty state -->
        <div
            v-if="deliveries.data.length === 0"
            class="rounded-lg border p-8 text-center"
        >
            <h2 class="font-medium">No delivery assignments</h2>

            <p class="mt-2 text-sm text-muted-foreground">
                Your assigned deliveries will appear here when they are
                scheduled.
            </p>
        </div>

        <!-- Delivery list -->
        <div v-else class="space-y-4">
            <article
                v-for="delivery in deliveries.data"
                :key="delivery.id"
                class="space-y-4 rounded-lg border p-5"
            >
                <!-- Order and status -->
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <h2 class="font-semibold">
                            {{
                                delivery.order?.order_number ??
                                `Delivery #${delivery.id}`
                            }}
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Booking Number:
                            <span class="font-medium text-foreground">
                                {{
                                    delivery.order?.booking?.booking_number ??
                                    'N/A'
                                }}
                            </span>
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Customer:
                            {{
                                delivery.order?.customer?.name ??
                                'Customer unavailable'
                            }}
                        </p>

                        <p
                            v-if="delivery.order"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            Order status:
                            {{ statusLabel(delivery.order.status) }}
                        </p>
                    </div>

                    <span
                        class="w-fit rounded-full px-3 py-1 text-xs font-medium capitalize"
                        :class="statusClass(delivery.status)"
                    >
                        {{ statusLabel(delivery.status) }}
                    </span>
                </div>

                <!-- Delivery information -->
                <div class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <p class="font-medium">Scheduled delivery</p>

                        <p class="mt-1 text-muted-foreground">
                            {{ formatDate(delivery.scheduled_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="font-medium">Delivery address</p>

                        <p
                            class="mt-1 whitespace-pre-line text-muted-foreground"
                        >
                            {{
                                delivery.address_snapshot ||
                                'No address recorded'
                            }}
                        </p>
                    </div>

                    <div v-if="delivery.started_at">
                        <p class="font-medium">Started</p>

                        <p class="mt-1 text-muted-foreground">
                            {{ formatDate(delivery.started_at) }}
                        </p>
                    </div>

                    <div v-if="delivery.completed_at">
                        <p class="font-medium">Completed</p>

                        <p class="mt-1 text-muted-foreground">
                            {{ formatDate(delivery.completed_at) }}
                        </p>
                    </div>
                </div>

                <!-- Saved delivery photo -->
                <div v-if="delivery.photo_path" class="space-y-2 border-t pt-4">
                    <h3 class="text-sm font-semibold">Delivery Proof</h3>

                    <a
                        :href="photoUrl(delivery.photo_path)"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <img
                            :src="photoUrl(delivery.photo_path)"
                            alt="Delivery proof"
                            class="max-h-64 rounded-lg border object-contain"
                        />
                    </a>

                    <p class="text-xs text-muted-foreground">
                        Photo taken:
                        {{ formatDate(delivery.photo_taken_at) }}
                    </p>
                </div>

                <!-- Actions for active deliveries -->
                <div
                    v-if="
                        delivery.status === 'scheduled' ||
                        delivery.status === 'in_progress'
                    "
                    class="space-y-3 border-t pt-4"
                >
                    <div>
                        <label
                            :for="`notes-${delivery.id}`"
                            class="block text-sm font-medium"
                        >
                            Notes (if delivery cannot be completed)
                        </label>

                        <textarea
                            :id="`notes-${delivery.id}`"
                            v-model="notes[delivery.id]"
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
                            v-if="delivery.status === 'scheduled'"
                            type="button"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="isSubmitting"
                            @click="requestConfirmation(delivery, 'start')"
                        >
                            Start Delivery
                        </button>

                        <button
                            v-if="delivery.status === 'in_progress'"
                            type="button"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="isSubmitting"
                            @click="requestConfirmation(delivery, 'complete')"
                        >
                            Complete Delivery
                        </button>

                        <button
                            type="button"
                            class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950"
                            :disabled="isSubmitting"
                            @click="requestConfirmation(delivery, 'missed')"
                        >
                            Mark as Missed
                        </button>
                    </div>
                </div>

                <!-- Outcome notes -->
                <div
                    v-if="delivery.outcome_notes"
                    class="rounded-md bg-muted p-3 text-sm"
                >
                    <p class="font-medium">Outcome notes</p>

                    <p class="mt-1 whitespace-pre-wrap text-muted-foreground">
                        {{ delivery.outcome_notes }}
                    </p>
                </div>
            </article>
        </div>

        <!-- Pagination -->
        <nav
            v-if="deliveries.last_page > 1"
            class="flex flex-wrap gap-2"
            aria-label="Delivery pagination"
        >
            <template
                v-for="(link, index) in deliveries.links"
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
            v-if="confirmation.delivery"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
            @click.self="closeConfirmation"
            @keydown.esc="closeConfirmation"
        >
            <section
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="confirmation-title"
                aria-describedby="confirmation-description"
                class="max-h-[90vh] w-full max-w-md space-y-5 overflow-y-auto rounded-xl border bg-background p-6 shadow-xl"
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

                <!-- Delivery photo upload -->
                <div
                    v-if="confirmation.action === 'complete'"
                    class="space-y-3 rounded-lg border p-4"
                >
                    <div>
                        <h3 class="text-sm font-semibold">
                            Delivery Photo Proof
                            <span class="text-red-500">*</span>
                        </h3>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Take a photo showing the laundry has been delivered
                            to the customer. This photo is required to complete
                            the delivery.
                        </p>
                    </div>

                    <input
                        ref="photoInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        capture="environment"
                        class="hidden"
                        @change="handlePhotoChange"
                    />

                    <!-- Photo preview -->
                    <div v-if="photoPreview" class="space-y-3">
                        <img
                            :src="photoPreview"
                            alt="Delivery proof preview"
                            class="max-h-64 w-full rounded-lg border object-contain"
                        />

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="rounded-md border px-3 py-2 text-sm hover:bg-muted"
                                :disabled="isSubmitting"
                                @click="selectPhoto"
                            >
                                Retake Photo
                            </button>

                            <button
                                type="button"
                                class="rounded-md border border-red-300 px-3 py-2 text-sm text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950"
                                :disabled="isSubmitting"
                                @click="removePhoto"
                            >
                                Remove Photo
                            </button>
                        </div>

                        <p class="text-xs text-green-600 dark:text-green-400">
                            Photo selected successfully.
                        </p>
                    </div>

                    <!-- Upload button -->
                    <button
                        v-else
                        type="button"
                        class="flex w-full flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-6 text-center transition hover:bg-muted"
                        :disabled="isSubmitting"
                        @click="selectPhoto"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="32"
                            height="32"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="text-muted-foreground"
                        >
                            <path
                                d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3z"
                            />
                            <circle cx="12" cy="13" r="3" />
                        </svg>

                        <span class="text-sm font-medium">
                            Take or Upload Delivery Photo
                        </span>

                        <span class="text-xs text-muted-foreground">
                            JPG, PNG or WEBP · Maximum 5 MB
                        </span>
                    </button>

                    <!-- Photo validation errors -->
                    <p
                        v-if="validationErrors.photo"
                        class="text-sm text-red-600 dark:text-red-400"
                    >
                        {{ validationErrors.photo }}
                    </p>

                    <p v-if="!photoFile" class="text-xs text-muted-foreground">
                        Please upload a photo before completing this delivery.
                    </p>
                </div>

                <!-- Delivery details -->
                <div class="rounded-lg bg-muted p-4 text-sm">
                    <p class="font-medium">
                        Order:
                        {{
                            confirmation.delivery.order?.order_number ??
                            `Delivery #${confirmation.delivery.id}`
                        }}
                    </p>

                    <p class="mt-1 text-muted-foreground">
                        Booking Number:
                        <span class="font-medium text-foreground">
                            {{
                                confirmation.delivery.order?.booking
                                    ?.booking_number ?? 'N/A'
                            }}
                        </span>
                    </p>

                    <p class="mt-1 text-muted-foreground">
                        Customer:
                        {{
                            confirmation.delivery.order?.customer?.name ??
                            'Customer unavailable'
                        }}
                    </p>

                    <p class="mt-2 text-muted-foreground">
                        Scheduled:
                        {{ formatDate(confirmation.delivery.scheduled_at) }}
                    </p>

                    <p class="mt-2 text-muted-foreground">
                        Delivery Address:
                        {{
                            confirmation.delivery.address_snapshot ||
                            'No address recorded'
                        }}
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
                                notes[confirmation.delivery.id]?.trim() ||
                                'No notes provided.'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Modal actions -->
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
                        :disabled="
                            isSubmitting ||
                            (confirmation.action === 'complete' && !photoFile)
                        "
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
