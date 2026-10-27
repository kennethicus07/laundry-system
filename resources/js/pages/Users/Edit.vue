<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/components/ui/select';

import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table';

/* ---------------- PROPS ---------------- */
const props = defineProps<{
    user: any;
    roles: any[];
    features: any[];
    access_groups: any[];
    permissions: any[];
    effectivePermissions: {
        permissions: number[];
        overrides: Record<number, number>;
    };
}>();

const loading = ref(false);

/* ---------------- FORM ---------------- */
const form = reactive({
    name: props.user.name,
    email: props.user.email,
    password: '',
    status: props.user.status,
    role_id: props.user.roles?.[0]?.id ?? null,
});

/* ---------------- DATA ---------------- */
const roles = computed(() => props.roles ?? []);
const features = computed(() => props.features ?? []);
const permissions = computed(() => props.permissions ?? []);

/* ---------------- ROLE PERMISSION MAP (KEY FIX) ---------------- */
const rolePermissionsMap = computed(() => {
    const map: Record<number, number[]> = {};

    roles.value.forEach((role) => {
        map[role.id] = role.permissions?.map((p: any) => p.id) ?? [];
    });

    return map;
});

/* ---------------- OVERRIDE MEMORY PER ROLE ---------------- */
const originalRoleId = props.user.roles?.[0]?.id ?? null;

const overrideMemory = reactive<Record<number, Record<number, number>>>({});

overrideMemory[originalRoleId] = {
    ...(props.effectivePermissions?.overrides ?? {}),
};

/* ---------------- ROLE ---------------- */
const selectedRole = computed(() =>
    roles.value.find((r) => r.id === form.role_id),
);

/* ---------------- ACCESS GROUP ---------------- */
const selectedAccessGroup = computed(() => {
    const groupId = selectedRole.value?.access_group_id;

    if (!groupId) return null;

    return (
        props.access_groups.find((g) => Number(g.id) === Number(groupId)) ??
        null
    );
});

const hasAccessGroup = computed(() => !!selectedAccessGroup.value);

/* ---------------- GROUPING ---------------- */
const grouped = computed(() => {
    const groupId = selectedRole.value?.access_group_id;

    if (!groupId) return [];

    const filteredFeatures = features.value.filter(
        (f) => Number(f.access_group_id) === Number(groupId),
    );

    const filteredPermissions = permissions.value.filter(
        (p) => Number(p.access_group_id) === Number(groupId),
    );

    return filteredFeatures.map((feature) => ({
        feature,
        permissions: filteredPermissions.filter(
            (p) => Number(p.features_id) === Number(feature.id),
        ),
    }));
});

/* ---------------- ACTIVE OVERRIDES ---------------- */
const activeOverrides = computed(() => {
    return overrideMemory[form.role_id] ?? {};
});

/* ---------------- CHECK LOGIC (FIXED) ---------------- */
function isChecked(permissionId: number): boolean {
    const roleId = form.role_id;

    const overrides = activeOverrides.value;

    // 1. role-specific override (highest priority)
    if (permissionId in overrides) {
        return overrides[permissionId] === 1;
    }

    // 2. ROLE BASE PERMISSIONS (FIXED)
    const rolePerms = rolePermissionsMap.value?.[roleId] ?? [];

    return rolePerms.includes(permissionId);
}

/* ---------------- TOGGLE ---------------- */
function toggle(permissionId: number, checked: boolean) {
    const roleId = form.role_id;

    if (!overrideMemory[roleId]) {
        overrideMemory[roleId] = {};
    }

    overrideMemory[roleId][permissionId] = checked ? 1 : -1;
}

/* ---------------- ROLE SWITCH INIT ---------------- */
watch(
    () => form.role_id,
    (newRoleId) => {
        if (!overrideMemory[newRoleId]) {
            overrideMemory[newRoleId] = {};
        }
    },
);

/* ---------------- SAVE ---------------- */
function save() {
    loading.value = true;

    const currentOverrides = overrideMemory[form.role_id] ?? {};

    router.put(
        `/admin/users/${props.user.id}`,
        {
            ...form,
            roles: form.role_id ? [form.role_id] : [],
            overrides: Object.entries(currentOverrides).map(
                ([permission_id, effect]) => ({
                    permission_id: Number(permission_id),
                    effect,
                }),
            ),
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success('User updated successfully');

                // 🔥 reload EVERYTHING from backend
                router.reload({
                    only: [
                        'user',
                        'roles',
                        'permissions',
                        'features',
                        'access_groups',
                        'effectivePermissions',
                    ],

                    onSuccess: (page) => {
                        const newRoleId =
                            page.props.user.roles?.[0]?.id ?? null;

                        // 🔥 FULL RESET (critical)
                        Object.keys(overrideMemory).forEach((key) => {
                            delete overrideMemory[Number(key)];
                        });

                        // 🔥 rehydrate from backend
                        if (newRoleId) {
                            overrideMemory[newRoleId] = {
                                ...(page.props.effectivePermissions
                                    ?.overrides ?? {}),
                            };
                        }
                    },
                });
            },

            onError: () => toast.error('Failed to update user'),

            onFinish: () => (loading.value = false),
        },
    );
}
</script>

<template>
    <div class="space-y-6 p-6">
        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Edit User</h1>
                <p class="text-sm text-muted-foreground">
                    Role-based dynamic permission system
                </p>
            </div>

            <Button @click="save" :disabled="loading">
                {{ loading ? 'Saving...' : 'Save Changes' }}
            </Button>
        </div>

        <!-- FORM -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <Input v-model="form.name" placeholder="Name" />
            <Input v-model="form.email" placeholder="Email" />
            <Input
                v-model="form.password"
                type="password"
                placeholder="Password"
            />

            <Select v-model="form.status">
                <SelectTrigger>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="1">Active</SelectItem>
                    <SelectItem :value="0">Deactivated</SelectItem>
                    <SelectItem :value="2">Pending</SelectItem>
                    <SelectItem :value="5">Suspended</SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="form.role_id">
                <SelectTrigger>
                    <SelectValue placeholder="Select role" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="role in roles"
                        :key="role.id"
                        :value="role.id"
                    >
                        {{ role.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- ACCESS GROUP -->
        <div>
            <label class="text-sm font-medium">Access Group</label>
            <p class="text-sm text-muted-foreground">
                {{ selectedAccessGroup?.label || 'No Access Group' }}
            </p>
        </div>

        <!-- PERMISSION MATRIX -->
        <!-- PERMISSION MATRIX -->
        <div v-if="hasAccessGroup" class="space-y-4">
            <!-- ❌ NO FEATURES -->
            <div
                v-if="grouped.length === 0"
                class="rounded-lg border p-6 text-center text-sm text-muted-foreground"
            >
                No features available for this access group.
            </div>

            <!-- ✅ HAS FEATURES -->
            <div
                v-else
                v-for="group in grouped"
                :key="group.feature.id"
                class="mb-5 rounded-lg border"
            >
                <Table>
                    <TableHeader
                        class="border-b bg-muted px-4 py-3 font-medium"
                    >
                        <TableRow>
                            <TableHead class="w-[200px]">
                                {{ group.feature.name }}
                            </TableHead>

                            <!-- ❌ NO PERMISSIONS HEADER -->
                            <TableHead
                                v-if="group.permissions.length === 0"
                                class="text-center text-muted-foreground"
                            >
                                No permissions
                            </TableHead>

                            <!-- ✅ PERMISSION HEADERS -->
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
                        <TableRow>
                            <TableCell />

                            <!-- ❌ NO PERMISSIONS BODY -->
                            <TableCell
                                v-if="group.permissions.length === 0"
                                class="text-center text-muted-foreground"
                            >
                                —
                            </TableCell>

                            <!-- ✅ PERMISSION CHECKBOXES -->
                            <TableCell
                                v-for="perm in group.permissions"
                                :key="perm.id"
                                class="text-center"
                            >
                                <Checkbox
                                    :model-value="isChecked(perm.id)"
                                    @update:model-value="
                                        (val) => toggle(perm.id, val === true)
                                    "
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- ❌ NO ACCESS GROUP -->
        <div
            v-else
            class="rounded-lg border p-6 text-center text-sm text-muted-foreground"
        >
            No access group assigned to this role.
        </div>
    </div>
</template>
