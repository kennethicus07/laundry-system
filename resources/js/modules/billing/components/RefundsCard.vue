<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDateTime, formatMoney, paymentMethodLabels } from '../helpers';
import type { Payment, Refund } from '../types';
import BillingStatusBadge from './BillingStatusBadge.vue';
import ComingSoonButton from './ComingSoonButton.vue';

const props = defineProps<{ payments: Payment[] }>();

const refunds = computed(() =>
    props.payments.flatMap((payment) =>
        (payment.refunds ?? []).map((refund: Refund) => ({ refund, payment })),
    ),
);
</script>

<template>
    <Card>
        <CardHeader
            class="flex flex-row items-center justify-between space-y-0"
        >
            <CardTitle class="text-base">Refunds</CardTitle>
            <ComingSoonButton>Request refund</ComingSoonButton>
        </CardHeader>
        <CardContent class="p-0">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="pl-6">Requested</TableHead>
                        <TableHead>Against payment</TableHead>
                        <TableHead class="text-right">Amount</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Reason</TableHead>
                        <TableHead class="pr-6 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="refunds.length === 0">
                        <TableCell
                            colspan="6"
                            class="py-8 text-center text-muted-foreground"
                            >No refunds.</TableCell
                        >
                    </TableRow>

                    <TableRow
                        v-for="{ refund, payment } in refunds"
                        :key="refund.id"
                    >
                        <TableCell class="pl-6 whitespace-nowrap">
                            <div>{{ formatDateTime(refund.requested_at) }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ refund.requested_by?.name }}
                            </div>
                        </TableCell>
                        <TableCell class="text-sm">
                            {{ paymentMethodLabels[payment.method] }} ·
                            {{ formatMoney(payment.amount) }}
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
                        </TableCell>
                        <TableCell
                            class="max-w-64 truncate text-sm"
                            :title="refund.reason"
                            >{{ refund.reason }}</TableCell
                        >
                        <TableCell class="pr-6">
                            <div class="flex justify-end gap-2">
                                <template v-if="refund.status === 'requested'">
                                    <ComingSoonButton>Approve</ComingSoonButton>
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
</template>
