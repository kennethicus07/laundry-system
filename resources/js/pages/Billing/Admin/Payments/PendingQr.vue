<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Info } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Card, CardContent } from '@/components/ui/card';
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
import ComingSoonButton from '@/modules/billing/components/ComingSoonButton.vue';
import FlashMessage from '@/modules/billing/components/FlashMessage.vue';
import { formatDateTime, formatMoney } from '@/modules/billing/helpers';
import { billingRoutes } from '@/modules/billing/routes';
import type { Paginated, Payment } from '@/modules/billing/types';

defineProps<{ payments: Paginated<Payment> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: billingRoutes.invoices.index() },
    { title: 'Pending QR', href: billingRoutes.payments.pendingQr() },
];
</script>

<template>
    <Head title="Pending QR payments" />

    <div class="space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Billing</h1>
            <p class="text-sm text-muted-foreground">
                QR payments waiting for staff to confirm the money reached the
                receiving account. Oldest first.
            </p>
        </div>

        <BillingNav />
        <FlashMessage />

        <Alert>
            <Info class="size-4" />
            <AlertDescription>
                Verify and reject arrive with the QR backend step. Until then
                this queue is read-only.
            </AlertDescription>
        </Alert>

        <Card>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="pl-6">Submitted</TableHead>
                            <TableHead>Invoice</TableHead>
                            <TableHead class="text-right">Amount</TableHead>
                            <TableHead>Reference</TableHead>
                            <TableHead class="pr-6 text-right"
                                >Actions</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="payments.data.length === 0">
                            <TableCell
                                colspan="5"
                                class="py-10 text-center text-muted-foreground"
                            >
                                No QR payments waiting for verification.
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-for="payment in payments.data"
                            :key="payment.id"
                        >
                            <TableCell class="pl-6 whitespace-nowrap">{{
                                formatDateTime(payment.received_at)
                            }}</TableCell>
                            <TableCell>
                                <Link
                                    v-if="payment.invoice"
                                    :href="
                                        billingRoutes.invoices.show(
                                            payment.invoice.id,
                                        )
                                    "
                                    class="font-medium hover:underline"
                                >
                                    {{ payment.invoice.invoice_number }}
                                </Link>
                            </TableCell>
                            <TableCell
                                class="text-right font-medium tabular-nums"
                                >{{ formatMoney(payment.amount) }}</TableCell
                            >
                            <TableCell class="font-mono text-xs">{{
                                payment.reference_number ?? '—'
                            }}</TableCell>
                            <TableCell class="pr-6">
                                <div class="flex justify-end gap-2">
                                    <ComingSoonButton
                                        v-if="payment.has_proof"
                                        variant="ghost"
                                        >Proof</ComingSoonButton
                                    >
                                    <ComingSoonButton>Verify</ComingSoonButton>
                                    <ComingSoonButton variant="ghost"
                                        >Reject</ComingSoonButton
                                    >
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <BillingPagination :links="payments.links" :meta="payments.meta" />
    </div>
</template>
