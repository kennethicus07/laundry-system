<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CircleCheck } from 'lucide-vue-next';
import { Alert, AlertDescription } from '@/components/ui/alert';

const page = usePage();

// Requires 'flash' => ['success' => ...] shared in HandleInertiaRequests.
const success = computed(
    () =>
        (page.props.flash as { success?: string | null } | undefined)?.success,
);
</script>

<template>
    <Alert
        v-if="success"
        class="border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200"
    >
        <CircleCheck class="size-4" />
        <AlertDescription>{{ success }}</AlertDescription>
    </Alert>
</template>
