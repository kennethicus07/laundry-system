<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import {
    invoiceStatusLabels,
    paymentStatusLabels,
    refundStatusLabels,
} from '../helpers';

type Kind = 'invoice' | 'payment' | 'refund';
type Tone = 'success' | 'warning' | 'info' | 'danger' | 'muted';

const props = defineProps<{
    kind: Kind;
    status: string;
}>();

const toneClasses: Record<Tone, string> = {
    success:
        'border-transparent bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
    warning:
        'border-transparent bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    info: 'border-transparent bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
    danger: 'border-transparent bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    muted: 'border-transparent bg-muted text-muted-foreground',
};

const tones: Record<Kind, Record<string, Tone>> = {
    invoice: {
        unpaid: 'warning',
        partially_paid: 'info',
        paid: 'success',
        void: 'muted',
    },
    payment: {
        pending_verification: 'warning',
        completed: 'success',
        failed: 'danger',
        cancelled: 'muted',
    },
    refund: {
        requested: 'warning',
        approved: 'info',
        processing: 'info',
        completed: 'success',
        rejected: 'danger',
        failed: 'danger',
    },
};

const labels: Record<Kind, Record<string, string>> = {
    invoice: invoiceStatusLabels,
    payment: paymentStatusLabels,
    refund: refundStatusLabels,
};

const tone = computed<Tone>(() => tones[props.kind][props.status] ?? 'muted');
const label = computed(() => labels[props.kind][props.status] ?? props.status);
</script>

<template>
    <Badge
        variant="outline"
        :class="cn('font-medium whitespace-nowrap', toneClasses[tone])"
    >
        {{ label }}
    </Badge>
</template>
