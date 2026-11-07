<script setup lang="ts">
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
import type { Payment } from '../types';
import BillingStatusBadge from './BillingStatusBadge.vue';
import ComingSoonButton from './ComingSoonButton.vue';

defineProps<{ payments: Payment[] }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="text-base">Payments</CardTitle>
        </CardHeader>
        <CardContent class="p-0">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="pl-6">Received</TableHead>
                        <TableHead>Method</TableHead>
                        <TableHead class="text-right">Amount</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Reference</TableHead>
                        <TableHead>Staff</TableHead>
                        <TableHead class="pr-6 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="payments.length === 0">
                        <TableCell
                            colspan="7"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No payments recorded yet.
                        </TableCell>
                    </TableRow>

                    <TableRow v-for="payment in payments" :key="payment.id">
                        <TableCell class="pl-6 whitespace-nowrap">{{
                            formatDateTime(payment.received_at)
                        }}</TableCell>
                        <TableCell>{{
                            paymentMethodLabels[payment.method]
                        }}</TableCell>
                        <TableCell
                            class="text-right font-medium tabular-nums"
                            >{{ formatMoney(payment.amount) }}</TableCell
                        >
                        <TableCell>
                            <BillingStatusBadge
                                kind="payment"
                                :status="payment.status"
                            />
                        </TableCell>
                        <TableCell class="font-mono text-xs">{{
                            payment.reference_number ?? '—'
                        }}</TableCell>
                        <TableCell class="text-sm">
                            <div>{{ payment.recorded_by?.name ?? '—' }}</div>
                            <div
                                v-if="payment.verified_by"
                                class="text-xs text-muted-foreground"
                            >
                                Verified by {{ payment.verified_by.name }}
                            </div>
                            <div
                                v-if="payment.notes"
                                class="text-xs text-muted-foreground"
                            >
                                {{ payment.notes }}
                            </div>
                        </TableCell>
                        <TableCell class="pr-6">
                            <div class="flex justify-end gap-2">
                                <ComingSoonButton
                                    v-if="payment.has_proof"
                                    variant="ghost"
                                    >Proof</ComingSoonButton
                                >
                                <template
                                    v-if="
                                        payment.method === 'qr' &&
                                        payment.status ===
                                            'pending_verification'
                                    "
                                >
                                    <ComingSoonButton>Verify</ComingSoonButton>
                                    <ComingSoonButton variant="ghost"
                                        >Reject</ComingSoonButton
                                    >
                                </template>
                                <ComingSoonButton
                                    v-if="payment.status === 'completed'"
                                    variant="ghost"
                                >
                                    Refund
                                </ComingSoonButton>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </CardContent>
    </Card>
</template>
