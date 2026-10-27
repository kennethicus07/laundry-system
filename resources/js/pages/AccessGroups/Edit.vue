<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { AccessGroup } from '@/types/access_group';

import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table';

import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    access_group: AccessGroup;
    permissions: any[];
    roles: any[];
    features: any[];
}>();

/* ---------------- LOCAL STATE (SOURCE OF TRUTH IN UI) ---------------- */
const state = reactive<Record<number, Set<number>>>({});
const loading = ref(false);

/* initialize role permissions */
props.roles.forEach((role: any) => {
    const isSuperAdmin = role.name === 'super-admin';

    state[role.id] = new Set(
        isSuperAdmin
            ? props.permissions.map((p) => p.id) // ALL permissions
            : (role.permissions?.map((p: any) => p.id) ?? []),
    );
});

/* ---------------- GROUP BY FEATURE ---------------- */
const grouped = computed(() => {
    return props.features.map((feature) => ({
        feature,
        permissions: props.permissions.filter(
            (p) => p.features_id === feature.id,
        ),
    }));
});

/* ---------------- CHECKBOX STATE ---------------- */
function isChecked(roleId: number, permissionId: number) {
    return state[roleId]?.has(permissionId) ?? false;
}

function toggle(roleId: number, permissionId: number, checked: boolean) {
    const set = state[roleId] ?? (state[roleId] = new Set());

    if (checked) {
        set.add(permissionId);
    } else {
        set.delete(permissionId);
    }

    console.log('🟡 TOGGLED:', {
        role_id: roleId,
        permission_id: permissionId,
        checked,
    });
}

/* ---------------- SAVE (ENTERPRISE SYNC) ---------------- */
function save() {
    const roles = Object.entries(state).map(([role_id, perms]) => ({
        role_id: Number(role_id),
        permissions: Array.from(perms),
    }));

    const payload = {
        access_group_id: props.access_group.id,
        roles,
    };

    console.log('RBAC SYNC PAYLOAD:', payload);

    loading.value = true;

    router.post('/admin/roles-permissions/sync', payload, {
        preserveScroll: true,

        onSuccess: () => {
            toast.success('Permissions updated successfully');
        },

        onError: (err) => {
            console.error('❌ SYNC ERROR:', err);
            toast.error('Failed to update permissions');
        },

        onFinish: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <div class="space-y-6 p-6">
        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ props.access_group.label }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    Access Group Permissions Matrix
                </p>
            </div>

            <Button @click="save" :disabled="loading">
                {{ loading ? 'Saving...' : 'Save Changes' }}
            </Button>
        </div>

        <!-- MATRIX -->
        <div
            v-for="group in grouped"
            :key="group.feature.id"
            class="rounded-lg border"
        >
            <!-- TABLE -->
            <Table>
                <TableHeader class="border-b bg-muted px-4 py-3 font-medium">
                    <TableRow>
                        <TableHead class="w-[200px]">
                            {{ group.feature.name }}
                        </TableHead>

                        <TableHead
                            v-for="perm in group.permissions"
                            :key="perm.id"
                            class="text-center"
                        >
                            {{ perm.label }}
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="role in props.roles" :key="role.id">
                        <!-- ROLE NAME -->
                        <TableCell class="font-medium">
                            {{ role.label }}
                        </TableCell>

                        <!-- PERMISSIONS -->
                        <TableCell
                            v-for="perm in group.permissions"
                            :key="perm.id"
                            class="text-center"
                        >
                            <Checkbox
                                :model-value="isChecked(role.id, perm.id)"
                                :disabled="role.name === 'super-admin'"
                                @update:model-value="
                                    (val) =>
                                        toggle(role.id, perm.id, val === true)
                                "
                            />
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
