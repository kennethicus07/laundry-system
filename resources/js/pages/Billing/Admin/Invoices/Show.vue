<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import BalanceSummary from '@/modules/billing/components/BalanceSummary.vue';
import BillingStatusBadge from '@/modules/billing/components/BillingStatusBadge.vue';
import ComingSoonButton from '@/modules/billing/components/ComingSoonButton.vue';
import FlashMessage from '@/modules/billing/components/FlashMessage.vue';
import PaymentsTable from '@/modules/billing/components/PaymentsTable.vue';
import RecordCashPaymentDialog from '@/modules/billing/components/RecordCashPaymentDialog.vue';
import RefundsCard from '@/modules/billing/components/RefundsCard.vue';
import RevisionHistory from '@/modules/billing/components/RevisionHistory.vue';
import {
    formatDateTime,
    formatMoney,
    formatQuantity,
    isZeroAmount,
} from '@/modules/billing/helpers';
import { billingRoutes } from '@/modules/billing/routes';
import type { Invoice, InvoiceBalance } from '@/modules/billing/types';

const props = defineProps<{
    invoice: Invoice;
    balance: InvoiceBalance;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Invoices', href: billingRoutes.invoices.index() },
    {
        title: props.invoice.invoice_number,
        href: billingRoutes.invoices.show(props.invoice.id),
    },
]);

const isVoid = computed(() => props.invoice.status === 'void');
const canRecordCash = computed(
    () => !isVoid.value && !isZeroAmount(props.balance.payable),
);
</script>

<template>
    <Head :title="invoice.invoice_number" />

    <div class="space-y-6 p-6">
        <Link
            :href="billingRoutes.invoices.index()"
            class="inline-flex w-fit items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            All invoices
        </Link>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ invoice.invoice_number }}
                    </h1>
                    <BillingStatusBadge
                        kind="invoice"
                        :status="invoice.status"
                    />
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    Order #{{ invoice.order_id }} · Issued
                    {{ formatDateTime(invoice.issued_at) }} · Revision
                    {{ invoice.revision_number }}
                </p>
                <p
                    v-if="invoice.voided_at"
                    class="mt-1 text-sm text-destructive"
                >
                    Voided {{ formatDateTime(invoice.voided_at) }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <ComingSoonButton size="default"
                    >Revise invoice</ComingSoonButton
                >
                <RecordCashPaymentDialog
                    :invoice-id="invoice.id"
                    :payable="balance.payable"
                    :pending="balance.pending"
                    :disabled="!canRecordCash"
                />
            </div>
        </div>

        <FlashMessage />

        <BalanceSummary :balance="balance" />

        <Card>
            <CardHeader>
                <CardTitle class="text-base">Items</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="pl-6">Description</TableHead>
                            <TableHead class="text-right">Qty</TableHead>
                            <TableHead class="text-right">Unit price</TableHead>
                            <TableHead class="pr-6 text-right"
                                >Line total</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in invoice.items" :key="item.id">
                            <TableCell class="pl-6">{{
                                item.description
                            }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{
                                formatQuantity(item.quantity)
                            }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{
                                formatMoney(item.unit_price)
                            }}</TableCell>
                            <TableCell class="pr-6 text-right tabular-nums">{{
                                formatMoney(item.line_total)
                            }}</TableCell>
                        </TableRow>
                    </TableBody>
                    <TableFooter>
                        <TableRow>
                            <TableCell
                                colspan="3"
                                class="pl-6 text-right text-muted-foreground"
                                >Subtotal</TableCell
                            >
                            <TableCell class="pr-6 text-right tabular-nums">{{
                                formatMoney(invoice.subtotal)
                            }}</TableCell>
                        </TableRow>
                        <TableRow v-if="!isZeroAmount(invoice.discount_amount)">
                            <TableCell
                                colspan="3"
                                class="pl-6 text-right text-muted-foreground"
                                >Discount</TableCell
                            >
                            <TableCell class="pr-6 text-right tabular-nums">
                                −{{ formatMoney(invoice.discount_amount) }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!isZeroAmount(invoice.tax_amount)">
                            <TableCell
                                colspan="3"
                                class="pl-6 text-right text-muted-foreground"
                                >Tax</TableCell
                            >
                            <TableCell class="pr-6 text-right tabular-nums">{{
                                formatMoney(invoice.tax_amount)
                            }}</TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell
                                colspan="3"
                                class="pl-6 text-right font-semibold"
                                >Total</TableCell
                            >
                            <TableCell
                                class="pr-6 text-right font-semibold tabular-nums"
                            >
                                {{ formatMoney(invoice.total_amount) }}
                            </TableCell>
                        </TableRow>
                    </TableFooter>
                </Table>
            </CardContent>
        </Card>

        <PaymentsTable :payments="invoice.payments" />

        <RefundsCard :payments="invoice.payments" />

        <RevisionHistory
            :revisions="invoice.revisions"
            :current-revision="invoice.revision_number"
        />
    </div>
</template>
