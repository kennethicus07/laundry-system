<script setup lang="ts">
import { Role } from '@/types/role';
import { useStatus } from '@/composables/useStatus';
import { Badge } from '@/components/ui/badge';

const { fromStatus } = useStatus();

const props = defineProps<{
    role: Role;
    permissions: any[];
}>();
</script>

<template>
    <div class="space-y-6 p-6">
        <!-- HEADER -->
        <div>
            <h1 class="text-2xl font-semibold">Role Details</h1>
            <p class="text-sm text-muted-foreground">
                View role information and assigned permissions
            </p>
        </div>

        <!-- ROLE INFO -->
        <div class="space-y-3 rounded-lg border p-4">
            <h2 class="font-medium">Role Info</h2>

            <!-- NAME -->
            <div class="text-sm">
                <span class="text-muted-foreground">Name:</span>
                <span class="ml-2 font-medium">
                    {{ props.role.name }}
                </span>
            </div>

            <!-- GROUP -->
            <div class="text-sm">
                <span class="text-muted-foreground">Group:</span>
                <span class="ml-2 font-medium">
                    {{ props.role.role_group?.name || '—' }}
                </span>
            </div>

            <!-- STATUS -->
            <div class="flex items-center gap-2 text-sm">
                <span class="text-muted-foreground">Status:</span>

                <Badge :variant="fromStatus(props.role.status).variant">
                    {{ fromStatus(props.role.status).label }}
                </Badge>
            </div>
        </div>

        <!-- PERMISSIONS -->
        <div class="rounded-lg border p-4">
            <h2 class="mb-4 font-medium">Permissions</h2>

            <div
                v-if="props.permissions?.length"
                class="grid grid-cols-2 gap-2"
            >
                <div
                    v-for="perm in props.permissions"
                    :key="perm.id"
                    class="flex items-center gap-2 text-sm"
                >
                    <span class="h-2 w-2 rounded-full bg-primary"></span>
                    {{ perm.name }}
                </div>
            </div>

            <div v-else class="text-sm text-muted-foreground">
                No permissions found for this group.
            </div>
        </div>
    </div>
</template>
