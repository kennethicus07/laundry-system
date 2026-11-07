<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { NavItem } from '@/types';

import {
    BookOpen,
    FolderGit2,
    KeyRound,
    LayoutGrid,
    BriefcaseBusiness,
    UserRoundCog,
    Users,
    Bell,
    CalendarCheck,
    Truck,
    WashingMachine,
} from 'lucide-vue-next';

import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import { dashboard } from '@/routes';

const page = usePage();

const roles = computed(() => page.props.auth?.roles ?? []);

const isAdmin = computed(
    () =>
        roles.value.includes('super-admin') ||
        roles.value.includes('moderator') ||
        roles.value.includes('staff') ||
        roles.value.includes('officer'),
);

const isMember = computed(() => roles.value.includes('member'));

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        // {
        //     title: 'Dashboard',
        //     href: dashboard(),
        //     icon: LayoutGrid,
        // },
    ];

    /* ---------------- ADMIN MENU ---------------- */
    if (isAdmin.value) {
        items.push(
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Booking Requests',
                href: '/admin/bookings',
                icon: LayoutGrid,
            },
            {
                title: 'Pickup Scheduling',
                href: '/admin/pickup-schedules',
                icon: LayoutGrid,
            },
            {
                title: 'Pickup Assignments',
                href: '/admin/pickups',
                icon: LayoutGrid,
            },
            {
                title: 'Laundry Processing',
                href: '/admin/processing',
                icon: WashingMachine,
            },
            {
                title: 'Delivery Scheduling',
                href: '/admin/delivery-schedules',
                icon: CalendarCheck,
            },
            {
                title: 'Delivery Assignments',
                href: '/admin/deliveries',
                icon: Truck,
            },
            {
                title: 'Invoice',
                href: '/admin/invoices',
                icon: Truck,
            },
            {
                title: 'Users',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Roles',
                href: '/admin/roles',
                icon: BriefcaseBusiness,
            },
            {
                title: 'Permissions',
                href: '/admin/permissions',
                icon: KeyRound,
            },
            {
                title: 'Access Groups',
                href: '/admin/access-groups',
                icon: UserRoundCog,
            },
        );
    }

    /* ---------------- MEMBER MENU ---------------- */
    if (isMember.value) {
        items.push(
            {
                title: 'Laundry Services',
                href: '/member/laundry-services',
                icon: LayoutGrid,
            },
            {
                title: 'My Bookings',
                href: '/member/bookings',
                icon: LayoutGrid,
            },
        );
    }
    return items;
});

/* --------------------------------
   FOOTER LINKS (STATIC)
-------------------------------- */
const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <!-- LOGO -->
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- MAIN MENU -->
        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <!-- FOOTER -->
        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
