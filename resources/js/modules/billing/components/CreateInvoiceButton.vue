<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Receipt } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { billingRoutes } from '../routes';

/**
 * One-click "Create invoice" for pages that already know the order,
 * e.g. the Processing list or an order detail page.
 */
const props = withDefaults(
    defineProps<{
        orderId: number;
        size?: 'default' | 'sm';
    }>(),
    { size: 'sm' },
);

const form = useForm({ order_id: props.orderId });

function submit() {
    form.post(billingRoutes.invoices.store(), { preserveScroll: true });
}
</script>

<template>
    <div class="inline-flex flex-col items-end gap-1">
        <Button
            :size="size"
            variant="outline"
            :disabled="form.processing"
            @click="submit"
        >
            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
            <Receipt v-else class="size-4" />
            Create invoice
        </Button>
        <p
            v-if="form.errors.order_id"
            class="max-w-56 text-right text-xs text-destructive"
        >
            {{ form.errors.order_id }}
        </p>
    </div>
</template>
