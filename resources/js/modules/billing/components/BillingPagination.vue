<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { PaginationLinks, PaginationMeta } from '../types';

defineProps<{
    links: PaginationLinks;
    meta: PaginationMeta;
}>();
</script>

<template>
    <div
        v-if="meta.total > 0"
        class="flex flex-wrap items-center justify-between gap-3 text-sm"
    >
        <p class="text-muted-foreground">
            Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}
        </p>

        <div v-if="meta.last_page > 1" class="flex items-center gap-2">
            <Button v-if="links.prev" variant="outline" size="sm" as-child>
                <Link :href="links.prev" preserve-scroll preserve-state
                    >Previous</Link
                >
            </Button>
            <Button v-else variant="outline" size="sm" disabled
                >Previous</Button
            >

            <span class="px-1 text-muted-foreground">
                Page {{ meta.current_page }} of {{ meta.last_page }}
            </span>

            <Button v-if="links.next" variant="outline" size="sm" as-child>
                <Link :href="links.next" preserve-scroll preserve-state
                    >Next</Link
                >
            </Button>
            <Button v-else variant="outline" size="sm" disabled>Next</Button>
        </div>
    </div>
</template>
