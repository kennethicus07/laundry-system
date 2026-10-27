<script setup lang="ts">
import { ref, reactive, watch, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { useStatus } from '@/composables/useStatus';
import { useDateFormat } from '@/composables/useDateFormat';
import { usePagination } from '@/composables/usePagination';

import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table';

import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/components/ui/select';

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu';

import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { UserStatus } from '@/generated/enums';

const props = defineProps<{
    users: any[];
    auth_user: any;
    roles: any[];
}>();

const { get: getStatus } = useStatus();
const { formatDateTime } = useDateFormat();

/* ---------------- STATE ---------------- */
const search = ref('');
const open = ref(false);
const loading = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

/* ---------------- FORM ---------------- */
const form = useForm({
    name: '',
    email: '',
    password: '',
    status: 1,
    role_id: null as number | null,
});

/* ---------------- CUSTOM ERRORS ---------------- */
const errors = reactive<Record<string, string>>({});

/* ---------------- FILTER ---------------- */
const filteredUsers = computed(() => {
    if (!search.value) return props.users;

    return props.users.filter(
        (u) =>
            u.name?.toLowerCase().includes(search.value.toLowerCase()) ||
            u.email?.toLowerCase().includes(search.value.toLowerCase()),
    );
});

/* ---------------- PAGINATION (RESTORED) ---------------- */
const {
    paginatedData,
    currentPage,
    perPage,
    totalPages,
    total,
    from,
    to,
    next,
    prev,
} = usePagination(() => filteredUsers.value);

/* ---------------- RESET ---------------- */
function resetForm() {
    form.reset();

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

/* ---------------- VALIDATION ---------------- */
function validate() {
    Object.keys(errors).forEach((k) => (errors[k] = ''));

    let ok = true;

    if (!form.name.trim()) {
        errors.name = 'Name is required';
        ok = false;
    }

    if (!form.email.trim()) {
        errors.email = 'Email is required';
        ok = false;
    }

    if (!isEditing.value && !form.password.trim()) {
        errors.password = 'Password is required';
        ok = false;
    }

    if (!form.role_id) {
        errors.role_id = 'Role is required';
        ok = false;
    }

    return ok;
}

function openView(user: any) {
    router.get(`/admin/users/${user.id}/edit`);
}

/* ---------------- SUBMIT ---------------- */
function submit() {
    if (!validate()) {
        toast.error('Please fix errors');
        return;
    }

    const payload = {
        name: form.name,
        email: form.email,
        password: form.password,
        status: form.status,
        roles: form.role_id ? [form.role_id] : [],
        auto_verify: true,
    };

    const options = {
        preserveScroll: true,
        preserveState: false,

        onSuccess: () => {
            router.reload({
                only: ['users'],
            });

            toast.success(isEditing.value ? 'User updated' : 'User created');

            resetForm();
        },

        onError: (errs: any) => {
            Object.values(errs).forEach((msg: any) => {
                toast.error(msg);
            });
        },

        onFinish: () => {
            loading.value = false;
        },
    };

    loading.value = true;

    if (isEditing.value && editingId.value) {
        router.put(`/admin/users/${editingId.value}`, payload, options);
    } else {
        router.post('/admin/users', payload, options);
    }
}

/* ---------------- CLEAR ERRORS ON TYPE (IMPORTANT FIX) ---------------- */
watch(
    () => form.name,
    () => (errors.name = ''),
);

watch(
    () => form.email,
    () => (errors.email = ''),
);

watch(
    () => form.password,
    () => (errors.password = ''),
);

watch(
    () => form.role_id,
    () => (errors.role_id = ''),
);

/* ---------------- CLOSE MODAL ---------------- */
watch(open, (val) => {
    if (!val) resetForm();
});

/* ---------------- EDIT ---------------- */
function editUser(user: any) {
    isEditing.value = true;
    editingId.value = user.id;

    form.name = user.name;
    form.status = user.status.value;
    form.email = user.email;
    form.password = '';

    // ✅ get role from relationship
    form.role_id = user.roles?.[0]?.id ?? null;

    open.value = true;
}
</script>

<template>
    <div class="space-y-6 p-6">
        <!-- HEADER -->
        <div>
            <h1 class="text-2xl font-semibold">Users</h1>
            <p class="text-sm text-muted-foreground">
                Manage system users and access
            </p>
        </div>

        <!-- SEARCH + CREATE -->
        <div class="flex items-center justify-between gap-4">
            <div class="w-72">
                <Input v-model="search" placeholder="Search users..." />
            </div>

            <Button @click="openCreate">Create User</Button>
        </div>

        <!-- TABLE -->
        <div class="rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Roles</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Created</TableHead>
                        <TableHead>Updated</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="user in paginatedData" :key="user.id">
                        <!-- NAME + (YOU) FIX -->
                        <TableCell>
                            <div class="flex items-center gap-1">
                                <span>{{ user.name }}</span>
                                <span
                                    v-if="user.id === props.auth_user.id"
                                    class="text-muted-foreground"
                                >
                                    (You)
                                </span>
                            </div>
                        </TableCell>

                        <TableCell>{{ user.email }}</TableCell>

                        <TableCell>
                            <Badge
                                v-for="role in user.roles"
                                :key="role.id"
                                variant="outline"
                            >
                                {{ role.label }}
                            </Badge>
                        </TableCell>

                        <TableCell>
                            <Badge :variant="getStatus(user.status).variant">
                                {{ getStatus(user.status).label }}
                            </Badge>
                        </TableCell>

                        <TableCell>
                            {{ formatDateTime(user.created_at) }}
                        </TableCell>

                        <TableCell>
                            {{ formatDateTime(user.updated_at) }}
                        </TableCell>

                        <TableCell class="text-right">
                            <div v-if="user.id === props.auth_user.id">
                                <span class="text-muted-foreground"
                                    >Current user</span
                                >
                            </div>

                            <DropdownMenu v-else>
                                <DropdownMenuTrigger as-child>
                                    <Button size="sm" variant="outline">
                                        Actions
                                    </Button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="editUser(user)">
                                        Quick Edit
                                    </DropdownMenuItem>

                                    <DropdownMenuItem @click="openView(user)">
                                        View
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="paginatedData.length === 0">
                        <TableCell colspan="7" class="py-6 text-center">
                            No users found
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- PAGINATION (RESTORED) -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-muted-foreground">
                Showing
                <span class="font-medium text-foreground">{{ from }}</span>
                to
                <span class="font-medium text-foreground">{{ to }}</span>
                of
                <span class="font-medium text-foreground">{{ total }}</span>
                results
            </div>

            <div class="flex items-center gap-2">
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

        <!-- DIALOG -->
        <Dialog v-model:open="open">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ isEditing ? 'Edit User' : 'Create User' }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium"> Name </label>

                        <Input v-model="form.name" placeholder="e.g. Name" />

                        <p v-if="errors.name" class="text-sm text-destructive">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium"> Email </label>

                        <Input v-model="form.email" placeholder="e.g. Email" />

                        <p v-if="errors.email" class="text-sm text-destructive">
                            {{ errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Password
                            <span
                                v-if="isEditing"
                                class="text-xs text-muted-foreground"
                            >
                                (leave blank to keep current)
                            </span>
                        </label>

                        <Input
                            v-model="form.password"
                            placeholder="e.g. Password"
                        />

                        <p
                            v-if="errors.password"
                            class="text-sm text-destructive"
                        >
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Role -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium"> Role </label>
                        <Select v-model="form.role_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Role" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem
                                    v-for="role in props.roles"
                                    :key="role.id"
                                    :value="role.id"
                                >
                                    {{ role.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <p
                            v-if="errors.role_id"
                            class="text-sm text-destructive"
                        >
                            {{ errors.role_id }}
                        </p>
                    </div>

                    <div v-if="isEditing" class="space-y-2">
                        <label class="text-sm font-medium"> Status </label>

                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem :value="UserStatus.ACTIVE">
                                    Active
                                </SelectItem>

                                <SelectItem :value="UserStatus.DEACTIVATED">
                                    Deactivated
                                </SelectItem>
                                <SelectItem :value="UserStatus.SUSPENDED">
                                    Suspended
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

                        <Button :disabled="loading" @click="submit">
                            {{ loading ? 'Saving...' : 'Save' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
