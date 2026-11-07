<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface Service {
    id: number;
    code: string;
    name: string;
    description: string | null;
    pricing_unit: string;
    default_unit_price: string;
    image_path?: string | null;
}
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Laundry Services',
                href: '/member/laundry-services',
            },
            {
                title: 'Service Details',
                href: '',
            },
        ],
    },
});

const props = defineProps<{
    service: Service;
}>();

const service = computed(() => props.service);

const form = useForm({
    pickup_address: '',
    mobile_number: '',
    preferred_pickup_at: '',
    estimated_quantity: '1',
    customer_notes: '',
});

function submitBooking() {
    form.post(`/member/laundry-services/${props.service.id}/book`);
}
</script>

<template>
    <Head :title="`Book ${service.name}`" />

    <main class="mx-auto max-w-6xl space-y-6 p-6">
        <Link
            href="/member/laundry-services"
            class="text-sm text-muted-foreground hover:text-foreground"
        >
            ← Back to services
        </Link>

        <div class="grid items-start gap-6 lg:grid-cols-2">
            <!-- Service details -->
            <Card class="overflow-hidden">
                <div v-if="service.image_path" class="aspect-[4/3] bg-muted">
                    <img
                        :src="`/storage/${service.image_path}`"
                        :alt="service.name"
                        class="h-full w-full object-cover"
                    />
                </div>

                <div
                    v-else
                    class="flex aspect-[4/3] items-center justify-center bg-muted"
                >
                    <span class="text-muted-foreground">
                        {{ service.name }}
                    </span>
                </div>

                <CardHeader>
                    <CardDescription>
                        {{ service.code }}
                    </CardDescription>

                    <CardTitle class="text-2xl">
                        {{ service.name }}
                    </CardTitle>

                    <CardDescription>
                        {{
                            service.description || 'Professional laundry care.'
                        }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-4">
                    <p class="text-2xl font-bold">
                        ₱{{ Number(service.default_unit_price).toFixed(2) }}
                        <span class="text-sm font-normal text-muted-foreground">
                            / {{ service.pricing_unit }}
                        </span>
                    </p>

                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-sm text-muted-foreground">
                            Estimated price
                        </p>

                        <p class="text-xl font-semibold">
                            ₱{{
                                (
                                    Number(service.default_unit_price) *
                                    Number(form.estimated_quantity || 0)
                                ).toFixed(2)
                            }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Estimate only. The final price may change after the
                            laundry is inspected and weighed.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Booking form -->
            <Card>
                <CardHeader>
                    <CardTitle>Book your laundry pickup</CardTitle>

                    <CardDescription>
                        Provide your pickup details for
                        {{ service.name }}.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form class="space-y-5" @submit.prevent="submitBooking">
                        <div class="space-y-2">
                            <Label for="pickup_address"> Pickup address </Label>

                            <Textarea
                                id="pickup_address"
                                v-model="form.pickup_address"
                                placeholder="Enter your complete pickup address"
                                required
                                :aria-invalid="!!form.errors.pickup_address"
                            />

                            <p
                                v-if="form.errors.pickup_address"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.pickup_address }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="mobile_number">Mobile number</Label>

                            <Input
                                id="mobile_number"
                                v-model="form.mobile_number"
                                type="tel"
                                inputmode="numeric"
                                maxlength="12"
                                placeholder="Enter your mobile number"
                                required
                                :aria-invalid="!!form.errors.mobile_number"
                                @input="
                                    form.mobile_number = form.mobile_number
                                        .replace(/\D/g, '')
                                        .slice(0, 12)
                                "
                            />

                            <p
                                v-if="form.errors.mobile_number"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.mobile_number }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="preferred_pickup_at">
                                Preferred pickup date and time
                            </Label>

                            <Input
                                id="preferred_pickup_at"
                                v-model="form.preferred_pickup_at"
                                type="datetime-local"
                                required
                                :aria-invalid="
                                    !!form.errors.preferred_pickup_at
                                "
                            />

                            <p
                                v-if="form.errors.preferred_pickup_at"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.preferred_pickup_at }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="estimated_quantity">
                                Estimated quantity ({{ service.pricing_unit }})
                            </Label>

                            <Input
                                id="estimated_quantity"
                                v-model="form.estimated_quantity"
                                type="number"
                                min="0.01"
                                step="0.01"
                                required
                                :aria-invalid="!!form.errors.estimated_quantity"
                            />

                            <p
                                v-if="form.errors.estimated_quantity"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.estimated_quantity }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="customer_notes">
                                Special instructions (optional)
                            </Label>

                            <Textarea
                                id="customer_notes"
                                v-model="form.customer_notes"
                                placeholder="Any pickup instructions or laundry concerns?"
                                :aria-invalid="!!form.errors.customer_notes"
                            />

                            <p
                                v-if="form.errors.customer_notes"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.customer_notes }}
                            </p>
                        </div>

                        <div
                            v-if="form.errors.service"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.service }}
                        </div>

                        <Button
                            type="submit"
                            class="w-full"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'Submitting booking...'
                                    : 'Submit booking request'
                            }}
                        </Button>

                        <p class="text-center text-xs text-muted-foreground">
                            Your booking will be submitted for confirmation.
                        </p>
                    </form>
                </CardContent>
            </Card>
        </div>
    </main>
</template>
