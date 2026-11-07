<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';

interface AvailableOrder {
    id: number;
    orderNumber: string;
    bookingNumber: string | null;
    mobileNumber: string | null;
    status: string;
    customerName: string;
    requestedPickupAt: string | null;
    preferredPickUpAddress: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedOrders {
    data: AvailableOrder[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginationLink[];
}

interface Officer {
    id: number;
    name: string;
}

interface Schedule {
    id: number;
    status: string;
    scheduled_at: string | null;
    address_snapshot: string | null;
    order: {
        id: number;
        order_number: string;
        booking: {
            id: number;
            booking_number: string;
        } | null;
    } | null;
    assigned_staff: {
        id: number;
        name: string;
    } | null;
}

interface PaginatedSchedules {
    data: Schedule[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    orders: PaginatedOrders;
    orderSearch: string;
    officers: Officer[];
    schedules: PaginatedSchedules;
    scheduleSearch: string;
    scheduleStatus: string;
}>();

const orderPickerOpen = ref(false);

const form = useForm({
    order_id: '',
    staff_id: '',
    scheduled_at: '',
    address_snapshot: '',
});

const orderSearch = ref(props.orderSearch);
const selectedOrderData = ref<AvailableOrder | null>(null);
const isSearching = ref(false);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const selectedOrder = computed(() => selectedOrderData.value);

watch(
    () => props.orderSearch,
    (value) => {
        if (value !== orderSearch.value) {
            orderSearch.value = value;
        }
    },
);

watch(orderSearch, (value) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    // A changed search invalidates a previous selection.
    if (selectedOrderData.value && !value.trim()) {
        selectedOrderData.value = null;
        form.order_id = '';
    }

    isSearching.value = true;

    searchTimeout = setTimeout(() => {
        router.get(
            '/admin/pickup-schedules',
            {
                order_search: value.trim() || undefined,
            },
            {
                only: ['orders', 'orderSearch'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => {
                    isSearching.value = false;
                },
            },
        );
    }, 300);
});

onBeforeUnmount(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});

function selectOrder(order: AvailableOrder) {
    selectedOrderData.value = order;
    form.order_id = String(order.id);

    if (order.preferredPickUpAddress) {
        form.address_snapshot = order.preferredPickUpAddress;
    }

    orderPickerOpen.value = false;
}

function changeOrdersPage(url: string | null) {
    if (!url) return;

    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    isSearching.value = true;

    router.get(
        url,
        {},
        {
            only: ['orders', 'orderSearch'],
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isSearching.value = false;
            },
        },
    );
}

function submit() {
    form.post('/admin/pickup-schedules', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selectedOrderData.value = null;
            orderSearch.value = '';
        },
    });
}

function formatDate(value: string | null): string {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return value;

    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}

function formatStatus(status: string): string {
    return status.replaceAll('_', ' ');
}

const scheduleSearch = ref(props.scheduleSearch);
const scheduleStatus = ref(props.scheduleStatus);
const isFilteringSchedules = ref(false);

let scheduleSearchTimeout: ReturnType<typeof setTimeout> | undefined;

watch(
    () => props.scheduleSearch,
    (value) => {
        if (value !== scheduleSearch.value) {
            scheduleSearch.value = value;
        }
    },
);

watch(
    () => props.scheduleStatus,
    (value) => {
        if (value !== scheduleStatus.value) {
            scheduleStatus.value = value;
        }
    },
);

watch([scheduleSearch, scheduleStatus], () => {
    if (scheduleSearchTimeout) {
        clearTimeout(scheduleSearchTimeout);
    }

    scheduleSearchTimeout = setTimeout(() => {
        isFilteringSchedules.value = true;

        router.get(
            '/admin/pickup-schedules',
            {
                order_search: orderSearch.value.trim() || undefined,
                schedule_search: scheduleSearch.value.trim() || undefined,
                schedule_status:
                    scheduleStatus.value === 'all'
                        ? undefined
                        : scheduleStatus.value,
            },
            {
                only: ['schedules', 'scheduleSearch', 'scheduleStatus'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => {
                    isFilteringSchedules.value = false;
                },
            },
        );
    }, 300);
});

function clearScheduleFilters() {
    scheduleSearch.value = '';
    scheduleStatus.value = 'all';
}

function changeSchedulesPage(url: string | null) {
    if (!url) return;

    if (scheduleSearchTimeout) {
        clearTimeout(scheduleSearchTimeout);
    }

    isFilteringSchedules.value = true;

    router.get(
        url,
        {},
        {
            only: ['schedules', 'scheduleSearch', 'scheduleStatus'],
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isFilteringSchedules.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Pickup Scheduling" />

    <main class="space-y-6 p-4 md:p-6">
        <header>
            <h1 class="text-2xl font-semibold tracking-tight">
                Pickup Scheduling
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                Assign an officer and schedule an order for pickup.
            </p>
        </header>

        <!-- Create pickup schedule -->
        <section class="rounded-lg border p-5">
            <h2 class="font-semibold">Create pickup schedule</h2>

            <form class="mt-5 space-y-5" @submit.prevent="submit">
                <!-- Search and select order -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">
                        Order to schedule
                    </label>

                    <Popover v-model:open="orderPickerOpen">
                        <PopoverTrigger as-child>
                            <Button
                                type="button"
                                variant="outline"
                                role="combobox"
                                :aria-expanded="orderPickerOpen"
                                class="w-full justify-between font-normal"
                            >
                                <span class="truncate">
                                    <template v-if="selectedOrder">
                                        {{
                                            selectedOrder.bookingNumber ||
                                            'No booking number'
                                        }}
                                        — {{ selectedOrder.orderNumber }}
                                    </template>

                                    <template v-else>
                                        Search booking number, order number, or
                                        customer...
                                    </template>
                                </span>

                                <icon
                                    name="chevrons-up-down"
                                    class="ml-2 size-4 shrink-0 opacity-50"
                                />
                            </Button>
                        </PopoverTrigger>

                        <PopoverContent
                            align="start"
                            class="w-[--reka-popover-trigger-width] min-w-[320px] p-0"
                        >
                            <Command :should-filter="false">
                                <CommandInput
                                    v-model="orderSearch"
                                    placeholder="Search bookings, orders, customers..."
                                />

                                <CommandList class="max-h-72">
                                    <CommandEmpty v-if="!isSearching">
                                        {{
                                            orderSearch.trim()
                                                ? 'No matching orders found.'
                                                : 'No orders available for pickup.'
                                        }}
                                    </CommandEmpty>

                                    <CommandGroup heading="Available orders">
                                        <CommandItem
                                            v-for="order in orders.data"
                                            :key="order.id"
                                            :value="String(order.id)"
                                            @select="selectOrder(order)"
                                        >
                                            <div
                                                class="flex min-w-0 flex-1 flex-col gap-1"
                                            >
                                                <span class="font-medium">
                                                    {{
                                                        order.bookingNumber ||
                                                        'No booking number'
                                                    }}
                                                </span>

                                                <span
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    {{ order.orderNumber }}
                                                </span>

                                                <span
                                                    class="truncate text-sm text-muted-foreground"
                                                >
                                                    {{ order.customerName }}
                                                </span>
                                            </div>

                                            <icon
                                                v-if="
                                                    form.order_id ===
                                                    String(order.id)
                                                "
                                                name="check"
                                                class="ml-2 size-4 shrink-0"
                                            />
                                        </CommandItem>
                                    </CommandGroup>

                                    <div
                                        v-if="isSearching"
                                        class="px-3 py-2 text-sm text-muted-foreground"
                                    >
                                        Searching...
                                    </div>
                                </CommandList>

                                <div
                                    v-if="orders.last_page > 1"
                                    class="flex items-center justify-between gap-2 border-t p-2"
                                >
                                    <span class="text-xs text-muted-foreground">
                                        {{ orders.total }} orders
                                    </span>

                                    <div class="flex gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            :disabled="
                                                orders.current_page <= 1 ||
                                                isSearching
                                            "
                                            @click="
                                                changeOrdersPage(
                                                    orders.links[0]?.url ??
                                                        null,
                                                )
                                            "
                                        >
                                            Previous
                                        </Button>

                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            :disabled="
                                                orders.current_page >=
                                                    orders.last_page ||
                                                isSearching
                                            "
                                            @click="
                                                changeOrdersPage(
                                                    orders.links[
                                                        orders.links.length - 1
                                                    ]?.url ?? null,
                                                )
                                            "
                                        >
                                            Next
                                        </Button>
                                    </div>
                                </div>
                            </Command>
                        </PopoverContent>
                    </Popover>

                    <p class="text-xs text-muted-foreground">
                        Search by booking number, order number, or customer
                        name.
                    </p>

                    <p
                        v-if="form.errors.order_id"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.order_id }}
                    </p>
                </div>

                <!-- Selected order details -->
                <div
                    v-if="selectedOrder"
                    class="space-y-4 rounded-md border bg-muted/40 p-4"
                >
                    <div
                        class="flex flex-wrap items-start justify-between gap-2"
                    >
                        <div>
                            <p class="text-sm font-medium">Selected order</p>

                            <p class="mt-1 font-semibold">
                                {{ selectedOrder.orderNumber }}
                            </p>

                            <p class="text-sm text-muted-foreground">
                                Booking:
                                {{ selectedOrder.bookingNumber || '—' }}
                            </p>
                        </div>

                        <Badge variant="secondary" class="capitalize">
                            {{ formatStatus(selectedOrder.status) }}
                        </Badge>
                    </div>

                    <div>
                        <p class="text-sm font-medium">Customer</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ selectedOrder.customerName }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium">
                            Customer's requested pickup time
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ formatDate(selectedOrder.requestedPickupAt) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium">
                            Customer's pickup address
                        </p>

                        <p
                            class="mt-1 text-sm break-words text-muted-foreground"
                        >
                            {{
                                selectedOrder.preferredPickUpAddress ||
                                'No address provided'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium">
                            Customer's mobile number
                        </p>

                        <p
                            class="mt-1 text-sm break-words text-muted-foreground"
                        >
                            {{
                                selectedOrder.mobileNumber || 'No mobile number'
                            }}
                        </p>
                    </div>

                    <button
                        v-if="selectedOrder.preferredPickUpAddress"
                        type="button"
                        class="text-sm font-medium text-primary underline underline-offset-4"
                        @click="
                            form.address_snapshot =
                                selectedOrder.preferredPickUpAddress ?? ''
                        "
                    >
                        Use customer's address
                    </button>
                </div>

                <!-- Officer -->
                <div class="space-y-1.5">
                    <label for="staff_id" class="text-sm font-medium">
                        Assign officer
                    </label>

                    <select
                        id="staff_id"
                        v-model="form.staff_id"
                        class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                        required
                    >
                        <option value="" disabled>Select an officer</option>

                        <option
                            v-for="officer in officers"
                            :key="officer.id"
                            :value="String(officer.id)"
                        >
                            {{ officer.name }}
                        </option>
                    </select>

                    <p v-if="form.errors.staff_id" class="text-sm text-red-600">
                        {{ form.errors.staff_id }}
                    </p>

                    <p
                        v-if="officers.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        No officers are available for assignment.
                    </p>
                </div>

                <!-- Schedule date and time -->
                <div class="space-y-1.5">
                    <label for="scheduled_at" class="text-sm font-medium">
                        Pickup date and time
                    </label>

                    <input
                        id="scheduled_at"
                        v-model="form.scheduled_at"
                        type="datetime-local"
                        class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                        required
                    />

                    <p
                        v-if="form.errors.scheduled_at"
                        class="text-sm text-red-600"
                    >
                        {{ form.errors.scheduled_at }}
                    </p>
                </div>

                <!-- Address snapshot -->
                <div class="space-y-1.5">
                    <label for="address_snapshot" class="text-sm font-medium">
                        Pickup address
                    </label>

                    <textarea
                        id="address_snapshot"
                        v-model="form.address_snapshot"
                        rows="3"
                        maxlength="1000"
                        placeholder="Enter the complete pickup address"
                        class="w-full rounded-md border bg-background px-3 py-2 text-sm"
                        required
                    />

                    <p
                        v-if="form.errors.address_snapshot"
                        class="text-sm text-red-600"
                    >
                        {{ form.errors.address_snapshot }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="
                        form.processing ||
                        !form.order_id ||
                        !form.staff_id ||
                        !form.scheduled_at ||
                        !form.address_snapshot.trim() ||
                        isSearching ||
                        officers.length === 0
                    "
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ form.processing ? 'Scheduling…' : 'Create schedule' }}
                </button>
            </form>
        </section>

        <!-- Existing pickup schedules -->
        <section class="overflow-hidden rounded-lg border">
            <div class="space-y-4 border-b p-5">
                <div>
                    <h2 class="font-semibold">Pickup schedules</h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Search assignments and filter them by pickup status.
                    </p>
                </div>

                <!-- Search and filters -->
                <div class="flex flex-col gap-3 md:flex-row">
                    <div class="relative flex-1">
                        <input
                            v-model="scheduleSearch"
                            type="search"
                            placeholder="Search order, officer, or address..."
                            aria-label="Search pickup schedules"
                            class="h-10 w-full rounded-md border bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>

                    <select
                        v-model="scheduleStatus"
                        aria-label="Filter pickup schedules by status"
                        class="h-10 w-full rounded-md border bg-background px-3 text-sm md:w-48"
                    >
                        <option value="all">All statuses</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="missed">Missed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <Button
                        type="button"
                        variant="outline"
                        @click="clearScheduleFilters"
                        :disabled="
                            (!scheduleSearch && scheduleStatus === 'all') ||
                            isFilteringSchedules
                        "
                    >
                        Clear filters
                    </Button>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm text-muted-foreground">
                        {{ schedules.total }}
                        {{ schedules.total === 1 ? 'schedule' : 'schedules' }}
                        found
                    </p>

                    <p
                        v-if="isFilteringSchedules"
                        class="text-sm text-muted-foreground"
                        role="status"
                    >
                        Updating results...
                    </p>
                </div>
            </div>

            <div v-if="schedules.data.length" class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead>
                        <tr class="border-b text-muted-foreground">
                            <th class="px-4 py-3 font-medium">Booking</th>
                            <th class="px-4 py-3 font-medium">Order</th>
                            <th class="px-4 py-3 font-medium">Officer</th>
                            <th class="px-4 py-3 font-medium">Scheduled</th>
                            <th class="px-4 py-3 font-medium">Address</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="schedule in schedules.data"
                            :key="schedule.id"
                            class="border-b last:border-0"
                        >
                            <!-- Booking -->
                            <td class="px-4 py-3 font-medium">
                                {{
                                    schedule.order?.booking?.booking_number ??
                                    '—'
                                }}
                            </td>

                            <!-- Order -->
                            <td class="px-4 py-3 font-medium">
                                {{
                                    schedule.order?.order_number ??
                                    `Order #${schedule.id}`
                                }}
                            </td>

                            <!-- Officer -->
                            <td class="px-4 py-3">
                                {{
                                    schedule.assigned_staff?.name ??
                                    'Unassigned'
                                }}
                            </td>

                            <!-- Scheduled -->
                            <td class="px-4 py-3">
                                {{ formatDate(schedule.scheduled_at) }}
                            </td>

                            <!-- Address -->
                            <td class="max-w-xs px-4 py-3">
                                {{ schedule.address_snapshot ?? '—' }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <Badge variant="secondary" class="capitalize">
                                    {{ formatStatus(schedule.status) }}
                                </Badge>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-else-if="isFilteringSchedules"
                class="p-8 text-center text-sm text-muted-foreground"
            >
                Loading pickup schedules...
            </div>

            <div v-else class="p-8 text-center">
                <p class="font-medium">No pickup schedules found</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Try another search term or change the status filter.
                </p>
            </div>

            <nav
                v-if="schedules.last_page > 1"
                class="flex flex-wrap gap-2 border-t p-4"
                aria-label="Schedule pagination"
            >
                <button
                    v-for="link in schedules.links"
                    :key="link.label"
                    type="button"
                    :disabled="!link.url || isFilteringSchedules"
                    class="rounded-md border px-3 py-2 text-sm disabled:pointer-events-none disabled:opacity-50"
                    :class="{ 'bg-muted font-semibold': link.active }"
                    @click="changeSchedulesPage(link.url)"
                    v-html="link.label"
                />
            </nav>
        </section>
    </main>
</template>
