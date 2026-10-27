<script setup lang="ts">
import { reactive, ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

import { useDateFormat } from '@/composables/useDateFormat';
import { useSortable } from '@/composables/useSortable';
import { usePagination } from '@/composables/usePagination';
import { RoleStatus } from '@/generated/enums';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import { Role } from '@/types/role';
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
    roles: Role[];
    groups: any[];
}>();

/* ---------------- SORT ---------------- */
const { sort, getSortIcon, sortedData } = useSortable(props.roles);

/* ---------------- FILTER ---------------- */
const search = ref('');
const selectedGroup = ref<string>('all');

const filteredRoles = computed(() => {
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

    return data;
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
} = usePagination(() => filteredRoles.value);

/* ---------------- MODAL ---------------- */
const open = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

/* ---------------- DELETE ---------------- */
const selectedRole = ref<any | null>(null);
const deleteOpen = ref(false);

/* ---------------- FORM ---------------- */
const form = reactive({
    name: '',
    label: '',
    access_group_id: '' as string | number | null,
    status: 1,
});

const errors = reactive<Record<string, string>>({});

/* ---------------- RESET ---------------- */
function resetForm() {
    form.name = '';
    form.label = '';
    form.access_group_id = null;
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

function openEdit(role: any) {
    form.name = role.name;
    form.label = role.label;
    form.access_group_id = String(role.access_group_id);
    form.status = role.status.value;

    isEditing.value = true;
    editingId.value = role.id;
    open.value = true;
}

// function openEdit(role: any) {
//     router.visit(`/roles/${role.id}/edit`);
// }

/* ---------------- VALIDATION ---------------- */
function validate() {
    Object.keys(errors).forEach((k) => (errors[k] = ''));

    let ok = true;

    if (!form.name.trim()) {
        errors.name = 'Role name is required';
        ok = false;
    }

    if (!form.label.trim()) {
        errors.label = 'Role label is required';
        ok = false;
    }

    if (!form.access_group_id) {
        errors.access_group_id = 'Please select a group';
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
                only: ['roles'],
            });
            toast.success(isEditing.value ? 'Role updated' : 'Role created');
            resetForm();
        },

        onError: (errs: any) => {
            Object.values(errs).forEach((msg: any) => toast.error(msg));
        },
    };

    if (isEditing.value && editingId.value) {
        router.put(`/admin/roles/${editingId.value}`, payload, options);
    } else {
        router.post('/admin/roles', payload, options);
    }
}

/* ---------------- DELETE ---------------- */
function confirmDelete(p: any) {
    selectedRole.value = p;
    deleteOpen.value = true;
}

function destroy() {
    if (!selectedRole.value) return;

    router.delete(`/admin/roles/${selectedRole.value.id}`, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            router.reload({
                only: ['roles'],
            });
            toast.success('Deleted successfully');
            deleteOpen.value = false;
            selectedRole.value = null;
        },
    });
}

/* ---------------- WATCH (clean & global) ---------------- */
watch(
    () => [form.name, form.label, form.access_group_id],
    () => Object.keys(errors).forEach((k) => (errors[k] = '')),
);
</script>

<template>
    <div class="space-y-6 p-6">
        <!-- HEADER -->
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Roles</h1>
            <p class="text-sm text-muted-foreground">
                Manage system roles for users
            </p>
        </div>

        <!-- FILTERS -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <!-- SEARCH -->
                <div class="w-72">
                    <Input v-model="search" placeholder="Search roles..." />
                </div>

                <!-- GROUP FILTER (SHADCN) -->
                <Select v-model="selectedGroup">
                    <SelectTrigger class="w-56">
                        <SelectValue placeholder="All Access Groups" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem value="all">All Access Groups</SelectItem>

                        <SelectItem
                            v-for="group in props.groups"
                            :key="group.id"
                            :value="String(group.id)"
                        >
                            {{ group.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <Button @click="openCreate"> Create Role </Button>
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
                            @click="sort('access_group_id')"
                            class="cursor-pointer select-none hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-2">
                                Group
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
                    <TableRow v-for="role in paginatedData" :key="role.id">
                        <TableCell class="font-medium">
                            {{ role.name }}
                        </TableCell>

                        <TableCell>
                            <span v-if="role.role_group">
                                {{ role.role_group.name }}
                            </span>
                            <span v-else class="text-muted-foreground">
                                —
                            </span>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="getStatus(role.status).variant">
                                {{ getStatus(role.status).label }}
                            </Badge>
                        </TableCell>

                        <TableCell>
                            {{ formatDateTime(role.created_at) }}
                        </TableCell>

                        <TableCell>
                            {{ formatDateTime(role.updated_at) }}
                        </TableCell>

                        <TableCell class="text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" size="sm">
                                        Actions
                                    </Button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="openEdit(role)">
                                        Edit
                                    </DropdownMenuItem>

                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="confirmDelete(role)"
                                    >
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>

                    <!-- EMPTY STATE -->
                    <TableRow v-if="filteredRoles.length === 0">
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
                        {{ isEditing ? 'Edit Role' : 'Create Role' }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- NAME -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium"> Role Name </label>

                        <Input
                            v-model="form.name"
                            placeholder="e.g. super-admin"
                        />

                        <p v-if="errors.name" class="text-sm text-destructive">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium"> Role Label </label>

                        <Input
                            v-model="form.label"
                            placeholder="e.g. Super Admin"
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
                                    v-for="group in props.groups"
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

                    <div v-if="isEditing" class="space-y-2">
                        <label class="text-sm font-medium"> Status </label>

                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem :value="RoleStatus.ACTIVE">
                                    Active
                                </SelectItem>
                                <SelectItem :value="RoleStatus.INACTIVE">
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
                    <AlertDialogTitle> Delete Role </AlertDialogTitle>

                    <AlertDialogDescription>
                        This action cannot be undone. This will permanently
                        delete
                        <span class="font-semibold text-foreground">
                            {{ selectedRole?.name }} </span
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
