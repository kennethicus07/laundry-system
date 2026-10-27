<script setup lang="ts">
import { reactive, ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

import { useDateFormat } from '@/composables/useDateFormat';
import { useSortable } from '@/composables/useSortable';
import { usePagination } from '@/composables/usePagination';
import { useStatus } from '@/composables/useStatus';
import { Status } from '@/generated/enums';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';

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

import { AccessGroup } from '@/types/access_group';

const { formatDateTime } = useDateFormat();
const { get: getStatus } = useStatus();

const props = defineProps<{
    access_groups: AccessGroup[];
}>();

/* ---------------- SORT ---------------- */
const { sort, getSortIcon, sortedData } = useSortable(props.access_groups);

/* ---------------- SEARCH ---------------- */
const search = ref('');

const filteredGroups = computed(() => {
    let data = sortedData.value;

    if (search.value) {
        data = data.filter((g) =>
            g.name.toLowerCase().includes(search.value.toLowerCase()),
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
} = usePagination(() => filteredGroups.value);

/* ---------------- MODAL ---------------- */
const open = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

/* ---------------- DELETE ---------------- */
const selected = ref<any | null>(null);
const deleteOpen = ref(false);

/* ---------------- FORM ---------------- */
const form = reactive({
    name: '',
    label: '',
    status: 1,
});

const errors = reactive<Record<string, string>>({});

/* ---------------- RESET ---------------- */
function resetForm() {
    form.name = '';
    form.label = '';
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

function openEdit(group: any) {
    form.name = group.name;
    form.label = group.label;
    form.status = group.status.value;
    isEditing.value = true;
    editingId.value = group.id;
    open.value = true;
}

function openView(group: any) {
    router.get(`/admin/access-groups/${group.id}/edit`);
}

/* ---------------- VALIDATION ---------------- */
function validate() {
    Object.keys(errors).forEach((k) => (errors[k] = ''));

    let ok = true;

    if (!form.name.trim()) {
        errors.name = 'Access group name is required';
        ok = false;
    }

    if (!form.label.trim()) {
        errors.label = 'Access group label is required';
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
            router.reload({ only: ['access_groups'] });
            toast.success(isEditing.value ? 'Updated' : 'Created');
            resetForm();
        },
        onError: (errs: any) => {
            Object.values(errs).forEach((msg: any) => toast.error(msg));
        },
    };

    if (isEditing.value && editingId.value) {
        router.put(`/admin/access-groups/${editingId.value}`, payload, options);
    } else {
        router.post('/admin/access-groups', payload, options);
    }
}

/* ---------------- DELETE ---------------- */
function confirmDelete(item: any) {
    selected.value = item;
    deleteOpen.value = true;
}

function destroy() {
    if (!selected.value) return;

    router.delete(`/admin/access-groups/${selected.value.id}`, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            router.reload({ only: ['access_groups'] });
            toast.success('Deleted successfully');
            deleteOpen.value = false;
            selected.value = null;
        },
    });
}

/* ---------------- WATCH ---------------- */
watch(
    () => form.name,
    () => Object.keys(errors).forEach((k) => (errors[k] = '')),
);
</script>

<template>
    <div class="space-y-6 p-6">
        <!-- HEADER -->
        <div>
            <h1 class="text-2xl font-semibold">Access Groups</h1>
            <p class="text-sm text-muted-foreground">
                Manage system access groups
            </p>
        </div>

        <!-- SEARCH + CREATE -->
        <div class="flex items-center justify-between gap-4">
            <div class="w-72">
                <Input v-model="search" placeholder="Search access groups..." />
            </div>

            <Button @click="openCreate">Create Group</Button>
        </div>

        <!-- TABLE -->
        <div class="rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead @click="sort('name')" class="cursor-pointer">
                            Name
                        </TableHead>

                        <TableHead>Status</TableHead>

                        <TableHead>Created At</TableHead>
                        <TableHead>Updated At</TableHead>

                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="group in paginatedData" :key="group.id">
                        <TableCell class="font-medium">
                            {{ group.name }}
                        </TableCell>

                        <TableCell>
                            <Badge :variant="getStatus(group.status).variant">
                                {{ getStatus(group.status).label }}
                            </Badge>
                        </TableCell>

                        <TableCell>
                            {{ formatDateTime(group.created_at) }}
                        </TableCell>
                        <TableCell>
                            {{ formatDateTime(group.updated_at) }}
                        </TableCell>

                        <TableCell class="text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" size="sm">
                                        Actions
                                    </Button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="openView(group)">
                                        View
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="openEdit(group)">
                                        Quick Edit
                                    </DropdownMenuItem>

                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="confirmDelete(group)"
                                    >
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="filteredGroups.length === 0">
                        <TableCell colspan="4" class="py-6 text-center">
                            No results found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- PAGINATION -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-muted-foreground">
                {{ from }} - {{ to }} of {{ total }}
            </div>

            <div class="flex gap-2">
                <Button size="sm" variant="outline" @click="prev">
                    Prev
                </Button>
                <Button size="sm" variant="outline" @click="next">
                    Next
                </Button>
            </div>
        </div>

        <!-- MODAL -->
        <Dialog v-model:open="open">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{
                            isEditing
                                ? 'Edit Access Group'
                                : 'Create Access Group'
                        }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm">Name</label>
                        <Input v-model="form.name" />
                        <p v-if="errors.name" class="text-sm text-red-500">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm">Label</label>
                        <Input v-model="form.label" />
                        <p v-if="errors.label" class="text-sm text-red-500">
                            {{ errors.label }}
                        </p>
                    </div>
                    <div v-if="isEditing" class="space-y-2">
                        <label class="text-sm font-medium"> Status </label>

                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem :value="Status.ACTIVE">
                                    Active
                                </SelectItem>
                                <SelectItem :value="Status.INACTIVE">
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
                    <div class="flex justify-end gap-2">
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
                    <AlertDialogTitle>Delete Group</AlertDialogTitle>
                    <AlertDialogDescription>
                        This cannot be undone. Delete
                        <b>{{ selected?.name }}</b
                        >?
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="destroy">
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
