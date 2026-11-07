<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Info } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent } from '@/components/ui/card';
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
import ComingSoonButton from '@/modules/billing/components/ComingSoonButton.vue';
import FlashMessage from '@/modules/billing/components/FlashMessage.vue';
import {
    formatDateTime,
    formatMoney,
    paymentMethodLabels,
    refundStatusLabels,
} from '@/modules/billing/helpers';
import { billingRoutes } from '@/modules/billing/routes';
import type { Paginated, Refund, RefundStatus } from '@/modules/billing/types';

const props = defineProps<{
    refunds: Paginated<Refund>;
    filters: { status: RefundStatus | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: billingRoutes.invoices.index() },
    { title: 'Refunds', href: billingRoutes.refunds.index() },
];

const statusOptions = Object.entries(refundStatusLabels) as [
    RefundStatus,
    string,
][];
const status = ref<string>(props.filters.status ?? 'all');

watch(status, (value) => {
    router.get(
        billingRoutes.refunds.index(),
        { status: value === 'all' ? undefined : value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});
</script>

<template>
    <Head title="Refunds" />

    <div class="space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Billing</h1>
            <p class="text-sm text-muted-foreground">
                Refunds need manager approval, and are completed only after the
                money is actually returned.
            </p>
        </div>

        <BillingNav />
        <FlashMessage />

        <Alert>
            <Info class="size-4" />
            <AlertDescription>
                Approve, reject and complete arrive with the refund backend
                step. This list is read-only for now.
            </AlertDescription>
        </Alert>

        <div class="flex justify-end">
            <Select v-model="status">
                <SelectTrigger class="w-full sm:w-48">
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
                            <TableHead class="pl-6">Requested</TableHead>
                            <TableHead>Invoice</TableHead>
                            <TableHead>Payment</TableHead>
                            <TableHead class="text-right">Refund</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Reason</TableHead>
                            <TableHead class="pr-6 text-right"
                                >Actions</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="refunds.data.length === 0">
                            <TableCell
                                colspan="7"
                                class="py-10 text-center text-muted-foreground"
                                >No refunds found.</TableCell
                            >
                        </TableRow>

                        <TableRow
                            v-for="refund in refunds.data"
                            :key="refund.id"
                        >
                            <TableCell class="pl-6 whitespace-nowrap">
                                <div>
                                    {{ formatDateTime(refund.requested_at) }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ refund.requested_by?.name }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Link
                                    v-if="refund.payment?.invoice"
                                    :href="
                                        billingRoutes.invoices.show(
                                            refund.payment.invoice.id,
                                        )
                                    "
                                    class="font-medium hover:underline"
                                >
                                    {{ refund.payment.invoice.invoice_number }}
                                </Link>
                            </TableCell>
                            <TableCell class="text-sm whitespace-nowrap">
                                <template v-if="refund.payment">
                                    {{
                                        paymentMethodLabels[
                                            refund.payment.method
                                        ]
                                    }}
                                    ·
                                    {{ formatMoney(refund.payment.amount) }}
                                </template>
                            </TableCell>
                            <TableCell
                                class="text-right font-medium tabular-nums"
                                >{{ formatMoney(refund.amount) }}</TableCell
                            >
                            <TableCell>
                                <BillingStatusBadge
                                    kind="refund"
                                    :status="refund.status"
                                />
                                <div
                                    v-if="refund.approved_by"
                                    class="mt-1 text-xs text-muted-foreground"
                                >
                                    by {{ refund.approved_by.name }}
                                </div>
                            </TableCell>
                            <TableCell
                                class="max-w-56 truncate text-sm"
                                :title="refund.reason"
                                >{{ refund.reason }}</TableCell
                            >
                            <TableCell class="pr-6">
                                <div class="flex justify-end gap-2">
                                    <template
                                        v-if="refund.status === 'requested'"
                                    >
                                        <ComingSoonButton
                                            >Approve</ComingSoonButton
                                        >
                                        <ComingSoonButton variant="ghost"
                                            >Reject</ComingSoonButton
                                        >
                                    </template>
                                    <ComingSoonButton
                                        v-if="
                                            refund.status === 'approved' ||
                                            refund.status === 'processing'
                                        "
                                    >
                                        Mark returned
                                    </ComingSoonButton>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <BillingPagination :links="refunds.links" :meta="refunds.meta" />
    </div>
</template>
