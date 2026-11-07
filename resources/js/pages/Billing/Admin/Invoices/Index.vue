<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import BillingNav from '@/modules/billing/components/BillingNav.vue';
import BillingPagination from '@/modules/billing/components/BillingPagination.vue';
import BillingStatusBadge from '@/modules/billing/components/BillingStatusBadge.vue';
import CreateInvoiceDialog from '@/modules/billing/components/CreateInvoiceDialog.vue';
import FlashMessage from '@/modules/billing/components/FlashMessage.vue';
import {
    formatDateTime,
    formatMoney,
    invoiceStatusLabels,
} from '@/modules/billing/helpers';
import { billingRoutes } from '@/modules/billing/routes';
import type {
    InvoiceStatus,
    InvoiceSummary,
    Paginated,
} from '@/modules/billing/types';

const props = defineProps<{
    invoices: Paginated<InvoiceSummary>;
    filters: { search: string | null; status: InvoiceStatus | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: billingRoutes.invoices.index() },
];

const statusOptions = Object.entries(invoiceStatusLabels) as [
    InvoiceStatus,
    string,
][];

const search = ref(props.filters.search ?? '');
const status = ref<string>(props.filters.status ?? 'all');

function applyFilters() {
    router.get(
        billingRoutes.invoices.index(),
        {
            search: search.value.trim() || undefined,
            status: status.value === 'all' ? undefined : status.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

let searchTimer: ReturnType<typeof setTimeout> | undefined;

watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 300);
});
watch(status, applyFilters);
onBeforeUnmount(() => clearTimeout(searchTimer));
</script>

<template>
    <Head title="Invoices" />

    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Billing</h1>
                <p class="text-sm text-muted-foreground">
                    Invoices, payments and refunds for laundry orders.
                </p>
            </div>
            <CreateInvoiceDialog />
        </div>

        <BillingNav />
        <FlashMessage />

        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    placeholder="Search invoice number or order ID"
                    class="pl-9"
                />
            </div>
            <Select v-model="status">
                <SelectTrigger class="sm:w-48">
                    <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All statuses</SelectItem>
                    <SelectItem
                        v-for="[value, label] in statusOptions"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <Card>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="pl-6">Invoice</TableHead>
                            <TableHead>Order</TableHead>
                            <TableHead>Issued</TableHead>
                            <TableHead class="text-right">Total</TableHead>
                            <TableHead class="pr-6">Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="invoices.data.length === 0">
                            <TableCell
                                colspan="5"
                                class="py-10 text-center text-muted-foreground"
                            >
                                No invoices match these filters.
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-for="invoice in invoices.data"
                            :key="invoice.id"
                        >
                            <TableCell class="pl-6">
                                <Link
                                    :href="
                                        billingRoutes.invoices.show(invoice.id)
                                    "
                                    class="font-medium hover:underline"
                                >
                                    {{ invoice.invoice_number }}
                                </Link>
                                <span
                                    v-if="invoice.revision_number > 1"
                                    class="ml-2 text-xs text-muted-foreground"
                                >
                                    rev. {{ invoice.revision_number }}
                                </span>
                            </TableCell>
                            <TableCell class="text-muted-foreground"
                                >#{{ invoice.order_id }}</TableCell
                            >
                            <TableCell class="whitespace-nowrap">{{
                                formatDateTime(invoice.issued_at)
                            }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{
                                formatMoney(invoice.total_amount)
                            }}</TableCell>
                            <TableCell class="pr-6">
                                <BillingStatusBadge
                                    kind="invoice"
                                    :status="invoice.status"
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <BillingPagination :links="invoices.links" :meta="invoices.meta" />
    </div>
</template>
