<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Banknote } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney, newIdempotencyKey } from '../helpers';
import { billingRoutes } from '../routes';
import type { Decimal } from '../types';

const props = defineProps<{
    invoiceId: number;
    payable: Decimal;
    pending: Decimal;
    disabled?: boolean;
}>();

const open = ref(false);
const form = useForm({ amount: '', idempotency_key: '' });

watch(open, (isOpen) => {
    if (!isOpen) return;

    form.reset();
    form.clearErrors();
    // One key per dialog session: double-clicks and network retries reuse it,
    // so the server records this cash payment only once.
    form.idempotency_key = newIdempotencyKey();
});

function fillPayable() {
    form.amount = props.payable;
}

function submit() {
    form.post(billingRoutes.invoices.cashPayment(props.invoiceId), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button :disabled="disabled">
                <Banknote class="size-4" />
                Record cash
            </Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-md">
            <form class="space-y-5" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Record cash payment</DialogTitle>
                    <DialogDescription>
                        Enter the amount applied to this invoice, not the cash
                        handed over. Give change separately.
                    </DialogDescription>
                </DialogHeader>

                <div class="rounded-md border bg-muted/40 p-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground"
                            >Amount payable now</span
                        >
                        <span class="font-semibold tabular-nums">{{
                            formatMoney(payable)
                        }}</span>
                    </div>
                    <p
                        v-if="pending !== '0.00'"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        {{ formatMoney(pending) }} is awaiting QR verification
                        and is held back.
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="cash_amount">Amount (₱)</Label>
                    <div class="flex gap-2">
                        <Input
                            id="cash_amount"
                            v-model="form.amount"
                            inputmode="decimal"
                            autocomplete="off"
                            placeholder="0.00"
                            class="tabular-nums"
                            autofocus
                        />
                        <Button
                            type="button"
                            variant="outline"
                            @click="fillPayable"
                            >Full amount</Button
                        >
                    </div>
                    <p
                        v-if="form.errors.amount"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.amount }}
                    </p>
                    <p
                        v-if="form.errors.idempotency_key"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.idempotency_key }}
                    </p>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="open = false"
                        >Cancel</Button
                    >
                    <Button
                        type="submit"
                        :disabled="form.processing || !form.amount"
                    >
                        {{ form.processing ? 'Recording…' : 'Record payment' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
