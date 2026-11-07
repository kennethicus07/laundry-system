<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Check, Loader2, Plus, Search } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { formatDateTime, formatMoney, orderStatusLabels } from '../helpers';
import { billingRoutes } from '../routes';
import type { BillableOrderOption } from '../types';

const open = ref(false);
const search = ref('');
const options = ref<BillableOrderOption[]>([]);
const selected = ref<BillableOrderOption | null>(null);
const loading = ref(false);
const loadError = ref<string | null>(null);

const form = useForm<{ order_id: number | null }>({ order_id: null });

let controller: AbortController | null = null;
let searchTimer: ReturnType<typeof setTimeout> | undefined;

async function loadOrders() {
    controller?.abort();
    const current = new AbortController();
    controller = current;

    loading.value = true;
    loadError.value = null;

    try {
        const response = await fetch(
            billingRoutes.invoices.billableOrders(search.value.trim()),
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: current.signal,
            },
        );

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const json = (await response.json()) as { data: BillableOrderOption[] };
        options.value = json.data;
    } catch (error) {
        if ((error as Error).name === 'AbortError') return;
        loadError.value = 'Could not load orders. Please try again.';
    } finally {
        if (controller === current) loading.value = false;
    }
}

watch(open, (isOpen) => {
    if (!isOpen) {
        controller?.abort();
        return;
    }

    search.value = '';
    selected.value = null;
    form.reset();
    form.clearErrors();
    loadOrders();
});

watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadOrders, 300);
});

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
    controller?.abort();
});

function select(order: BillableOrderOption) {
    selected.value = order;
    form.order_id = order.id;
    form.clearErrors();
}

const canSubmit = computed(() => selected.value !== null && !form.processing);

function submit() {
    if (!selected.value) return;

    form.post(billingRoutes.invoices.store(), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
        // The order may have been invoiced by someone else meanwhile.
        onError: () => loadOrders(),
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button>
                <Plus class="size-4" />
                Create invoice
            </Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-lg">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Create invoice</DialogTitle>
                    <DialogDescription>
                        Choose a received order. Its items and agreed prices are
                        copied into the invoice.
                    </DialogDescription>
                </DialogHeader>

                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search order number or customer"
                        class="pl-9"
                        autocomplete="off"
                        aria-label="Search orders"
                    />
                    <Loader2
                        v-if="loading"
                        class="absolute top-1/2 right-3 size-4 -translate-y-1/2 animate-spin text-muted-foreground"
                    />
                </div>

                <div
                    class="max-h-80 space-y-2 overflow-y-auto pr-1"
                    role="listbox"
                    aria-label="Orders ready for invoicing"
                >
                    <p
                        v-if="loadError"
                        class="rounded-md border border-destructive/40 p-3 text-sm text-destructive"
                    >
                        {{ loadError }}
                    </p>

                    <div
                        v-else-if="!loading && options.length === 0"
                        class="rounded-md border border-dashed p-6 text-center text-sm text-muted-foreground"
                    >
                        <p class="font-medium text-foreground">
                            No orders ready for invoicing
                        </p>
                        <p class="mt-1">
                            Orders appear here once laundry is received, they
                            have items, and they don’t have an active invoice
                            yet.
                        </p>
                    </div>

                    <button
                        v-for="order in options"
                        :key="order.id"
                        type="button"
                        role="option"
                        :aria-selected="selected?.id === order.id"
                        :class="
                            cn(
                                'flex w-full items-start gap-3 rounded-md border p-3 text-left transition-colors hover:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                                selected?.id === order.id &&
                                    'border-primary bg-primary/5 hover:bg-primary/5',
                            )
                        "
                        @click="select(order)"
                    >
                        <div class="min-w-0 flex-1">
                            <div
                                class="flex items-baseline justify-between gap-3"
                            >
                                <span class="font-medium">{{
                                    order.order_number
                                }}</span>
                                <span
                                    class="text-sm font-medium tabular-nums"
                                    >{{ formatMoney(order.items_total) }}</span
                                >
                            </div>
                            <p class="truncate text-sm text-muted-foreground">
                                {{ order.customer_name ?? 'Unknown customer' }}
                            </p>
                            <div
                                class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground"
                            >
                                <Badge variant="secondary" class="font-normal">
                                    {{
                                        orderStatusLabels[order.status] ??
                                        order.status
                                    }}
                                </Badge>
                                <span
                                    >{{ order.item_count }}
                                    {{
                                        order.item_count === 1
                                            ? 'item'
                                            : 'items'
                                    }}</span
                                >
                                <span
                                    >· Received
                                    {{
                                        formatDateTime(order.received_at)
                                    }}</span
                                >
                            </div>
                        </div>
                        <Check
                            v-if="selected?.id === order.id"
                            class="mt-0.5 size-4 shrink-0 text-primary"
                        />
                    </button>
                </div>

                <p v-if="form.errors.order_id" class="text-sm text-destructive">
                    {{ form.errors.order_id }}
                </p>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="open = false"
                        >Cancel</Button
                    >
                    <Button type="submit" :disabled="!canSubmit">
                        <Loader2
                            v-if="form.processing"
                            class="size-4 animate-spin"
                        />
                        {{
                            selected
                                ? `Create invoice for ${selected.order_number}`
                                : 'Select an order'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
