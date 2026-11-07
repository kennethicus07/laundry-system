<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { billingRoutes } from '../routes';

const page = usePage();

const items = [
    { label: 'Invoices', href: billingRoutes.invoices.index() },
    { label: 'Pending QR', href: billingRoutes.payments.pendingQr() },
    { label: 'Refunds', href: billingRoutes.refunds.index() },
];

const currentPath = computed(() => page.url.split('?')[0]);
</script>

<template>
    <nav class="flex gap-1 overflow-x-auto border-b" aria-label="Billing">
        <Link
            v-for="item in items"
            :key="item.href"
            :href="item.href"
            :class="
                cn(
                    '-mb-px border-b-2 px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                    currentPath.startsWith(item.href)
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground',
                )
            "
        >
            {{ item.label }}
        </Link>
    </nav>
</template>
