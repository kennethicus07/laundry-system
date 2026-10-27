<script setup lang="ts">
import { reactive, ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

import { useDateFormat } from '@/composables/useDateFormat';
import { useSortable } from '@/composables/useSortable';
import { usePagination } from '@/composables/usePagination';
import { PermissionStatus } from '@/generated/enums';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import { Permission } from '@/types/permission';
import { Badge } from '@/components/ui/badge';
import { useStatus } from '@/composables/useStatus';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
} from '@/components/ui/alert-dialog';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/components/ui/select';

import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu';

const { formatDateTime } = useDateFormat();
const { get: getStatus } = useStatus();

const props = defineProps<{
    permissions: Permission[];
    accessGroups: any[];
    features: any[];
}>();

/* ---------------- SORT ---------------- */
const { sort, getSortIcon, sortedData } = useSortable(props.permissions);

/* ---------------- FILTER ---------------- */
const search = ref('');
const selectedGroup = ref<string>('all');
const selectedFeature = ref<string>('all');

const filteredPermissions = computed(() => {
    let data = sortedData.value;

    if (search.value) {
        data = data.filter((p) =>
            p.name.toLowerCase().includes(search.value.toLowerCase()),
        );
    }

    if (selectedGroup.value !== 'all') {
        data = data.filter(
            (p) => String(p.access_group_id) === selectedGroup.value,
        );
    }

    if (selectedFeature.value !== 'all') {
        data = data.filter(
            (p) => String(p.features_id) === selectedFeature.value,
        );
    }

    return data;
});

const filteredFeatures = computed(() => {
    if (selectedGroup.value === 'all') return [];

    return props.features.filter(
        (f) => String(f.access_group_id) === selectedGroup.value,
    );
});

const formFilteredFeatures = computed(() => {
    if (!form.access_group_id) return [];

    return props.features.filter(
        (f) => String(f.access_group_id) === String(form.access_group_id),
    );
});

/* ---------------- PAGINATION ---------------- */
const {
    currentPage,
    perPage,
    paginatedData,
    totalPages,
    total,
    from,
    to,
    next,
    prev,
} = usePagination(() => filteredPermissions.value);

/* ---------------- MODAL ---------------- */
const open = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

/* ---------------- DELETE ---------------- */
const selectedPermission = ref<any | null>(null);
const deleteOpen = ref(false);

/* ---------------- FORM ---------------- */
const form = reactive({
    name: '',
    label: '',
    access_group_id: '' as string | number | null,
    features_id: '' as string | number | null,
    status: 1,
});

const errors = reactive<Record<string, string>>({});

/* ---------------- RESET ---------------- */
function resetForm() {
    form.name = '';
    form.label = '';
    form.access_group_id = null;
    form.features_id = null;
    form.status = 1;

    Object.keys(errors).forEach((k) => (errors[k] = ''));

    open.value = false;
    isEditing.value = false;
    editingId.value = null;
}

/* ---------------- OPEN ---------------- */
function openCreate() {
    resetForm();
    open.value = true;
}

function openEdit(permission: any) {
    form.name = permission.name;
    form.label = permission.label;
    form.access_group_id = String(permission.access_group_id);
    form.features_id = null;

    isEditing.value = true;
    editingId.value = permission.id;
    open.value = true;

    requestAnimationFrame(() => {
        form.features_id = String(permission.features_id);
    });
}

/* ---------------- VALIDATION ---------------- */
function validate() {
    Object.keys(errors).forEach((k) => (errors[k] = ''));

    let ok = true;

    if (!form.name.trim()) {
        errors.name = 'Permission name is required';
        ok = false;
    }

    if (!form.label.trim()) {
        errors.label = 'Permission label is required';
        ok = false;
    }

    if (!form.access_group_id) {
        errors.access_group_id = 'Please select a group';
        ok = false;
    }

    if (!form.features_id) {
        errors.features_id = 'Please select a feature';
        ok = false;
    }

    return ok;
}

/* ---------------- SUBMIT ---------------- */
function submit() {
    if (!validate()) {
        toast.error('Please fix errors');
        return;
    }

    const payload = { ...form };

    const options = {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            router.reload({
                only: ['permissions'],
            });
            toast.success(
                isEditing.value ? 'Permission updated' : 'Permission created',
            );
            resetForm();
        },

        onError: (errs: any) => {
            Object.values(errs).forEach((msg: any) => toast.error(msg));
        },
    };

    if (isEditing.value && editingId.value) {
        router.put(`/admin/permissions/${editingId.value}`, payload, options);
    } else {
        router.post('/admin/permissions', payload, options);
    }
}

/* ---------------- DELETE ---------------- */
function confirmDelete(p: any) {
    selectedPermission.value = p;
    deleteOpen.value = true;
}

function destroy() {
    if (!selectedPermission.value) return;

    router.delete(`/admin/permissions/${selectedPermission.value.id}`, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            router.reload({
                only: ['permissions'],
            });
            toast.success('Deleted successfully');
            deleteOpen.value = false;
            selectedPermission.value = null;
        },
    });
}

/* ---------------- WATCH (clean & global) ---------------- */
watch(
    () => [form.name, form.label, form.access_group_id],
    () => Object.keys(errors).forEach((k) => (errors[k] = '')),
);

watch(
    () => form.access_group_id,
    () => {
        form.features_id = null;
    },
);

watch(selectedGroup, () => {
    selectedFeature.value = 'all';
});
</script>

<template>
    <div class="space-y-6 p-6">
        <!-- HEADER -->
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Permissions</h1>
            <p class="text-sm text-muted-foreground">
                Manage system permissions for roles and users
            </p>
        </div>

        <!-- FILTERS -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <!-- SEARCH -->
                <div class="w-72">
                    <Input
                        v-model="search"
                        placeholder="Search permissions..."
                    />
                </div>

                <!-- GROUP FILTER (SHADCN) -->
                <Select v-model="selectedGroup">
                    <SelectTrigger class="w-56">
                        <SelectValue placeholder="All Access Groups" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem value="all">All Access Groups</SelectItem>

                        <SelectItem
                            v-for="group in props.accessGroups"
                            :key="group.id"
                            :value="String(group.id)"
                        >
                            {{ group.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select
                    v-model="selectedFeature"
                    :disabled="selectedGroup === 'all'"
                >
                    <SelectTrigger class="w-56">
                        <SelectValue
                            :placeholder="
                                selectedGroup === 'all'
                                    ? 'Select Access Group first'
                                    : 'All Features'
                            "
                        />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem value="all">All Features</SelectItem>

                        <SelectItem
                            v-for="feature in filteredFeatures"
                            :key="feature.id"
                            :value="String(feature.id)"
                        >
                            {{ feature.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <Button @click="openCreate"> Create Permission </Button>
        </div>

        <!-- TABLE -->
        <div class="rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead
                            @click="sort('name')"
                            class="cursor-pointer select-none hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-2">
                                Name
                                <component
                                    :is="getSortIcon('name')"
                                    class="h-4 w-4"
                                />
                            </div>
                        </TableHead>
                        <TableHead
                            @click="sort('features_id')"
                            class="cursor-pointer select-none hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-2">
                                Feature
                                <component
                                    :is="getSortIcon('features_id')"
                                    class="h-4 w-4"
                                />
                            </div>
                        </TableHead>
                        <TableHead
                            @click="sort('access_group_id')"
                            class="cursor-pointer select-none hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-2">
                                Access Group
                                <component
                                    :is="getSortIcon('access_group_id')"
                                    class="h-4 w-4"
                                />
                            </div>
                        </TableHead>

                        <TableHead>Status</TableHead>

                        <TableHead
                            @click="sort('created_at')"
                            class="cursor-pointer select-none hover:bg-muted/50"
                        >
                            Created At
                        </TableHead>

                        <TableHead
                            @click="sort('updated_at')"
                            class="cursor-pointer select-none hover:bg-muted/50"
                        >
                            Updated At
                        </TableHead>

                        <TableHead class="w-[120px] text-right">
                            Actions
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="perm in paginatedData" :key="perm.id">
                        <TableCell class="font-medium">
                            {{ perm.name }}
                        </TableCell>
                        <TableCell>
                            <span v-if="perm.features" class="text-foreground">
                                {{ perm.features.name }}
                            </span>
                            <span v-else class="text-muted-foreground">
                                —
                            </span>
                        </TableCell>
                        <TableCell>
                            <span
                                v-if="perm.access_group"
                                class="text-foreground"
                            >
                                {{ perm.access_group.name }}
                            </span>
                            <span v-else class="text-muted-foreground">
                                —
                            </span>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="getStatus(perm.status).variant">
                                {{ getStatus(perm.status).label }}
                            </Badge>
                        </TableCell>

                        <TableCell>
                            {{ formatDateTime(perm.created_at) }}
                        </TableCell>

                        <TableCell>
                            {{ formatDateTime(perm.updated_at) }}
                        </TableCell>

                        <TableCell class="text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" size="sm">
                                        Actions
                                    </Button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="openEdit(perm)">
                                        Edit
                                    </DropdownMenuItem>

                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="confirmDelete(perm)"
                                    >
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>

                    <!-- EMPTY STATE -->
                    <TableRow v-if="filteredPermissions.length === 0">
                        <TableCell
                            colspan="5"
                            class="py-6 text-center text-muted-foreground"
                        >
                            No results found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- PAGINATION -->
        <div class="flex items-center justify-between pt-4">
            <div class="text-sm text-muted-foreground">
                Showing
                <span class="font-medium text-foreground">{{ from }}</span>
                to
                <span class="font-medium text-foreground">{{ to }}</span>
                of
                <span class="font-medium text-foreground">{{ total }}</span>
                results
            </div>

            <div class="flex items-center gap-3">
                <Select v-model="perPage">
                    <SelectTrigger class="w-[90px]">
                        <SelectValue placeholder="10" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem :value="10">10</SelectItem>
                        <SelectItem :value="20">20</SelectItem>
                        <SelectItem :value="30">30</SelectItem>
                        <SelectItem :value="40">40</SelectItem>
                        <SelectItem :value="50">50</SelectItem>
                    </SelectContent>
                </Select>

                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === 1"
                    @click="prev"
                >
                    Prev
                </Button>

                <span class="text-sm">
                    {{ currentPage }} / {{ totalPages }}
                </span>

                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === totalPages"
                    @click="next"
                >
                    Next
                </Button>
            </div>
        </div>

        <!-- CREATE / EDIT DIALOG -->
        <Dialog v-model:open="open">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{
                            isEditing ? 'Edit Permission' : 'Create Permission'
                        }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- NAME -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Permission Name
                        </label>

                        <Input
                            v-model="form.name"
                            placeholder="e.g. event.delete"
                        />

                        <p v-if="errors.name" class="text-sm text-destructive">
                            {{ errors.name }}
                        </p>
                    </div>

                    <!-- LABEL -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Permission Label
                        </label>

                        <Input
                            v-model="form.label"
                            placeholder="e.g. event.delete"
                        />

                        <p v-if="errors.label" class="text-sm text-destructive">
                            {{ errors.label }}
                        </p>
                    </div>

                    <!-- GROUP -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Access Group
                        </label>

                        <Select v-model="form.access_group_id">
                            <SelectTrigger>
                                <SelectValue
                                    placeholder="Select access group"
                                />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem
                                    v-for="group in props.accessGroups"
                                    :key="group.id"
                                    :value="String(group.id)"
                                >
                                    {{ group.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <p
                            v-if="errors.access_group_id"
                            class="text-sm text-destructive"
                        >
                            {{ errors.access_group_id }}
                        </p>
                    </div>

                    <!-- Feature -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium"> Feature </label>

                        <Select
                            v-model="form.features_id"
                            :disabled="!form.access_group_id"
                        >
                            <SelectTrigger>
                                <SelectValue
                                    :placeholder="
                                        !form.access_group_id
                                            ? 'Select access group first'
                                            : 'Select feature'
                                    "
                                />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem
                                    v-for="feature in formFilteredFeatures"
                                    :key="feature.id"
                                    :value="String(feature.id)"
                                >
                                    {{ feature.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <p
                            v-if="errors.features_id"
                            class="text-sm text-destructive"
                        >
                            {{ errors.features_id }}
                        </p>
                    </div>

                    <div v-if="isEditing" class="space-y-2">
                        <label class="text-sm font-medium"> Status </label>

                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem :value="PermissionStatus.ACTIVE">
                                    Active
                                </SelectItem>
                                <SelectItem :value="PermissionStatus.INACTIVE">
                                    Inactive
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <p
                            v-if="errors.status"
                            class="text-sm text-destructive"
                        >
                            {{ errors.status }}
                        </p>
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex justify-end gap-2 pt-2">
                        <Button variant="outline" @click="resetForm">
                            Cancel
                        </Button>

                        <Button @click="submit">
                            {{ isEditing ? 'Update' : 'Save' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- DELETE -->
        <AlertDialog v-model:open="deleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle> Delete Permission </AlertDialogTitle>

                    <AlertDialogDescription>
                        This action cannot be undone. This will permanently
                        delete
                        <span class="font-semibold text-foreground">
                            {{ selectedPermission?.name }} </span
                        >.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel> Cancel </AlertDialogCancel>

                    <AlertDialogAction
                        class="bg-destructive text-white hover:bg-destructive/90"
                        @click="destroy"
                    >
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
