<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';

import {
    Search,
    WashingMachine,
    Clock,
    CheckCircle2,
    Package,
    UserRound,
    CalendarDays,
    LoaderCircle,
} from 'lucide-vue-next';

interface ProcessingItem {
    service_name: string;
    pricing_unit: string;
    quantity: string | number;
}

interface ProcessingOrder {
    id: number;
    orderNumber: string;
    bookingNumber: string;
    status: string;
    customerName: string | null;
    receivedAt: string | null;
    items: ProcessingItem[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedOrders {
    data: ProcessingOrder[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    orders: PaginatedOrders;
    activeTab: string;
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search ?? '');
const loading = ref(false);

const tabs = [
    {
        value: 'awaiting_processing',
        label: 'Awaiting Processing',
        icon: Clock,
    },
    {
        value: 'in_progress',
        label: 'In Progress',
        icon: WashingMachine,
    },
    {
        value: 'ready_for_delivery',
        label: 'Ready for Delivery',
        icon: Package,
    },
    {
        value: 'history',
        label: 'History',
        icon: CheckCircle2,
    },
];

const activeTabLabel = computed(() => {
    return (
        tabs.find((tab) => tab.value === props.activeTab)?.label ??
        'Awaiting Processing'
    );
});

function navigate(tab = props.activeTab, page?: number) {
    loading.value = true;

    router.get(
        '/admin/processing',
        {
            tab,
            search: search.value || undefined,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function changeTab(tab: string) {
    search.value = '';
    navigate(tab);
}

function searchOrders() {
    navigate(props.activeTab);
}

function formatDate(date: string | null) {
    if (!date) return '—';

    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(date));
}

function formatQuantity(quantity: string | number) {
    return Number(quantity).toLocaleString('en-PH', {
        maximumFractionDigits: 2,
    });
}

function statusLabel(status: string) {
    const labels: Record<string, string> = {
        received: 'Awaiting Processing',
        processing: 'In Progress',
        ready_for_delivery: 'Ready for Delivery',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };

    return labels[status] ?? status;
}

function statusVariant(status: string) {
    const variants: Record<string, string> = {
        received: 'secondary',
        processing: 'default',
        ready_for_delivery: 'outline',
        completed: 'secondary',
        cancelled: 'destructive',
    };

    return variants[status] ?? 'secondary';
}

function formatPricingUnit(unit: string) {
    const units: Record<string, string> = {
        kg: 'kg',
        piece: 'piece',
        load: 'load',
    };

    return units[unit] ?? unit;
}

const processingOrderId = ref<number | null>(null);

function startProcessing(order: ProcessingOrder) {
    processingOrderId.value = order.id;

    router.post(
        `/admin/processing/${order.id}/start`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingOrderId.value = null;
            },
        },
    );
}

function completeProcessing(order: ProcessingOrder) {
    processingOrderId.value = order.id;

    router.post(
        `/admin/processing/${order.id}/complete`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingOrderId.value = null;
            },
        },
    );
}
</script>

<template>
    <Head title="Laundry Processing" />

    <div class="space-y-6 p-6">
        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <div class="flex items-center gap-2">
                    <WashingMachine class="h-6 w-6 text-primary" />
                    <h1 class="text-2xl font-bold tracking-tight">
                        Laundry Processing
                    </h1>
                </div>

                <p class="mt-1 text-sm text-muted-foreground">
                    Manage laundry orders from processing to completion.
                </p>
            </div>
        </div>

        <!-- Tabs -->
        <Tabs :model-value="activeTab" @update:model-value="changeTab">
            <TabsList
                class="grid h-auto w-full grid-cols-2 gap-1 md:grid-cols-4"
            >
                <TabsTrigger
                    v-for="tab in tabs"
                    :key="tab.value"
                    :value="tab.value"
                    class="gap-2 py-2"
                >
                    <component :is="tab.icon" class="h-4 w-4" />
                    <span>{{ tab.label }}</span>
                </TabsTrigger>
            </TabsList>
        </Tabs>

        <!-- Search and Count -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <form
                class="flex w-full gap-2 sm:max-w-md"
                @submit.prevent="searchOrders"
            >
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />

                    <Input
                        v-model="search"
                        placeholder="Search order number or customer..."
                        class="pl-9"
                    />
                </div>

                <Button type="submit" :disabled="loading"> Search </Button>
            </form>

            <p class="text-sm text-muted-foreground">
                {{ orders.total }}
                {{ orders.total === 1 ? 'order' : 'orders' }}
                in {{ activeTabLabel }}
            </p>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
        </div>

        <!-- Orders -->
        <div v-else-if="orders.data.length" class="grid gap-4">
            <Card v-for="order in orders.data" :key="order.id">
                <CardHeader
                    class="flex flex-row items-start justify-between gap-4 space-y-0"
                >
                    <div class="space-y-1">
                        <CardTitle class="text-base">
                            {{ order.orderNumber }}
                        </CardTitle>
                        <h3>{{ order.bookingNumber }}</h3>
                        <div
                            class="flex items-center gap-2 text-sm text-muted-foreground"
                        >
                            <UserRound class="h-4 w-4" />
                            {{ order.customerName ?? 'Unknown customer' }}
                        </div>
                    </div>

                    <Badge :variant="statusVariant(order.status) as any">
                        {{ statusLabel(order.status) }}
                    </Badge>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Order Information -->
                    <div
                        class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-muted-foreground"
                    >
                        <div class="flex items-center gap-2">
                            <CalendarDays class="h-4 w-4" />
                            <span>
                                Received: {{ formatDate(order.receivedAt) }}
                            </span>
                        </div>
                    </div>

                    <!-- Laundry Items -->
                    <div class="rounded-lg border p-3">
                        <p class="mb-3 text-sm font-medium">Laundry Services</p>

                        <div
                            v-for="(item, index) in order.items"
                            :key="index"
                            class="flex items-center justify-between gap-3 py-1.5 text-sm"
                        >
                            <span>{{ item.service_name }}</span>

                            <span
                                class="whitespace-nowrap text-muted-foreground"
                            >
                                {{ formatQuantity(item.quantity) }}
                                {{ formatPricingUnit(item.pricing_unit) }}
                            </span>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="flex justify-end gap-2">
                        <Button
                            v-if="order.status === 'received'"
                            :disabled="processingOrderId === order.id"
                            @click="startProcessing(order)"
                        >
                            <LoaderCircle
                                v-if="processingOrderId === order.id"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <WashingMachine v-else class="mr-2 h-4 w-4" />
                            Start Processing
                        </Button>

                        <Button
                            v-else-if="order.status === 'processing'"
                            :disabled="processingOrderId === order.id"
                            @click="completeProcessing(order)"
                        >
                            <LoaderCircle
                                v-if="processingOrderId === order.id"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <CheckCircle2 v-else class="mr-2 h-4 w-4" />
                            Complete Processing
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Empty State -->
        <Card v-else>
            <CardContent
                class="flex flex-col items-center justify-center py-16 text-center"
            >
                <div class="mb-4 rounded-full bg-muted p-4">
                    <WashingMachine class="h-8 w-8 text-muted-foreground" />
                </div>

                <h3 class="font-semibold">No orders found</h3>

                <p class="mt-1 max-w-sm text-sm text-muted-foreground">
                    There are currently no orders in
                    {{ activeTabLabel.toLowerCase() }}.
                </p>
            </CardContent>
        </Card>

        <!-- Pagination -->
        <div
            v-if="orders.last_page > 1"
            class="flex flex-wrap items-center justify-between gap-3"
        >
            <p class="text-sm text-muted-foreground">
                Page {{ orders.current_page }} of {{ orders.last_page }}
            </p>

            <div class="flex flex-wrap gap-1">
                <Button
                    v-for="(link, index) in orders.links"
                    :key="index"
                    size="sm"
                    :variant="link.active ? 'default' : 'outline'"
                    :disabled="!link.url || loading"
                    @click="link.url && navigateToPage(link.url)"
                >
                    <span v-html="link.label" />
                </Button>
            </div>
        </div>
    </div>
</template>
