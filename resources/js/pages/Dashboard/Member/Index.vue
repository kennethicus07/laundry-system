```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface Service {
    id: number;
    code: string;
    name: string;
    description: string | null;
    image_path: string | null;
    pricing_unit: string;
    default_unit_price: string;
    is_active: boolean;
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Laundry Services',
                href: '/member/laundry-services',
            },
        ],
    },
});

defineProps<{
    services: Service[];
}>();

function serviceImageUrl(imagePath: string | null): string | null {
    if (!imagePath) {
        return null;
    }

    return `/storage/${imagePath.replace(/^\/+/, '')}`;
}
</script>

<template>
    <Head title="Laundry Services" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
        <!-- Page heading -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Laundry Services</h1>

            <p class="text-muted-foreground">
                Explore our services and choose what works for you.
            </p>
        </div>

        <!-- Services grid -->
        <div
            v-if="services.length > 0"
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
        >
            <Link
                v-for="service in services"
                :key="service.id"
                :href="`/member/laundry-services/${service.id}`"
                class="group block rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-ring"
                :aria-label="`View ${service.name} details`"
            >
                <Card
                    class="h-full overflow-hidden transition-shadow group-hover:shadow-md group-focus-visible:shadow-md"
                >
                    <!-- Service image -->
                    <div class="aspect-[16/9] overflow-hidden bg-muted">
                        <img
                            v-if="serviceImageUrl(service.image_path)"
                            :src="serviceImageUrl(service.image_path)!"
                            :alt="service.name"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy"
                        />

                        <!-- Fallback when no image is provided -->
                        <div
                            v-else
                            class="flex h-full items-center justify-center text-muted-foreground"
                        >
                            <span class="text-sm"> No image available </span>
                        </div>
                    </div>

                    <CardHeader>
                        <div class="flex items-start justify-between gap-3">
                            <CardTitle>
                                {{ service.name }}
                            </CardTitle>

                            <span
                                v-if="service.is_active"
                                class="shrink-0 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700"
                            >
                                Available
                            </span>

                            <span
                                v-else
                                class="shrink-0 rounded-full bg-muted px-2 py-1 text-xs font-medium text-muted-foreground"
                            >
                                Unavailable
                            </span>
                        </div>

                        <CardDescription>
                            {{ service.description || 'Laundry service' }}
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <p class="text-2xl font-bold">
                            ₱{{ Number(service.default_unit_price).toFixed(2) }}
                        </p>

                        <p class="text-sm text-muted-foreground">
                            per {{ service.pricing_unit }}
                        </p>

                        <p class="mt-4 text-sm font-medium text-primary">
                            View service details
                            <span aria-hidden="true">→</span>
                        </p>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Empty state -->
        <Card v-else>
            <CardContent class="py-10 text-center">
                <p class="font-medium">No services available yet.</p>

                <p class="mt-1 text-sm text-muted-foreground">
                    Please check back later.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
```
