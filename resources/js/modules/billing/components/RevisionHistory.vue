<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime, formatMoney, formatQuantity } from '../helpers';
import type { InvoiceRevision } from '../types';

defineProps<{
    revisions: InvoiceRevision[];
    currentRevision: number;
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="text-base">Revision history</CardTitle>
        </CardHeader>
        <CardContent>
            <p
                v-if="revisions.length === 0"
                class="text-sm text-muted-foreground"
            >
                No revisions. This is the original invoice (revision
                {{ currentRevision }}).
            </p>

            <ol v-else class="space-y-3">
                <li v-for="revision in revisions" :key="revision.id">
                    <details class="group rounded-md border">
                        <summary
                            class="flex cursor-pointer list-none items-start gap-3 p-3 [&::-webkit-details-marker]:hidden"
                        >
                            <ChevronRight
                                class="mt-0.5 size-4 shrink-0 text-muted-foreground transition-transform group-open:rotate-90"
                            />
                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex flex-wrap items-baseline justify-between gap-x-4"
                                >
                                    <span class="font-medium"
                                        >Revision
                                        {{ revision.revision_number }}</span
                                    >
                                    <span class="tabular-nums">{{
                                        formatMoney(revision.total_amount)
                                    }}</span>
                                </div>
                                <p class="mt-0.5 text-sm text-muted-foreground">
                                    {{ revision.reason }}
                                </p>
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    Replaced
                                    {{ formatDateTime(revision.revised_at) }}
                                    <template v-if="revision.revised_by">
                                        by
                                        {{ revision.revised_by.name }}</template
                                    >
                                </p>
                            </div>
                        </summary>

                        <div class="overflow-x-auto border-t px-3 py-2">
                            <table class="w-full text-sm">
                                <thead
                                    class="text-left text-xs text-muted-foreground"
                                >
                                    <tr>
                                        <th class="py-1 font-medium">Item</th>
                                        <th class="py-1 text-right font-medium">
                                            Qty
                                        </th>
                                        <th class="py-1 text-right font-medium">
                                            Unit price
                                        </th>
                                        <th class="py-1 text-right font-medium">
                                            Line total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in revision.items ?? []"
                                        :key="item.id"
                                    >
                                        <td class="py-1">
                                            {{ item.description }}
                                        </td>
                                        <td
                                            class="py-1 text-right tabular-nums"
                                        >
                                            {{ formatQuantity(item.quantity) }}
                                        </td>
                                        <td
                                            class="py-1 text-right tabular-nums"
                                        >
                                            {{ formatMoney(item.unit_price) }}
                                        </td>
                                        <td
                                            class="py-1 text-right tabular-nums"
                                        >
                                            {{ formatMoney(item.line_total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </li>
            </ol>
        </CardContent>
    </Card>
</template>
