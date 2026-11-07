<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { formatMoney, isNegativeAmount, isZeroAmount } from '../helpers';
import type { InvoiceBalance } from '../types';

const props = defineProps<{ balance: InvoiceBalance }>();

const overpaid = computed(() => isNegativeAmount(props.balance.balance));
const settled = computed(() => isZeroAmount(props.balance.balance));
</script>

<template>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <Card>
            <CardContent class="p-4">
                <p
                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    Invoice total
                </p>
                <p class="mt-1 text-2xl font-semibold tabular-nums">
                    {{ formatMoney(balance.total) }}
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardContent class="p-4">
                <p
                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    Net paid
                </p>
                <p class="mt-1 text-2xl font-semibold tabular-nums">
                    {{ formatMoney(balance.net_paid) }}
                </p>
                <p
                    v-if="!isZeroAmount(balance.refunded)"
                    class="mt-1 text-xs text-muted-foreground"
                >
                    {{ formatMoney(balance.paid) }} paid −
                    {{ formatMoney(balance.refunded) }} refunded
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardContent class="p-4">
                <p
                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    Pending QR
                </p>
                <p class="mt-1 text-2xl font-semibold tabular-nums">
                    {{ formatMoney(balance.pending) }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Not counted until verified
                </p>
            </CardContent>
        </Card>

        <Card
            :class="
                cn(
                    !settled &&
                        !overpaid &&
                        'border-amber-300 dark:border-amber-800',
                    overpaid && 'border-red-300 dark:border-red-800',
                )
            "
        >
            <CardContent class="p-4">
                <p
                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    {{ overpaid ? 'Overpaid' : 'Balance due' }}
                </p>
                <p
                    :class="
                        cn(
                            'mt-1 text-2xl font-semibold tabular-nums',
                            overpaid && 'text-destructive',
                        )
                    "
                >
                    {{ formatMoney(balance.balance) }}
                </p>
                <p
                    v-if="settled"
                    class="mt-1 text-xs text-emerald-700 dark:text-emerald-400"
                >
                    Fully settled
                </p>
            </CardContent>
        </Card>
    </div>
</template>
