<script setup lang="ts">
import { Button } from '@/components/ui/button';

/**
 * Disabled placeholder for actions whose backend isn't built yet
 * (QR verify/reject, refunds, invoice revision). Swap for a real button later.
 */
withDefaults(
    defineProps<{
        size?: 'default' | 'sm' | 'lg';
        variant?: 'default' | 'outline' | 'secondary' | 'ghost' | 'destructive';
        reason?: string;
    }>(),
    {
        size: 'sm',
        variant: 'outline',
        reason: 'Coming soon: backend action not built yet',
    },
);
</script>

<template>
    <!-- Disabled buttons don't show tooltips, so the wrapper carries the title. -->
    <span :title="reason" class="inline-flex cursor-not-allowed">
        <Button
            :size="size"
            :variant="variant"
            disabled
            class="pointer-events-none gap-1.5"
        >
            <slot />
            <span
                class="rounded bg-muted px-1 py-px text-[10px] font-medium tracking-wide text-muted-foreground uppercase"
            >
                Soon
            </span>
        </Button>
    </span>
</template>
